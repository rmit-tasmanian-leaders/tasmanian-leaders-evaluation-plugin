<?php

/**
 * REST API
 *
 * Provides read-only access to evaluation data.
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
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Retrieve evaluation records.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public static function get_evaluations($request)
    {
        $limit = $request->get_param('limit');

        if (!$limit) {
            $limit = 50;
        }

        $evaluations = TLE_Evaluation_Database::get_evaluations($limit);

        return rest_ensure_response($evaluations);
    }
}

add_action(
    'rest_api_init',
    ['TLE_REST_API', 'register_routes']
);
