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
    /**
     * Register REST API routes.
     *
     * @return void
     */
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

    /**
     * Check whether the current user can access evaluation data.
     *
     * @return bool
     */
    public static function permissions_check()
    {
        return is_user_logged_in();
    }

    /**
     * Retrieve evaluation report data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function get_evaluations($request)
    {
        $records = [];

        foreach (['ELF-I', 'ELF-E', 'ELF-D'] as $form_key) {
            $records = array_merge(
                $records,
                TLE_Evaluation_Data_Service::get_evaluations($form_key)
            );
        }

        $filters = [];

        $program = $request->get_param('program');
        $cohort = $request->get_param('cohort');
        $evaluation_stage = $request->get_param('evaluation_stage');
        $comparison = $request->get_param('comparison');

        if ($program !== null && $program !== '') {
            $filters['program'] = sanitize_text_field($program);
        }

        if ($cohort !== null && $cohort !== '') {
            $filters['cohort'] = sanitize_text_field($cohort);
        }

        if ($evaluation_stage !== null && $evaluation_stage !== '') {
            $filters['evaluation_stage'] = sanitize_text_field(
                $evaluation_stage
            );
        }

        if ($comparison !== null && $comparison !== '') {
            $filters['comparison'] = sanitize_text_field($comparison);
        }

        $report = TLE_Reporting_Service::generate_report(
            $records,
            $filters
        );

        return rest_ensure_response($report);
    }
}

add_action(
    'rest_api_init',
    ['TLE_REST_API', 'register_routes']
);