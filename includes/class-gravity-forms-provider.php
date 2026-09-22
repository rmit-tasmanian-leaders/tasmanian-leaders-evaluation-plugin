<?php

/**
 * Gravity Forms Provider
 *
 * Provides access to Gravity Forms through GFAPI.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_Gravity_Forms_Provider
{
    public static function get_field_map()
    {
        static $field_map = null;

        if ($field_map === null) {
            $field_map = require plugin_dir_path(__FILE__) . '../config/gravity-forms-field-map.php';
        }

        return $field_map;
    }

    public static function is_available()
    {
        return class_exists('GFAPI');
    }

    public static function get_forms()
    {
        if (!self::is_available()) {
            return [];
        }

        return GFAPI::get_forms();
    }

    public static function get_form($form_id)
    {
        if (!self::is_available()) {
            return false;
        }

        return GFAPI::get_form(absint($form_id));
    }

    public static function get_entries(
        $form_id,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        if (!self::is_available()) {
            return [];
        }

        return GFAPI::get_entries(
            absint($form_id),
            $search_criteria,
            $sorting,
            $paging
        );
    }

    public static function get_entry($entry_id)
    {
        if (!self::is_available()) {
            return false;
        }

        return GFAPI::get_entry(absint($entry_id));
    }

    public static function normalise_entry($entry, $form_key)
    {
        $field_map = self::get_field_map();

        if (!isset($field_map[$form_key])) {
            return [];
        }

        $fields = $field_map[$form_key]['fields'];

        $get_value = static function ($field_key) use ($entry, $fields) {
            if (empty($fields[$field_key])) {
                return '';
            }

            $field_id = $fields[$field_key];

            return isset($entry[$field_id]) ? $entry[$field_id] : '';
        };

        return TLE_Evaluation_Data_Service::normalise_record([
            'participant_key' => $get_value('participant_key'),
            'program' => $get_value('program'),
            'cohort' => $get_value('cohort'),
            'evaluation_stage' => $get_value('evaluation_stage'),
            'submission_id' => isset($entry['id']) ? $entry['id'] : 0,
            'submission_date' => isset($entry['date_created']) ? $entry['date_created'] : '',
        ]);
    }

    public static function get_normalised_entries(
        $form_key,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        $field_map = self::get_field_map();

        if (
            !isset($field_map[$form_key]) ||
            empty($field_map[$form_key]['form_id'])
        ) {
            return [];
        }

        $entries = self::get_entries(
            $field_map[$form_key]['form_id'],
            $search_criteria,
            $sorting,
            $paging
        );

        $normalised_entries = [];

        foreach ($entries as $entry) {
            $normalised_entries[] = self::normalise_entry($entry, $form_key);
        }

        return $normalised_entries;
    }
}
