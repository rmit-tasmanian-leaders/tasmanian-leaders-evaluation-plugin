<?php
/** Manager response distributions shared by browser and PDF consumers. */
if (!defined('ABSPATH')) { exit; }

class TLE_Manager_Report_Service
{
    private static function label_key($value) {
        return strtolower(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'))));
    }

    /** Choice scores are intentionally ignored: distributions describe selected answers. */
    private static function scale($field) {
        $choices = $field['choices'] ?? [];
        if (!is_array($choices) || !$choices) { return null; }
        $choices = array_values(array_filter($choices, static function ($choice) {
            return !is_array($choice) || !in_array(self::label_key($choice['text'] ?? ''), ['not applicable', 'n/a'], true);
        }));
        $labels = [];
        foreach ($choices as $choice) {
            if (!is_array($choice) || !is_scalar($choice['text'] ?? null)) { return null; }
            $labels[] = self::label_key($choice['text']);
        }
        $scales = [
            'agreement' => ['strongly disagree', 'disagree', 'neutral', 'agree', 'strongly agree'],
            'agreement_four' => ['strongly disagree', 'disagree', 'agree', 'strongly agree'],
            'agreement_four_with_neutral' => ['strongly disagree', 'disagree', 'neutral', 'agree'],
            'satisfaction' => ['extremely dissatisfied', 'somewhat dissatisfied', 'neutral', 'somewhat satisfied', 'extremely satisfied'],
            'yes_no' => ['no', 'yes'],
        ];
        // Preserve source display labels; tolerate the known source spelling error.
        $comparison = array_map(static function ($v) { return $v === 'somewhat disatisfied' ? 'somewhat dissatisfied' : $v; }, $labels);
        foreach ($scales as $name => $expected) {
            $a = $comparison; $b = $expected; sort($a); sort($b);
            if ($a !== $b) { continue; }
            $ordered = [];
            foreach ($expected as $key) { $ordered[] = $choices[array_search($key, $comparison, true)]; }
            return ['name' => $name, 'choices' => $ordered];
        }
        return null;
    }

    /** Aggregate one source field. Missing and unknown answers never dilute valid percentages. */
    public static function distribution($field_id, $label, $field, $entries, $rule = []) {
        $scale = self::scale($field);
        if (!$scale) { return null; }
        $positive = $rule['positive_choices'] ?? ($scale['name'] === 'yes_no' ? ['Yes'] : ($scale['name'] === 'satisfaction' ? ['Somewhat satisfied', 'Extremely satisfied'] : ['Agree', 'Strongly agree']));
        $positive = array_map([self::class, 'label_key'], $positive);
        $categories = []; $tokens = []; $ambiguous = [];
        foreach ($scale['choices'] as $i => $choice) {
            $text = (string) $choice['text'];
            $categories[] = ['label' => $text, 'count' => 0, 'percentage' => null, 'positive' => in_array(self::label_key($text), $positive, true)];
            $values = [$text];
            if (isset($choice['value']) && is_scalar($choice['value']) && (string) $choice['value'] !== '') { $values[] = (string) $choice['value']; }
            foreach ($values as $token) {
                $token = trim($token);
                if (isset($tokens[$token]) && $tokens[$token] !== $i) { $ambiguous[$token] = true; }
                $tokens[$token] = $i;
            }
        }
        $excluded_tokens = [];
        foreach ($field['choices'] as $choice) {
            if (in_array(self::label_key($choice['text'] ?? ''), ['not applicable', 'n/a'], true)) {
                $excluded_tokens[] = trim((string) $choice['text']);
                if (isset($choice['value']) && is_scalar($choice['value'])) { $excluded_tokens[] = trim((string) $choice['value']); }
            }
        }
        $excluded = 0; $valid = 0; $missing = 0; $invalid = 0; $positive_count = 0;
        foreach ($entries as $entry) {
            $raw = $entry[$field_id] ?? null;
            if ($raw === null || (is_scalar($raw) && trim((string) $raw) === '')) { $missing++; continue; }
            if (!is_scalar($raw)) { $invalid++; continue; }
            $raw = trim((string) $raw);
            if (!isset($tokens[$raw]) && strpos($raw, ':') !== false) {
                $parts = explode(':', $raw);
                $rows = is_array($field['gsurveyLikertRows'] ?? null) ? $field['gsurveyLikertRows'] : [];
                $row_values = array_map('strval', array_column($rows, 'value'));
                if (count($parts) === 2 && in_array($parts[0], $row_values, true)) { $raw = trim($parts[1]); }
            }
            if (in_array($raw, $excluded_tokens, true) && !isset($tokens[$raw])) { $excluded++; continue; }
            if (!isset($tokens[$raw]) || isset($ambiguous[$raw])) { $invalid++; continue; }
            $index = $tokens[$raw]; $valid++; $categories[$index]['count']++;
            if ($categories[$index]['positive']) { $positive_count++; }
        }
        foreach ($categories as &$category) { $category['percentage'] = $valid ? $category['count'] / $valid * 100.0 : null; } unset($category);
        return [
            'field_id' => (string) $field_id, 'statement' => (string) $label, 'scale' => $scale['name'],
            'categories' => $categories, 'answered_count' => $valid, 'missing_count' => $missing,
            'invalid_count' => $invalid, 'excluded_count' => $excluded, 'positive_count' => $positive_count,
            'positive_percentage' => $valid ? $positive_count / $valid * 100.0 : null,
            'positive_definition' => array_values(array_column(array_filter($categories, static function ($category) { return $category['positive']; }), 'label')),
        ];
    }

    public static function get_report($filters = [], $form_ids = []) {
        if (!is_array($filters) || !is_array($form_ids)) { return new WP_Error('tle_invalid_manager_request', 'Filters and form selections must be arrays.', ['status' => 400]); }
        foreach ($filters as $key => $value) {
            if (!in_array($key, ['participant_key', 'program_group', 'program', 'cohort'], true) || !is_scalar($value)) { return new WP_Error('tle_invalid_manager_filter', 'Invalid Manager report filter.', ['status' => 400]); }
            $filters[$key] = sanitize_text_field((string) $value);
        }
        if (!$form_ids) {
            $forms = TLE_Form_Registry::discover();
            if (is_wp_error($forms)) { return $forms; }
            foreach ($forms as $form) { if ($form['enabled'] && $form['evaluation_stage'] === 'manager') { $form_ids[] = $form['form_id']; } }
        }
        if (!$form_ids) { return new WP_Error('tle_no_manager_forms', 'Configure and enable a Manager form in Evaluation Forms.', ['status' => 422]); }
        foreach ($form_ids as $id) {
            if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/', (string) $id)) { return new WP_Error('tle_invalid_manager_form', 'Manager form IDs must be positive integers.', ['status' => 400]); }
        }
        $groups = []; $submissions = 0;
        foreach (array_unique($form_ids, SORT_REGULAR) as $id) {
            if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/', (string) $id)) { return new WP_Error('tle_invalid_manager_form', 'Manager form IDs must be positive integers.', ['status' => 400]); }
            $id = (int) $id; $config = TLE_Form_Registry::configuration($id);
            if (empty($config['enabled']) || $config['evaluation_stage'] !== 'manager') { return new WP_Error('tle_manager_setup_required', 'The selected form must be enabled with stage manager.', ['status' => 422]); }
            $form = TLE_Gravity_Forms_Provider::get_form($id);
            if (is_wp_error($form)) { return $form; }
            if (!is_array($form) || !is_array($form['fields'] ?? null)) { return new WP_Error('tle_manager_fields_unavailable', 'Manager form fields are unavailable.', ['status' => 502]); }
            $fields = TLE_Form_Registry::fields($form);
            foreach ($filters as $key => $value) {
                if ($value !== '' && (empty($config['fields'][$key]) || !isset($fields[$config['fields'][$key]]))) { return new WP_Error('tle_manager_context_unmapped', 'Map ' . $key . ' before filtering this Manager form.', ['status' => 422]); }
            }
            $program_values = [null];
            if (!empty($filters['program'])) {
                $program_values = method_exists('TLE_Report_Service', 'program_filter_values') ? TLE_Report_Service::program_filter_values($filters['program']) : [$filters['program']];
            }
            $entries = [];
            foreach (array_unique($program_values, SORT_REGULAR) as $program_value) {
                $search = ['status' => 'active']; $conditions = ['mode' => 'all'];
                foreach ($filters as $key => $value) {
                    if ($value === '') { continue; }
                    $conditions[] = ['key' => $config['fields'][$key], 'value' => $key === 'program' ? $program_value : $value, 'operator' => 'is'];
                }
                if (count($conditions) > 1) { $search['field_filters'] = $conditions; }
                $page = TLE_Gravity_Forms_Provider::get_all_entries($id, $search);
                if (is_wp_error($page)) { return $page; }
                foreach ($page as $entry) {
                    // Also enforce all selected filters locally; never attribute blank context.
                    $matches = true;
                    foreach ($filters as $key => $value) {
                        if ($value === '') { continue; }
                        $actual = $entry[$config['fields'][$key]] ?? '';
                        if (!is_scalar($actual) || trim((string) $actual) !== (string) ($key === 'program' ? $program_value : $value)) { $matches = false; break; }
                    }
                    if ($matches) { $entries[(string) $entry['id']] = $entry; }
                }
            }
            $context_entries = [];
            foreach ($entries as $entry) {
                $context = [];
                foreach (['program_group', 'program', 'cohort'] as $key) {
                    $value = $entry[$config['fields'][$key] ?? ''] ?? '';
                    $context[$key] = is_scalar($value) ? trim((string) $value) : '';
                }
                if (method_exists('TLE_Report_Service', 'canonical_program')) { $context['program'] = TLE_Report_Service::canonical_program($context['program']); }
                $key = json_encode($context);
                if (!isset($context_entries[$key])) { $context_entries[$key] = ['context' => $context, 'entries' => []]; }
                $context_entries[$key]['entries'][] = $entry;
            }
            // With no matching entries, retain configured statement metadata without ratings.
            if (!$context_entries) { $context_entries['empty'] = ['context' => array_intersect_key($filters, array_flip(['program_group', 'program', 'cohort'])), 'entries' => []]; }
            foreach ($context_entries as $batch) {
                $rows = []; $skipped = [];
                foreach ($fields as $field_id => $info) {
                    $rule = $config['manager_questions'][(string) $field_id] ?? [];
                    if (($rule['enabled'] ?? true) === false) { continue; }
                    // Non-Likert compound subinputs are not Manager statement fields.
                    if (strpos((string) $field_id, '.') !== false && empty($info['field']['gsurveyLikertEnableMultipleRows'])) { continue; }
                    if (isset($rule['positive_choices'])) {
                        $scale = self::scale($info['field']);
                        $labels = $scale ? array_map([self::class, 'label_key'], array_column($scale['choices'], 'text')) : [];
                        foreach ($rule['positive_choices'] as $positive_label) {
                            if (!in_array(self::label_key($positive_label), $labels, true)) { return new WP_Error('tle_manager_positive_choice_unavailable', 'A configured positive choice is not present in Manager field ' . $field_id . '.', ['status' => 422]); }
                        }
                    }
                    $row = self::distribution($field_id, $info['label'], $info['field'], $batch['entries'], $rule);
                    if ($row === null) { $skipped[] = (string) $field_id; continue; }
                    $rows[] = $row;
                }
                if (!$rows) { return new WP_Error('tle_manager_questions_unavailable', 'No supported Manager response scales were found.', ['status' => 422]); }
                $key = json_encode($batch['context']);
                if (!isset($groups[$key])) { $groups[$key] = ['context' => $batch['context'], 'series' => []]; }
                $groups[$key]['series'][] = ['form_id' => $id, 'title' => (string) ($form['title'] ?? ''), 'evaluation_stage' => 'manager', 'submission_count' => count($batch['entries']), 'status' => $batch['entries'] ? 'available' : 'no_matching_responses', 'questions' => $rows, 'skipped_field_ids' => $skipped];
            }
            $submissions += count($entries);
        }
        return ['filters' => $filters, 'submission_count' => $submissions, 'status' => $submissions ? 'available' : 'no_matching_responses', 'groups' => array_values($groups)];
    }
}
