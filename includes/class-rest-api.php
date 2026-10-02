<?php

/**
 * REST API
 *
 * Provides read-only access to evaluation reporting data.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_REST_API
{
    public static function register_routes()
    {
        register_rest_route(
            'tle/v1',
            '/evaluations',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_evaluations'],
                'permission_callback' => [self::class, 'permissions_check'],
            ]
        );
    }

    public static function permissions_check()
    {
        return is_user_logged_in();
    }

    /**
     * Retrieve a report using complete evaluation datasets.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public static function get_evaluations($request)
    {
        $filters = [];

        foreach (
            ['program', 'cohort', 'evaluation_stage', 'comparison']
            as $parameter
        ) {
            $value = $request->get_param($parameter);

            if ($value === null || $value === '') {
                continue;
            }

            if (!is_scalar($value)) {
                return new WP_Error(
                    'tle_invalid_filter',
                    'Report filters must be text values.',
                    ['status' => 400]
                );
            }

            $filters[$parameter] = sanitize_text_field($value);
        }

        $supported_forms = [
            'pre_program' => 'ELF-I',
            'completion' => 'ELF-E',
            'delay' => 'ELF-D',
        ];

        if (
            isset($filters['evaluation_stage']) &&
            !isset($supported_forms[$filters['evaluation_stage']])
        ) {
            return new WP_Error(
                'tle_invalid_evaluation_stage',
                'Choose pre_program, completion or delay.',
                ['status' => 400]
            );
        }

        $records = [];
        $retrieved_stages = [];

        foreach ($supported_forms as $stage => $form_key) {
            if (
                isset($filters['evaluation_stage']) &&
                $filters['evaluation_stage'] !== $stage
            ) {
                continue;
            }

            $form_records = TLE_Evaluation_Data_Service::get_all_evaluations(
                $form_key,
                ['status' => 'active']
            );

            if (is_wp_error($form_records)) {
                return new WP_Error(
                    'tle_report_retrieval_failed',
                    'Could not retrieve the complete '
                        . $stage . ' evaluation dataset.',
                    ['status' => 502]
                );
            }

            $records = array_merge($records, $form_records);
            $retrieved_stages[] = $stage;
        }

        $report = TLE_Reporting_Service::generate_report(
            $records,
            $filters
        );

        $report['scoring_status'] = $report['capability_scoring']['status'];
        $report['retrieved_stages'] = $retrieved_stages;

        $report['limitations'] = [
            'Capability mappings and scoring rules are provisional.',
            'Adjusted capability scoring currently supports Form 37 only.',
            'Q13 uses equal category intervals rescaled to 1-7.',
            'Q14 excludes N/A and converts agreement scores to 1-7.',
            'Q26 and Q37.1 scoring direction remains unresolved.',
            'Forms 40 and 43 lack shared participant, program and cohort fields.',
            'Program/cohort filters exclude records without matching context.',
            'Question averages and change values use raw scores.',
            'Stage differences do not establish participant-linked change.',
            'Form 50 is pending field mapping and integration.',
        ];

        return rest_ensure_response($report);
    }
}

add_action(
    'rest_api_init',
    ['TLE_REST_API', 'register_routes']
);