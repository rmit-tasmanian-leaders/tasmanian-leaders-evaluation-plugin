<?php
/** Document-based capability reporting, isolated by form and cohort context. */
if (!defined('ABSPATH')) { exit; }
class TLE_Reporting_Service
{
    public static function filter_records($records, $filters = []) {
        return array_values(array_filter($records, static function ($record) use ($filters) {
            foreach (['participant_key', 'program', 'program_group', 'cohort', 'evaluation_stage', 'form_id', 'form_key'] as $key) {
                if (isset($filters[$key]) && (string) $filters[$key] !== '' && (string) ($record[$key] ?? '') !== (string) $filters[$key]) { return false; }
            }
            return true;
        }));
    }
    public static function calculate_question_averages($records, $round_results = true) {
        $buckets = [];
        foreach ($records as $record) {
            $score = $record['score'] ?? null;
            if ($score === null || !is_numeric($score) || !is_finite((float) $score)) { continue; }
            // Question numbers are local to each form; they are never global identifiers.
            $key = ($record['form_key'] ?? ('form-' . ($record['form_id'] ?? 0))) . ':' . ($record['question'] ?? '');
            $stage = $record['evaluation_stage'] ?? '';
            $buckets[$key][$stage][] = (float) $score;
        }
        $results = [];
        foreach ($buckets as $key => $stages) {
            foreach ($stages as $stage => $scores) {
                $value = array_sum($scores) / count($scores);
                $results[$key][$stage] = $round_results ? round($value, 2) : $value;
            }
        }
        return $results;
    }
    public static function get_capability_questions($form_key = null) {
        $result = [];
        foreach (TLE_Form_Registry::catalog() as $section => $caps) {
            foreach ($caps as $cap => $items) { $result[$section][$cap] = array_column($items, 'id'); }
        }
        if ($form_key !== null) {
            $profile = TLE_Form_Registry::profile($form_key);
            if (is_wp_error($profile)) { return []; }
            foreach ($result as $section => &$caps) { foreach ($caps as &$questions) { $questions = []; } unset($questions); } unset($caps);
            foreach ($profile['questions'] as $field => $item) { $result[$item['section']][$item['capability']][] = (string) $field; }
        }
        return $result;
    }
    public static function get_reverse_scored_questions($form_key = null) {
        if ($form_key !== null) {
            $profile = TLE_Form_Registry::profile($form_key);
            if (is_wp_error($profile)) { return []; }
            $result = [];
            foreach ($profile['questions'] as $id => $item) { if (!empty($item['scoring']['reverse'])) { $result[] = (string) $id; } }
            return $result;
        }
        return array_keys(array_filter(TLE_Form_Registry::item_index(), static function ($item) { return !empty($item['reverse']); }));
    }
    public static function prepare_capability_score($question, $score, $rule = null) {
        // Field IDs alone cannot determine a scoring rule across arbitrary forms.
        if (!is_array($rule) || $score === null || !is_numeric($score) || !is_finite((float) $score)) { return null; }
        $score = (float) $score;
        foreach ($rule['excluded_scores'] ?? [] as $excluded) { if ($score === (float) $excluded) { return null; } }
        if (!empty($rule['allowed_scores'])) {
            $allowed = array_map('floatval', $rule['allowed_scores']);
            if (!in_array($score, $allowed, true)) { return null; }
        }
        $min = $rule['raw_min'] ?? null; $max = $rule['raw_max'] ?? null;
        if (!is_numeric($min) || !is_numeric($max) || !is_finite((float) $min) || !is_finite((float) $max) || $max <= $min || $score < $min || $score > $max) { return null; }
        $target_min = (float) ($rule['target_min'] ?? 1); $target_max = (float) ($rule['target_max'] ?? 7);
        if (!is_finite($target_min) || !is_finite($target_max) || $target_max <= $target_min) { return null; }
        $value = $target_min + ($score - $min) * ($target_max - $target_min) / ($max - $min);
        return !empty($rule['reverse']) ? $target_min + $target_max - $value : $value;
    }
    public static function prepare_capability_records($records) {
        $result = [];
        foreach ($records as $record) {
            if (empty($record['item_id']) || empty($record['capability'])) { continue; }
            $record['score'] = self::prepare_capability_score($record['question'], $record['score'] ?? null, $record['scoring_rule'] ?? null);
            $result[] = $record;
        }
        return $result;
    }
    public static function calculate_capability_averages($question_averages, $mapping = null) {
        $result = [];
        foreach ($mapping ?? self::get_capability_questions() as $section => $caps) {
            foreach ($caps as $cap => $items) {
                $result[$section][$cap] = []; $stages = [];
                foreach ($items as $item) {
                    foreach ($question_averages[$item] ?? [] as $stage => $mean) {
                        if (is_numeric($mean)) { $stages[$stage][] = (float) $mean; }
                    }
                }
                foreach ($stages as $stage => $means) { $result[$section][$cap][$stage] = round(array_sum($means) / count($means), 2); }
            }
        }
        return $result;
    }
    public static function calculate_change($question_averages, $from_stage, $to_stage) {
        $result = [];
        foreach ($question_averages as $item => $stages) {
            if (isset($stages[$from_stage], $stages[$to_stage])) { $result[$item] = round($stages[$to_stage] - $stages[$from_stage], 2); }
        }
        return $result;
    }
    private static function context($record) {
        $result = [];
        foreach (['program_group', 'program', 'cohort'] as $key) { $result[$key] = (string) ($record[$key] ?? ''); }
        return $result;
    }
    private static function counts($records) {
        $participants = []; $submissions = [];
        foreach ($records as $r) {
            $context = json_encode(self::context($r));
            if ((string) ($r['participant_key'] ?? '') !== '') { $participants[$context . ':' . $r['participant_key']] = true; }
            $submissions[($r['form_id'] ?? 0) . ':' . ($r['submission_id'] ?? 0)] = true;
        }
        return ['participant_count' => count($participants), 'submission_count' => count($submissions), 'record_count' => count($records)];
    }
    public static function generate_report($records, $filters = []) {
        $records = self::filter_records($records, $filters);
        $groups = [];
        foreach ($records as $record) {
            $context = self::context($record); $key = json_encode($context);
            if (!isset($groups[$key])) { $groups[$key] = ['context' => $context, 'records' => []]; }
            $groups[$key]['records'][] = $record;
        }
        $group_reports = []; $all_stages = []; $all_confirmed = true;
        foreach ($groups as $group) {
            $forms = [];
            foreach ($group['records'] as $record) { $forms[$record['form_id']][] = $record; }
            $series = []; $stage_forms = [];
            foreach ($forms as $id => $form_records) {
                $prepared = self::prepare_capability_records($form_records); $items = []; $response_counts = [];
                foreach ($prepared as $record) {
                    if (empty($record['scoring_confirmed'])) { $all_confirmed = false; }
                    if ($record['score'] === null) { continue; }
                    $items[$record['item_id']][] = $record['score'];
                }
                $stage = $form_records[0]['evaluation_stage']; $means = [];
                foreach ($items as $item => $scores) { $means[$item][$stage] = array_sum($scores) / count($scores); $response_counts[$item] = count($scores); }
                $mapping = self::get_capability_questions();
                foreach ($prepared as $record) {
                    $section = $record['section']; $cap = $record['capability']; $item = $record['item_id'];
                    if (!in_array($item, $mapping[$section][$cap] ?? [], true)) { $mapping[$section][$cap][] = $item; }
                }
                $caps = self::calculate_capability_averages($means, $mapping);
                $profile = TLE_Form_Registry::profile($id);
                $diagnostics = is_wp_error($profile) ? ['profile_error' => $profile->get_error_code()] : ['missing_items' => $profile['missing_items'], 'ambiguous_fields' => $profile['ambiguous_fields'], 'issues' => $profile['issues']];
                $series[] = ['form_id' => (int) $id, 'form_key' => 'form-' . $id, 'evaluation_stage' => $stage,
                    'catalog_mode' => is_wp_error($profile) ? null : ($profile['catalog_mode'] ?? 'document'),
                    'capability_averages' => $caps, 'adjusted_item_averages' => $means,
                    'item_response_counts' => $response_counts, 'mapping_diagnostics' => $diagnostics] + self::counts($form_records);
                $stage_forms[$stage][] = $caps; $all_stages[$stage] = true;
            }
            $combined = self::calculate_capability_averages([]);
            foreach ($stage_forms as $stage => $form_caps) {
                // Multiple forms at one stage remain separate report series.
                if (count($form_caps) !== 1) { continue; }
                foreach ($form_caps[0] as $section => $caps) { foreach ($caps as $cap => $stages) { if (isset($stages[$stage])) { $combined[$section][$cap][$stage] = $stages[$stage]; } } }
            }
            $group_reports[] = ['context' => $group['context'], 'series' => $series, 'question_averages' => self::calculate_question_averages($group['records']), 'capability_averages' => $combined] + self::counts($group['records']);
        }
        $capabilities = count($group_reports) === 1 ? $group_reports[0]['capability_averages'] : self::calculate_capability_averages([]);
        $status = $all_confirmed && $records ? 'configured_adjusted' : 'provisional_adjusted';
        return ['schema_version' => '2.0', 'filters' => $filters,
            'comparison' => $filters['comparison'] ?? 'Selected evaluation stages',
            'question_averages' => count($group_reports) <= 1 ? self::calculate_question_averages($records) : [],
            'capability_averages' => $capabilities, 'groups' => $group_reports,
            'retrieved_stages' => array_keys($all_stages), 'scoring_status' => $status,
            'capability_scoring' => ['status' => $status, 'scale_min' => 1, 'scale_max' => 7,
                'supported_stages' => array_keys($all_stages), 'question_weighting' => 'equal',
                'missing_questions' => 'skip_with_coverage', 'duplicate_submissions' => 'include_all',
                'scale_method' => 'linear rescale valid scored categories to 1-7; document reverse directions',
                'mapping_source' => 'Document catalog or per-form custom items; inspect series catalog_mode'],
            'change' => [], 'records' => $records,
            'limitations' => ['Absent questions are excluded and listed in coverage diagnostics.',
                'Repeated submissions are included; participant-first weighting is not applied.',
                'Scale conversion is provisional until confirmed per form.',
                'Different forms at the same stage remain separate series.',
                'Different program/year/group contexts remain separate groups.',
                'No participant-linked growth or benchmark is calculated.']
        ] + self::counts($records);
    }
}