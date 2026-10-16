<?php
if (!defined('ABSPATH')) { exit; }
class TLE_REST_API
{
    public static function permissions_check() { return is_user_logged_in(); }
    public static function setup_permissions_check() { return current_user_can('manage_options'); }
    public static function register_routes() {
        register_rest_route('tle/v1', '/evaluations', ['methods' => WP_REST_Server::READABLE, 'callback' => [self::class, 'get_evaluations'], 'permission_callback' => [self::class, 'permissions_check']]);
        register_rest_route('tle/v1', '/forms', ['methods' => WP_REST_Server::READABLE, 'callback' => [self::class, 'get_forms'], 'permission_callback' => [self::class, 'setup_permissions_check']]);
        register_rest_route('tle/v1', '/forms/(?P<id>\d+)/profile', [
            ['methods' => WP_REST_Server::READABLE, 'callback' => [self::class, 'get_profile'], 'permission_callback' => [self::class, 'setup_permissions_check']],
            ['methods' => 'POST', 'callback' => [self::class, 'save_profile'], 'permission_callback' => [self::class, 'setup_permissions_check']],
        ]);
    }
    public static function get_forms($request) {
        $data = TLE_Form_Registry::discover();
        return is_wp_error($data) ? $data : rest_ensure_response($data);
    }
    public static function get_profile($request) {
        $data = TLE_Form_Registry::profile($request['id']);
        return is_wp_error($data) ? $data : rest_ensure_response($data);
    }
    public static function save_profile($request) {
        $existing = TLE_Form_Registry::profile($request['id']);
        if (is_wp_error($existing)) { return $existing; }
        $data = TLE_Form_Registry::save($request['id'], $request->get_json_params());
        return is_wp_error($data) ? $data : rest_ensure_response($data);
    }
    public static function get_evaluations($request) {
        $filters = [];
        foreach (['participant_key', 'program', 'program_group', 'cohort', 'evaluation_stage', 'comparison', 'form_id', 'form_key'] as $key) {
            $value = $request->get_param($key);
            if ($value === null || $value === '') { continue; }
            if (!is_scalar($value)) { return new WP_Error('tle_invalid_filter', 'Report filters must be text values.', ['status' => 400]); }
            $filters[$key] = sanitize_text_field((string) $value);
        }
        $ids = $request->get_param('form_ids');
        if ($ids === null) { $ids = []; }
        if (!is_array($ids)) { return new WP_Error('tle_invalid_form_ids', 'Pass form_ids as an array.', ['status' => 400]); }
        $data = TLE_Report_Service::get_report($filters, $ids);
        return is_wp_error($data) ? $data : rest_ensure_response($data);
    }
}
add_action('rest_api_init', ['TLE_REST_API', 'register_routes']);
