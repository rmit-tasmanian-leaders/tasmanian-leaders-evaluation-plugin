<?php
/** Discover REST forms and resolve document-based, administrator-configurable profiles. */
if (!defined('ABSPATH')) { exit; }
class TLE_Form_Registry
{
    const OPTION = 'tle_form_profiles_v1';
    public static function catalog() {
        static $catalog;
        if ($catalog === null) {
            $catalog = json_decode(file_get_contents(dirname(__DIR__) . '/config/capability-catalog.json'), true);
        }
        return is_array($catalog) ? $catalog : [];
    }
    public static function normalise_text($text) {
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES, 'UTF-8');
        $text = str_replace(['’', '‘', '“', '”', '(R)', '*'], ["'", "'", '"', '"', '', ''], $text);
        $text = strtolower($text);
        return preg_replace('/[^a-z0-9]+/', '', $text);
    }
    public static function stored() { return (array) get_option(self::OPTION, []); }
    public static function legacy() {
        return require dirname(__DIR__) . '/config/gravity-forms-field-map.php';
    }
    public static function form_id($key) {
        if (is_numeric($key)) { return absint($key); }
        if (preg_match('/^form-(\d+)$/', (string) $key, $match)) { return absint($match[1]); }
        $legacy = self::legacy();
        return absint($legacy[$key]['form_id'] ?? 0);
    }
    public static function item_index() {
        $index = [];
        foreach (self::catalog() as $section => $caps) {
            foreach ($caps as $cap => $items) {
                foreach ($items as $item) {
                    $index[$item['id']] = $item + ['section' => $section, 'capability' => $cap];
                }
            }
        }
        return $index;
    }
    private static function profile_item_index($config) {
        $index = ($config['catalog_mode'] ?? 'document') === 'custom' ? [] : self::item_index();
        foreach ($config['custom_items'] ?? [] as $item) {
            $index[$item['id']] = $item + ['source' => 'configured_custom'];
        }
        return $index;
    }
    public static function discover() {
        $forms = TLE_Gravity_Forms_Provider::get_forms();
        if (is_wp_error($forms)) { return $forms; }
        if (isset($forms['forms'])) { $forms = $forms['forms']; }
        if (!is_array($forms)) { return new WP_Error('tle_invalid_forms', 'The API did not return a form list.', ['status' => 502]); }
        $result = [];
        foreach ($forms as $form) {
            if (!is_array($form) || empty($form['id'])) { continue; }
            $id = absint($form['id']);
            $config = self::configuration($id);
            $result[] = ['form_id' => $id, 'form_key' => 'form-' . $id,
                'title' => (string) ($form['title'] ?? ''),
                'evaluation_stage' => $config['evaluation_stage'] ?? '',
                'enabled' => !empty($config['enabled'])];
        }
        return $result;
    }
    public static function configuration($id) {
        $base = ['catalog_mode' => 'document', 'enabled' => false, 'evaluation_stage' => '', 'fields' => [], 'question_overrides' => [], 'custom_items' => [], 'scoring_confirmed' => false];
        // Import existing context mappings only. Question membership comes from the document.
        foreach (self::legacy() as $map) {
            if ((int) $map['form_id'] === (int) $id) {
                $base['enabled'] = true;
                $base['evaluation_stage'] = $map['evaluation_stage'];
                $base['fields'] = $map['fields'];
                break;
            }
        }
        $stored = self::stored();
        return array_replace($base, (array) ($stored[(string) $id] ?? []));
    }
    public static function fields($form) {
        $result = [];
        foreach ($form['fields'] ?? [] as $field) {
            if (!isset($field['id'])) { continue; }
            $inputs = is_array($field['inputs'] ?? null) ? $field['inputs'] : [];
            if (!empty($inputs) && !empty($field['gsurveyLikertEnableMultipleRows'])) {
                $rows = array_values(is_array($field['gsurveyLikertRows'] ?? null) ? $field['gsurveyLikertRows'] : []);
                foreach (array_values($inputs) as $i => $input) {
                    if (!isset($input['id'])) { continue; }
                    $result[(string) $input['id']] = ['label' => $input['label'] ?? ($rows[$i]['text'] ?? ''), 'field' => $field];
                }
            } else {
                $result[(string) $field['id']] = ['label' => $field['label'] ?? '', 'field' => $field];
                foreach ($inputs as $input) {
                    if (isset($input['id'])) {
                        $result[(string) $input['id']] = ['label' => $input['label'] ?? '', 'field' => $field];
                    }
                }
            }
        }
        return $result;
    }
    private static $cache = [];
    public static function profile($key) {
        $id = self::form_id($key);
        if (!isset(self::$cache[$id])) { self::$cache[$id] = self::resolve_profile($key); }
        return self::$cache[$id];
    }
    private static function resolve_profile($key) {
        $id = self::form_id($key);
        if (!$id) { return new WP_Error('tle_unknown_form', 'Select a valid API form ID.', ['status' => 400]); }
        $form = TLE_Gravity_Forms_Provider::get_form($id);
        if (is_wp_error($form)) { return $form; }
        if (!is_array($form) || !isset($form['fields'])) {
            return new WP_Error('tle_form_fields_unavailable', 'The API did not return form fields.', ['status' => 502]);
        }
        $config = self::configuration($id);
        $fields = self::fields($form);
        $index = self::profile_item_index($config);
        $matches = []; $unmatched = []; $ambiguous = []; $issues = [];
        foreach ($fields as $field_id => $info) {
            $candidates = [];
            foreach ($index as $item_id => $item) {
                if (self::normalise_text($info['label']) !== '' && self::normalise_text($info['label']) === self::normalise_text($item['text'])) {
                    $candidates[] = $item_id;
                }
            }
            $override = $config['question_overrides'][(string) $field_id] ?? null;
            if (is_array($override) && array_key_exists('item_id', $override)) {
                $candidates = $override['item_id'] === '' ? [] : [(string) $override['item_id']];
            }
            if (count($candidates) !== 1) {
                if (count($candidates) > 1) { $ambiguous[] = (string) $field_id; }
                continue;
            }
            $item_id = $candidates[0];
            if (!isset($index[$item_id])) { $issues[] = 'Unknown document item for field ' . $field_id; continue; }
            $item = $index[$item_id];
            $scores = []; $excluded = [];
            foreach (is_array($info['field']['choices'] ?? null) ? $info['field']['choices'] : [] as $choice) {
                if (!isset($choice['score']) || !is_numeric($choice['score'])) { continue; }
                $score = (float) $choice['score'];
                if (in_array(self::normalise_text($choice['text'] ?? ''), ['notapplicable', 'na'], true)) {
                    $excluded[] = $score;
                } else { $scores[] = $score; }
            }
            $rule = ['reverse' => $item['reverse'], 'excluded_scores' => $excluded,
                'raw_min' => $scores ? min($scores) : null, 'raw_max' => $scores ? max($scores) : null,
                'allowed_scores' => array_values(array_unique($scores)), 'target_min' => 1, 'target_max' => 7];
            if (is_array($override)) { $rule = array_replace($rule, (array) ($override['scoring'] ?? [])); }
            if (!is_numeric($rule['raw_min']) || !is_numeric($rule['raw_max']) || !is_finite((float) $rule['raw_min']) || !is_finite((float) $rule['raw_max']) || $rule['raw_max'] <= $rule['raw_min']) {
                $issues[] = 'Scoring range unavailable for field ' . $field_id;
                continue;
            }
            $matches[(string) $field_id] = $item + ['scoring' => $rule, 'field_id' => (string) $field_id];
        }
        // Duplicate matches cannot silently count one document item twice.
        $by_item = [];
        foreach ($matches as $field_id => $item) { $by_item[$item['id']][] = $field_id; }
        foreach ($by_item as $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $field_id) { unset($matches[$field_id]); $ambiguous[] = (string) $field_id; }
                $issues[] = 'Multiple fields match the same document question; exclude or remap one.';
            }
        }
        $mapped = array_column($matches, 'id');
        foreach ($index as $item_id => $item) {
            if (!in_array($item_id, $mapped, true)) { $unmatched[] = $item_id; }
        }
        foreach ($config['fields'] as $name => $field_id) {
            if ($field_id !== '' && !isset($fields[(string) $field_id])) { $issues[] = 'Context field not found: ' . $name; }
        }
        $evaluation_fields = [];
        foreach ($fields as $field_id => $info) {
            if (is_array($info['field']['choices'] ?? null) && !empty($info['field']['choices']) && !in_array($info['field']['type'] ?? '', ['html', 'section', 'page'], true)) {
                foreach ($info['field']['choices'] as $choice) {
                    if (isset($choice['score']) && is_numeric($choice['score'])) { $evaluation_fields[] = (string) $field_id; break; }
                }
            }
        }
        return $config + ['form_id' => $id, 'form_key' => 'form-' . $id,
            'title' => (string) ($form['title'] ?? ''), 'questions' => $matches,
            'evaluation_fields' => $evaluation_fields, 'missing_items' => $unmatched,
            'ambiguous_fields' => $ambiguous, 'issues' => $issues, 'available_fields' => array_map(static function ($f) { return $f['label']; }, $fields)];
    }
    public static function save($id, $config) {
        if (!absint($id)) { return new WP_Error('tle_invalid_form', 'A valid form ID is required.'); }
        if (!is_array($config) || !isset($config['fields']) || !is_array($config['fields'])) {
            return new WP_Error('tle_invalid_profile', 'A profile must contain a fields object.');
        }
        $stage = $config['evaluation_stage'] ?? '';
        if (!is_string($stage) || ($stage !== '' && !preg_match('/^[a-z][a-z0-9_]*$/', $stage))) {
            return new WP_Error('tle_invalid_stage', 'Use a stage key such as pre_program or manager.');
        }
        $mode = $config['catalog_mode'] ?? 'document';
        if (!in_array($mode, ['document', 'custom'], true)) { return new WP_Error('tle_invalid_catalog_mode', 'Catalog mode must be document or custom.'); }
        $clean = ['catalog_mode' => $mode, 'enabled' => !empty($config['enabled']), 'evaluation_stage' => $stage,
            'scoring_confirmed' => !empty($config['scoring_confirmed']), 'fields' => [], 'question_overrides' => [], 'custom_items' => []];
        foreach (['participant_key', 'program', 'program_group', 'cohort', 'location', 'sector'] as $name) {
            $value = $config['fields'][$name] ?? '';
            if (!is_scalar($value) || ((string) $value !== '' && !preg_match('/^\d+(\.\d+)?$/', (string) $value))) {
                return new WP_Error('tle_invalid_field', 'Context field IDs must be numeric field or subfield IDs.');
            }
            $clean['fields'][$name] = (string) $value;
        }
        $custom = $config['custom_items'] ?? [];
        if (!is_array($custom)) { return new WP_Error('tle_invalid_custom_items', 'Custom items must be an array.'); }
        $catalog = self::catalog(); $document = self::item_index();
        foreach ($custom as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null) || !preg_match('/^custom\.[a-z0-9_.-]+$/', $item['id']) || isset($document[$item['id']]) || isset($clean['custom_items'][$item['id']])) {
                return new WP_Error('tle_invalid_custom_item', 'Custom items require unique IDs starting with custom.');
            }
            if (!is_string($item['section'] ?? null) || !is_string($item['capability'] ?? null) || !isset($catalog[$item['section']][$item['capability']]) || !is_string($item['text'] ?? null) || trim($item['text']) === '' || !is_bool($item['reverse'] ?? null)) {
                return new WP_Error('tle_invalid_custom_item', 'Provide question text, an existing section/capability and a boolean reverse direction.');
            }
            $clean['custom_items'][$item['id']] = ['id' => $item['id'], 'text' => sanitize_text_field($item['text']), 'section' => $item['section'], 'capability' => $item['capability'], 'reverse' => $item['reverse']];
        }
        $clean['custom_items'] = array_values($clean['custom_items']);
        $index = self::profile_item_index($clean);
        foreach ((array) ($config['question_overrides'] ?? []) as $field => $override) {
            if (!preg_match('/^\d+(\.\d+)?$/', (string) $field) || !is_array($override)) { return new WP_Error('tle_invalid_override', 'Invalid question override.'); }
            $item = $override['item_id'] ?? '';
            if (!is_string($item) || ($item !== '' && !isset($index[$item]))) { return new WP_Error('tle_invalid_item', 'Select a document or configured custom item ID.'); }
            $scoring = (array) ($override['scoring'] ?? []);
            foreach ($scoring as $name => $value) {
                if (!in_array($name, ['raw_min', 'raw_max', 'target_min', 'target_max', 'allowed_scores', 'excluded_scores', 'reverse'], true)) { return new WP_Error('tle_invalid_scoring', 'Unknown scoring setting.'); }
                if (in_array($name, ['allowed_scores', 'excluded_scores'], true)) {
                    if (!is_array($value) || count(array_filter($value, 'is_numeric')) !== count($value)) { return new WP_Error('tle_invalid_scoring', 'Score lists must be numeric arrays.'); }
                } elseif ($name === 'reverse') {
                    if (!is_bool($value)) { return new WP_Error('tle_invalid_scoring', 'Reverse must be true or false.'); }
                } elseif (!is_numeric($value) || !is_finite((float) $value)) { return new WP_Error('tle_invalid_scoring', 'Scoring bounds must be finite numbers.'); }
            }
            // The common report scale remains 1-7.
            if ((isset($scoring['target_min']) && (float) $scoring['target_min'] !== 1.0) || (isset($scoring['target_max']) && (float) $scoring['target_max'] !== 7.0)) { return new WP_Error('tle_invalid_scale', 'The shared report uses a 1-7 target scale.'); }
            $clean['question_overrides'][(string) $field] = ['item_id' => $item, 'scoring' => $scoring];
        }
        if ($clean['enabled'] && $stage === '') { return new WP_Error('tle_missing_stage', 'Enabled forms require an evaluation stage.'); }
        $profiles = self::stored(); $profiles[(string) absint($id)] = $clean;
        update_option(self::OPTION, $profiles, false);
        self::$cache = [];
        return $clean;
    }
}