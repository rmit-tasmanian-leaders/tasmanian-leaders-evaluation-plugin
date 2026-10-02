<?php

/**
 * Evaluation Data Service
 *
 * Normalises evaluation data for reporting, dashboard and PDF components.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_Evaluation_Data_Service
{
    /**
     * Create a normalised evaluation record.
     *
     * @param array $data Raw evaluation data.
     * @return array
     */
    public static function normalise_record($data)
    {
        return [
            'participant_key' => isset($data['participant_key'])
                ? sanitize_text_field($data['participant_key'])
                : '',

            'program' => isset($data['program'])
                ? sanitize_text_field($data['program'])
                : '',

            'program_group' => isset($data['program_group'])
                ? sanitize_text_field($data['program_group'])
                : '',

            'cohort' => isset($data['cohort'])
                ? sanitize_text_field($data['cohort'])
                : '',

            'evaluation_stage' => isset($data['evaluation_stage'])
                ? sanitize_text_field($data['evaluation_stage'])
                : '',

            'submission_id' => isset($data['submission_id'])
                ? absint($data['submission_id'])
                : 0,

            'submission_date' => isset($data['submission_date'])
                ? sanitize_text_field($data['submission_date'])
                : '',

            'capability' => isset($data['capability'])
                ? sanitize_text_field($data['capability'])
                : '',

            'question' => isset($data['question'])
                ? sanitize_text_field($data['question'])
                : '',

            'score' => isset($data['score']) && $data['score'] !== null
                ? floatval($data['score'])
                : null,

            'response' => isset($data['response'])
                ? sanitize_textarea_field($data['response'])
                : '',

            'location' => isset($data['location'])
                ? sanitize_text_field($data['location'])
                : '',

            'sector' => isset($data['sector'])
                ? sanitize_text_field($data['sector'])
                : '',
        ];
    }

    /**
     * Validate a normalised evaluation record.
     *
     * Participant context is optional because some forms
     * do not currently provide shared identity/context fields.
     *
     * @param array $record Normalised evaluation record.
     * @return bool
     */
    public static function validate_record($record)
    {
        if (!is_array($record)) {
            return false;
        }

        $required_fields = [
            'evaluation_stage',
            'submission_id',
            'submission_date',
            'question',
            'score',
        ];

        foreach ($required_fields as $field) {
            if (!array_key_exists($field, $record)) {
                return false;
            }
        }

        if ($record['evaluation_stage'] === '') {
            return false;
        }

        if ($record['submission_id'] < 1) {
            return false;
        }

        if ($record['submission_date'] === '') {
            return false;
        }

        if ($record['question'] === '') {
            return false;
        }

        if (
            $record['score'] !== null &&
            !is_numeric($record['score'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve one page of normalised evaluation records.
     *
     * Preserves the existing interface.
     * Invalid records are excluded.
     *
     * @return array
     */
    public static function get_evaluations(
        $form_key,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        if (!TLE_Gravity_Forms_Provider::is_available()) {
            return [];
        }

        $records = TLE_Gravity_Forms_Provider::get_normalised_entries(
            $form_key,
            $search_criteria,
            $sorting,
            $paging
        );

        if (!is_array($records)) {
            return [];
        }

        return array_values(
            array_filter(
                $records,
                [self::class, 'validate_record']
            )
        );
    }

    /**
     * Retrieve all matching evaluation records for reporting.
     *
     * Retrieval or validation failures return an explicit error.
     * Callers must check is_wp_error() before using the result.
     *
     * @return array|WP_Error
     */
    public static function get_all_evaluations(
        $form_key,
        $search_criteria = [],
        $page_size = 100
    ) {
        if (!TLE_Gravity_Forms_Provider::is_available()) {
            return new WP_Error(
                'tle_provider_unavailable',
                'The Gravity Forms REST connection is not configured.',
                ['status' => 503]
            );
        }

        $records = TLE_Gravity_Forms_Provider::get_all_normalised_entries(
            $form_key,
            $search_criteria,
            $page_size
        );

        if (is_wp_error($records)) {
            return $records;
        }

        foreach ($records as $record) {
            if (!self::validate_record($record)) {
                return new WP_Error(
                    'tle_invalid_evaluation_record',
                    'An evaluation record failed validation. '
                        . 'The report was stopped to avoid incomplete results.',
                    ['status' => 502]
                );
            }
        }

        return $records;
    }
}