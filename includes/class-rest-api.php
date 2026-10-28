<?php

if (!defined('ABSPATH')) {
    exit;
}

class TLE_REST_API
{
    public static function permissions_check()
    {
        return is_user_logged_in();
    }

    public static function setup_permissions_check()
    {
        return current_user_can('manage_options');
    }

    public static function register_routes()
    {
        register_rest_route(
            'tle/v1',
            '/manager-evaluations',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_manager_evaluations'],
                'permission_callback' => [
                    self::class,
                    'permissions_check',
                ],
            ]
        );

        register_rest_route(
            'tle/v1',
            '/evaluations',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_evaluations'],
                'permission_callback' => [
                    self::class,
                    'permissions_check',
                ],
            ]
        );

        register_rest_route(
            'tle/v1',
            '/comparison-options',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_comparison_options'],
                'permission_callback' => [
                    self::class,
                    'permissions_check',
                ],
            ]
        );

        register_rest_route(
            'tle/v1',
            '/forms',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_forms'],
                'permission_callback' => [
                    self::class,
                    'setup_permissions_check',
                ],
            ]
        );

        register_rest_route(
            'tle/v1',
            '/forms/(?P<id>\d+)/profile',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [self::class, 'get_profile'],
                    'permission_callback' => [
                        self::class,
                        'setup_permissions_check',
                    ],
                ],
                [
                    'methods' => 'POST',
                    'callback' => [self::class, 'save_profile'],
                    'permission_callback' => [
                        self::class,
                        'setup_permissions_check',
                    ],
                ],
            ]
        );
    }

    public static function get_manager_evaluations($request)
    {
        $filters = [];

        foreach (
            ['participant_key', 'program', 'program_group', 'cohort']
            as $key
        ) {
            $value = $request->get_param($key);

            if ($value === null || $value === '') {
                continue;
            }

            if (!is_scalar($value)) {
                return new WP_Error(
                    'tle_invalid_manager_filter',
                    'Manager filters must be text.',
                    ['status' => 400]
                );
            }

            $filters[$key] = sanitize_text_field((string) $value);
        }

        $ids = $request->get_param('form_ids');

        if ($ids === null) {
            $ids = [];
        }

        return rest_ensure_response(
            TLE_Manager_Report_Service::get_report($filters, $ids)
        );
    }

    public static function get_forms($request)
    {
        $data = TLE_Form_Registry::discover();

        return is_wp_error($data)
            ? $data
            : rest_ensure_response($data);
    }

    public static function get_profile($request)
    {
        $data = TLE_Form_Registry::profile($request['id']);

        return is_wp_error($data)
            ? $data
            : rest_ensure_response($data);
    }

    public static function save_profile($request)
    {
        $existing = TLE_Form_Registry::profile($request['id']);

        if (is_wp_error($existing)) {
            return $existing;
        }

        $data = TLE_Form_Registry::save(
            $request['id'],
            $request->get_json_params()
        );

        if (is_wp_error($data)) {
            return $data;
        }

        delete_transient('tle_comparison_options_v1');

        return rest_ensure_response($data);
    }

    /**
     * Return program/cohort options with usable scores in all three stages.
     *
     * Uses the same resolved records and scoring as report generation.
     */
    public static function get_comparison_options($request)
    {
        $required_stages = [
            'pre_program',
            'completion',
            'delay',
        ];

        $forms = TLE_Form_Registry::discover();

        if (is_wp_error($forms)) {
            return $forms;
        }

        $form_ids = [];
        $configurations = [];

        foreach ($forms as $form) {
            if (
                empty($form['enabled']) ||
                empty($form['form_id']) ||
                !in_array(
                    $form['evaluation_stage'] ?? '',
                    $required_stages,
                    true
                )
            ) {
                continue;
            }

            $id = absint($form['form_id']);

            $form_ids[] = $id;
            $configurations[$id] =
                TLE_Form_Registry::configuration($id);
        }

        sort($form_ids, SORT_NUMERIC);
        ksort($configurations, SORT_NUMERIC);

        /*
         * Retain the existing transient name so the administrator
         * settings page can continue clearing it.
         *
         * The new cache structure rejects old cached option lists.
         */
        $cache_key = 'tle_comparison_options_v1';

        $fingerprint = hash(
            'sha256',
            wp_json_encode([
                'version' => 2,
                'api_source' => defined('TLE_GF_API_BASE_URL')
                    ? TLE_GF_API_BASE_URL
                    : '',
                'form_configurations' => $configurations,
                'historical_rules' => get_option(
                    'tle_historical_context_rules_v1',
                    []
                ),
                'catalog' => TLE_Form_Registry::catalog(),
            ])
        );

        $cached = get_transient($cache_key);

        if (
            is_array($cached) &&
            isset($cached['fingerprint'], $cached['options']) &&
            $cached['fingerprint'] === $fingerprint &&
            is_array($cached['options'])
        ) {
            return rest_ensure_response([
                'options' => $cached['options'],
                'cached' => true,
            ]);
        }

        $options = [];

        if ($form_ids) {
            /*
             * Explicit form selection limits discovery to the three
             * evaluation stages. Historical context is resolved by
             * the provider before report grouping and calculation.
             */
            $report = TLE_Report_Service::get_report([], $form_ids);

            if (is_wp_error($report)) {
                return $report;
            }

            $contexts_per_selection = [];

            foreach ($report['groups'] as $group) {
                $context = $group['context'] ?? [];

                $program = trim(
                    (string) ($context['program'] ?? '')
                );

                $cohort = trim(
                    (string) ($context['cohort'] ?? '')
                );

                if ($program === '' || $cohort === '') {
                    continue;
                }

                $selection_key = wp_json_encode([
                    'program' => $program,
                    'cohort' => $cohort,
                ]);

                /*
                 * The current frontend selects program and cohort,
                 * not program_group. Do not combine separate groups.
                 */
                $contexts_per_selection[$selection_key] =
                    ($contexts_per_selection[$selection_key] ?? 0) + 1;

                $available_stages = [];

                foreach (
                    $group['capability_averages'] ?? []
                    as $capabilities
                ) {
                    foreach ($capabilities as $scores) {
                        foreach ($required_stages as $stage) {
                            $score = $scores[$stage] ?? null;

                            if (
                                $score !== null &&
                                is_numeric($score) &&
                                is_finite((float) $score)
                            ) {
                                $available_stages[$stage] = true;
                            }
                        }
                    }
                }

                if (
                    array_diff(
                        $required_stages,
                        array_keys($available_stages)
                    )
                ) {
                    continue;
                }

                $options[$selection_key] = [
                    'program' => $program,
                    'cohort' => $cohort,
                    'stages' => $required_stages,
                ];
            }

            foreach ($options as $key => $option) {
                if (($contexts_per_selection[$key] ?? 0) !== 1) {
                    unset($options[$key]);
                }
            }

            $options = array_values($options);
        }

        usort(
            $options,
            static function ($first, $second) {
                $first_cohort = (string) ($first['cohort'] ?? '');
                $second_cohort = (string) ($second['cohort'] ?? '');

                if (
                    is_numeric($first_cohort) &&
                    is_numeric($second_cohort) &&
                    (int) $first_cohort !== (int) $second_cohort
                ) {
                    return (int) $first_cohort
                        <=> (int) $second_cohort;
                }

                $cohort_order = strcmp(
                    $first_cohort,
                    $second_cohort
                );

                if ($cohort_order !== 0) {
                    return $cohort_order;
                }

                return strcmp(
                    (string) ($first['program'] ?? ''),
                    (string) ($second['program'] ?? '')
                );
            }
        );

        /*
         * Cache metadata only, never individual participant answers.
         * Source entry changes are picked up after cache expiry.
         */
        set_transient(
            $cache_key,
            [
                'fingerprint' => $fingerprint,
                'options' => $options,
            ],
            5 * MINUTE_IN_SECONDS
        );

        return rest_ensure_response([
            'options' => $options,
            'cached' => false,
        ]);
    }

    public static function get_evaluations($request)
    {
        $filters = [];

        foreach (
            [
                'participant_key',
                'program',
                'program_group',
                'cohort',
                'evaluation_stage',
                'comparison',
                'form_id',
                'form_key',
            ] as $key
        ) {
            $value = $request->get_param($key);

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

            $filters[$key] = sanitize_text_field((string) $value);
        }

        $ids = $request->get_param('form_ids');

        if ($ids === null) {
            $ids = [];
        }

        if (!is_array($ids)) {
            return new WP_Error(
                'tle_invalid_form_ids',
                'Pass form_ids as an array.',
                ['status' => 400]
            );
        }

        $data = TLE_Report_Service::get_report($filters, $ids);

        return is_wp_error($data)
            ? $data
            : rest_ensure_response($data);
    }
}

add_action('rest_api_init', ['TLE_REST_API', 'register_routes']);