<?php

/**
 * Gravity Forms Provider
 *
 * Provides access to Gravity Forms through the Gravity Forms REST API.
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
        return defined('TLE_GF_API_BASE_URL')
            && defined('TLE_GF_CONSUMER_KEY')
            && defined('TLE_GF_CONSUMER_SECRET')
            && !empty(TLE_GF_API_BASE_URL)
            && !empty(TLE_GF_CONSUMER_KEY)
            && !empty(TLE_GF_CONSUMER_SECRET);
    }

    private static function request($endpoint)
    {
        if (!self::is_available()) {
            return false;
        }

        $url = trailingslashit(TLE_GF_API_BASE_URL) . ltrim($endpoint, '/');

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(
                        TLE_GF_CONSUMER_KEY . ':' . TLE_GF_CONSUMER_SECRET
                    ),
                    'Accept' => 'application/json',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code < 200 || $status_code >= 300) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) {
            return false;
        }

        return $data;
    }

    public static function get_forms()
    {
        $forms = self::request('forms');

        return is_array($forms) ? $forms : [];
    }

    public static function get_form($form_id)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return false;
        }

        return self::request('forms/' . $form_id);
    }

    public static function get_entries(
        $form_id,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        $form_id = absint($form_id);

        if (!$form_id) {
            return [];
        }

        $endpoint = 'forms/' . $form_id . '/entries';
        $query_parameters = [];

        if (!empty($paging['page'])) {
            $query_parameters['page'] = absint($paging['page']);
        }

        if (!empty($paging['page_size'])) {
            $query_parameters['page_size'] = absint($paging['page_size']);
        }

        if (!empty($sorting['key'])) {
            $query_parameters['sorting_key'] = sanitize_text_field($sorting['key']);
        }

        if (!empty($sorting['direction'])) {
            $query_parameters['sorting_direction'] = sanitize_text_field($sorting['direction']);
        }

        if (!empty($query_parameters)) {
            $endpoint .= '?' . http_build_query($query_parameters);
        }

        $entries = self::request($endpoint);

        if (!is_array($entries)) {
            return [];
        }

        if (isset($entries['entries']) && is_array($entries['entries'])) {
            return $entries['entries'];
        }

        return $entries;
    }

    public static function get_entry($form_id, $entry_id)
    {
        $form_id = absint($form_id);
        $entry_id = absint($entry_id);

        if (!$form_id || !$entry_id) {
            return false;
        }

        return self::request(
            'forms/' . $form_id . '/entries/' . $entry_id
        );
    }

    /**
     * Convert a Gravity Forms response into its numeric score.
     */
    private static function get_score_from_field($field, $response)
    {
        if ($response === '' || $response === null) {
            return null;
        }

        if (empty($field['choices']) || !is_array($field['choices'])) {
            return null;
        }

        $response_value = (string) $response;

        /*
         * Likert responses are stored as:
         *
         * row_value:choice_value
         *
         * Example:
         * glikertrowb86af978:glikertcol12918f30fa
         *
         * The choice value is the part after the colon.
         */
        if (strpos($response_value, ':') !== false) {
            $parts = explode(':', $response_value);
            $response_value = end($parts);
        }

        foreach ($field['choices'] as $choice) {
            if (
                isset($choice['value']) &&
                (string) $choice['value'] === $response_value
            ) {
                if (isset($choice['score']) && $choice['score'] !== '') {
                    return floatval($choice['score']);
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Build a lookup of Gravity Forms fields by field ID.
     *
     * Subfields such as 12.1 are mapped to their parent field 12
     * so that choices and scoring metadata can be retrieved.
     */
    private static function get_form_fields($form_id)
    {
        $form = self::get_form($form_id);

        if (
            !is_array($form) ||
            empty($form['fields']) ||
            !is_array($form['fields'])
        ) {
            return [];
        }

        $fields = [];

        foreach ($form['fields'] as $field) {
            if (!isset($field['id'])) {
                continue;
            }

            $field_id = (string) $field['id'];

            $fields[$field_id] = $field;

            /*
             * Survey/Likert fields have inputs such as:
             *
             * 12.1
             * 12.2
             * 12.3
             *
             * Their choices belong to the parent field 12.
             *
             * Add the parent field under each input ID so that
             * score lookup works automatically.
             */
            if (!empty($field['inputs']) && is_array($field['inputs'])) {
                foreach ($field['inputs'] as $input) {
                    if (!isset($input['id'])) {
                        continue;
                    }

                    $input_id = (string) $input['id'];

                    $fields[$input_id] = $field;
                }
            }
        }

        return $fields;
    }

    public static function normalise_entry($entry, $form_key)
    {
        $field_map = self::get_field_map();

        if (!isset($field_map[$form_key])) {
            return [];
        }

        $mapping = $field_map[$form_key];

        $fields = $mapping['fields'] ?? [];
        $evaluation_fields = $mapping['evaluation_fields'] ?? [];

        $form_id = isset($mapping['form_id'])
            ? absint($mapping['form_id'])
            : 0;

        if (!$form_id) {
            return [];
        }

        $form_fields = self::get_form_fields($form_id);

        $get_value = static function ($field_key) use ($entry, $fields) {
            if (empty($fields[$field_key])) {
                return '';
            }

            $field_id = $fields[$field_key];

            return isset($entry[$field_id])
                ? $entry[$field_id]
                : '';
        };

        $evaluation_stage = $mapping['evaluation_stage'] ?? '';

        $records = [];

        foreach ($evaluation_fields as $field_id) {
            if (!isset($entry[$field_id])) {
                continue;
            }

            $response = $entry[$field_id];

            if ($response === '' || $response === null) {
                continue;
            }

            $field = $form_fields[(string) $field_id] ?? [];

            $score = self::get_score_from_field(
                $field,
                $response
            );

            $records[] = TLE_Evaluation_Data_Service::normalise_record([
                'participant_key' => $get_value('participant_key'),
                'program' => $get_value('program'),
                'program_group' => $get_value('program_group'),
                'cohort' => $get_value('cohort'),
                'evaluation_stage' => $evaluation_stage,
                'submission_id' => isset($entry['id'])
                    ? $entry['id']
                    : 0,
                'submission_date' => isset($entry['date_created'])
                    ? $entry['date_created']
                    : '',
                'question' => (string) $field_id,
                'score' => $score,
                'response' => is_scalar($response)
                    ? (string) $response
                    : '',
            ]);
        }

        return $records;
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
            $records = self::normalise_entry(
                $entry,
                $form_key
            );

            foreach ($records as $record) {
                $normalised_entries[] = $record;
            }
        }

        return $normalised_entries;
    }
}
