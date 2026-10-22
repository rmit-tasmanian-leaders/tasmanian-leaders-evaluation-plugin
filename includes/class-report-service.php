<?php
/** One report retrieval entry point for REST, dashboard and PDF callers. */
if (!defined('ABSPATH')) { exit; }
class TLE_Report_Service
{
    /**
     * Return the canonical dashboard/reporting name for a program.
     */
    public static function canonical_program($program) {
        $program = sanitize_text_field((string) $program);

        $aliases = [
            'I-LEAD Women in Community Services' =>
                'I-LEAD Women in Community',
            'I-LEAD Tassie Wine' =>
                'I-LEAD Wine Industry',
        ];

        return $aliases[$program] ?? $program;
    }

    /**
     * Return all Gravity Forms values that represent a program.
     */
    public static function program_filter_values($program) {
        $canonical = self::canonical_program($program);

        $values = [
            'I-LEAD Women in Community' => [
                'I-LEAD Women in Community',
                'I-LEAD Women in Community Services',
            ],
            'I-LEAD Wine Industry' => [
                'I-LEAD Wine Industry',
                'I-LEAD Tassie Wine',
            ],
        ];

        return $values[$canonical] ?? [$canonical];
    }

    public static function get_report($filters = [], $form_ids = []) {
        if (!is_array($filters) || !is_array($form_ids)) { return new WP_Error('tle_invalid_report', 'Report filters and form IDs must be arrays.', ['status' => 400]); }
        foreach ($filters as $key => $value) {
            if (!in_array($key, ['participant_key', 'program', 'program_group', 'cohort', 'evaluation_stage', 'comparison', 'form_id', 'form_key'], true) || !is_scalar($value)) { return new WP_Error('tle_invalid_filter', 'Invalid report filter.', ['status' => 400]); }
            $filters[$key] = sanitize_text_field((string) $value);
        }
        if (
            isset($filters['program']) &&
            $filters['program'] !== ''
        ) {
            $filters['program'] =
                self::canonical_program(
                    $filters['program']
                );
        }
        if (isset($filters['form_id']) && !$form_ids) { $form_ids = [$filters['form_id']]; }
        if (isset($filters['form_key']) && !$form_ids) { $form_ids = [$filters['form_key']]; }
        $explicit = !empty($form_ids); $profiles = [];
        if (!$explicit) {
            $forms = TLE_Form_Registry::discover();
            if (is_wp_error($forms)) { return $forms; }
            foreach ($forms as $form) {
                if (!$form['enabled'] || $form['evaluation_stage'] === '') { continue; }
                if (!empty($filters['evaluation_stage']) && $form['evaluation_stage'] !== $filters['evaluation_stage']) { continue; }
                $form_ids[] = $form['form_id'];
            }
        }
        if (!$form_ids) { return new WP_Error('tle_no_configured_forms', 'No enabled forms match this selection. Configure Evaluation Forms first.', ['status' => 422]); }
        foreach ($form_ids as $key) {
            if (!is_scalar($key)) { return new WP_Error('tle_invalid_form', 'Form selections must be IDs or keys.', ['status' => 400]); }
            $profile = TLE_Form_Registry::profile($key);
            if (is_wp_error($profile)) { return $profile; }
            if (empty($profile['enabled']) || empty($profile['evaluation_stage'])) { return new WP_Error('tle_form_setup_required', 'Configure and enable form ' . $profile['form_id'] . ' in Evaluation Forms.', ['status' => 422]); }
            if (!empty($filters['evaluation_stage']) && $profile['evaluation_stage'] !== $filters['evaluation_stage']) { return new WP_Error('tle_stage_mismatch', 'A selected form does not match the selected stage.', ['status' => 400]); }
            foreach (['participant_key', 'program', 'program_group', 'cohort'] as $field) {
                if (isset($filters[$field]) && $filters[$field] !== '' && empty($profile['fields'][$field])) {
                    return new WP_Error('tle_missing_context_mapping', 'Form ' . $profile['form_id'] . ' has no mapping for ' . $field . '. Configure it before filtering.', ['status' => 422]);
                }
            }
            $profiles[$profile['form_id']] = $profile;
        }
        $records = [];

        foreach ($profiles as $id => $profile) {
            $base_filters = ['mode' => 'all'];

            foreach (
                ['participant_key', 'program_group', 'cohort']
                as $field
            ) {
                if (
                    isset($filters[$field]) &&
                    $filters[$field] !== ''
                ) {
                    $base_filters[] = [
                        'key' => $profile['fields'][$field],
                        'value' => $filters[$field],
                        'operator' => 'is',
                    ];
                }
            }

            $program_values =
                isset($filters['program']) &&
                $filters['program'] !== ''
                    ? self::program_filter_values(
                        $filters['program']
                    )
                    : [null];

            foreach ($program_values as $program_value) {
                $search = ['status' => 'active'];
                $field_filters = $base_filters;

                if ($program_value !== null) {
                    $field_filters[] = [
                        'key' => $profile['fields']['program'],
                        'value' => $program_value,
                        'operator' => 'is',
                    ];
                }

                if (count($field_filters) > 1) {
                    $search['field_filters'] =
                        $field_filters;
                }

                $form_records =
                    TLE_Evaluation_Data_Service::
                        get_all_evaluations(
                            $id,
                            $search
                        );

                if (is_wp_error($form_records)) {
                    return $form_records;
                }

                foreach ($form_records as &$record) {
                    if (
                        isset($record['program']) &&
                        $record['program'] !== ''
                    ) {
                        $record['program'] =
                            self::canonical_program(
                                $record['program']
                            );
                    }
                }
                unset($record);

                $records = array_merge(
                    $records,
                    $form_records
                );
            }
        }
        $report = TLE_Reporting_Service::generate_report($records, $filters);
        $report['selected_forms'] = array_map(static function ($p) {
            return ['form_id' => $p['form_id'], 'title' => $p['title'], 'evaluation_stage' => $p['evaluation_stage'],
                'missing_items' => $p['missing_items'], 'ambiguous_fields' => $p['ambiguous_fields'], 'issues' => $p['issues']];
        }, array_values($profiles));
        $report['retrieved_stages'] = array_values(array_unique(array_column($profiles, 'evaluation_stage')));
        return $report;
    }
}
function tle_get_report_data($program, $cohort, $form_ids = [], $extra_filters = []) {
    $report = TLE_Report_Service::get_report(array_replace((array) $extra_filters, ['program' => $program, 'cohort' => $cohort]), $form_ids);
    if (is_wp_error($report)) { return $report; }
    if (!$report['record_count']) { return new WP_Error('tle_no_report_data', 'No answers match the selected forms and filters.', ['status' => 404]); }
    if (count($report['groups']) !== 1) { return new WP_Error('tle_multiple_report_groups', 'Select one program group for this PDF adapter, or render the groups from the full report separately.', ['status' => 422]); }
    $group = $report['groups'][0];
    $stage_counts = array_count_values(array_column($group['series'], 'evaluation_stage'));
    if (max($stage_counts) > 1) { return new WP_Error('tle_multiple_stage_series', 'Multiple forms share a stage. Render the separate series from the full report rather than the legacy PDF rows.', ['status' => 422]); }
    $has_scores = false;
    foreach ($group['capability_averages'] as $caps) { foreach ($caps as $stages) { if ($stages) { $has_scores = true; } } }
    if (!$has_scores) { return new WP_Error('tle_no_report_scores', 'No valid capability scores are available for this selection.', ['status' => 422]); }
    $data = [
        'program' => $program, 'cohort' => $cohort, 'program_group' => $group['context']['program_group'],
        'report_type' => 'Leadership Capability Report', 'evaluation_point' => implode(', ', $report['retrieved_stages']),
        'comparison' => implode(' / ', $report['retrieved_stages']), 'benchmark' => 'Not available', 'average_score' => 'Not available',
        'social_desirability' => ['impression_management' => 'Not available', 'self_deception' => 'Not available'],
        'participant_count' => $report['participant_count'], 'submission_count' => $report['submission_count'], 'record_count' => $report['record_count'],
        'scoring_status' => $report['scoring_status'], 'scoring_note' => 'Document-based question mappings. Scale conversion and averaging policies require confirmation.',
        'available_stages' => array_map(static function ($stage) { return $stage === 'pre_program' ? 'pre' : $stage; }, $report['retrieved_stages']),
        'series' => $group['series'], 'report' => $report,
    ];
    foreach ($group['capability_averages'] as $section => $caps) {
        $data[$section] = [];
        foreach ($caps as $cap => $stages) {
            $data[$section][] = ['measure' => $cap, 'pre' => $stages['pre_program'] ?? null,
                'completion' => $stages['completion'] ?? null, 'delay' => $stages['delay'] ?? null,
                'manager' => $stages['manager'] ?? null, 'benchmark' => null, 'stages' => $stages];
        }
    }
    return $data;
}
