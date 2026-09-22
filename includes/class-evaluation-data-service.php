<?php

/**
 * Evaluation Data Service
 *
 * Normalises evaluation data into a consistent structure
 * for reporting, dashboard, and PDF components.
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

            'score' => isset($data['score'])
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
     * Retrieve normalised evaluation records from Gravity Forms.
     *
     * @param string $form_key Gravity Forms mapping key.
     * @param array  $search_criteria GFAPI search criteria.
     * @param array  $sorting GFAPI sorting options.
     * @param array  $paging GFAPI paging options.
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

        return TLE_Gravity_Forms_Provider::get_normalised_entries(
            $form_key,
            $search_criteria,
            $sorting,
            $paging
        );
    }
}
