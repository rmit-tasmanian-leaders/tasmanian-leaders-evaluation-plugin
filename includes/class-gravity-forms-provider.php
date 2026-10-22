<?php

/**
 * Gravity Forms Provider
 *
 * Retrieves and normalises evaluation data through the REST API.
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
            $field_map = require plugin_dir_path(__FILE__)
                . '../config/gravity-forms-field-map.php';
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
            return new WP_Error('tle_provider_unavailable', 'Configure the remote Gravity Forms API URL and credentials.', ['status' => 503]);
        }

        $url = trailingslashit(TLE_GF_API_BASE_URL)
            . ltrim($endpoint, '/');

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(
                        TLE_GF_CONSUMER_KEY . ':'
                        . TLE_GF_CONSUMER_SECRET
                    ),
                    'Accept' => 'application/json',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return new WP_Error('tle_connection_failed', 'Unable to connect to the configured Gravity Forms API.', ['status' => 502]);
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code < 200 || $status_code >= 300) {
            return new WP_Error(in_array($status_code, [401, 403], true) ? 'tle_api_authentication_failed' : 'tle_api_http_error', 'Gravity Forms API returned HTTP ' . $status_code . '. Check the remote API URL and access permissions.', ['status' => 502, 'upstream_status' => $status_code]);
        }

        $data = json_decode(
            wp_remote_retrieve_body($response),
            true
        );

        return is_array($data) ? $data : new WP_Error('tle_api_invalid_json', 'Gravity Forms API returned invalid JSON.', ['status' => 502]);
    }

    public static function get_forms()
    {
        $forms = self::request('forms');

        return is_wp_error($forms) ? $forms : (is_array($forms) ? $forms : []);
    }

    public static function get_form($form_id)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return false;
        }

        return self::request('forms/' . $form_id);
    }

    /**
     * Retrieve one page, retaining its total count.
     *
     * @return array|WP_Error
     */
    private static function get_entries_page(
        $form_id,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        $form_id = absint($form_id);

        if (
            !$form_id ||
            !is_array($search_criteria) ||
            !is_array($sorting) ||
            !is_array($paging)
        ) {
            return new WP_Error(
                'tle_invalid_entries_arguments',
                'Invalid entry retrieval arguments.'
            );
        }

        $query_parameters = [];

        if (!empty($search_criteria)) {
            $query_parameters['search'] = wp_json_encode(
                $search_criteria
            );
        }

        if (!empty($sorting)) {
            $query_parameters['sorting'] = $sorting;
        }

        $page_size = !empty($paging['page_size'])
            ? max(1, absint($paging['page_size']))
            : 100;

        $offset = isset($paging['offset'])
            ? absint($paging['offset'])
            : 0;

        if (!isset($paging['offset']) && !empty($paging['page'])) {
            $page = max(1, absint($paging['page']));
            $offset = ($page - 1) * $page_size;
        }

        $query_parameters['paging'] = [
            'page_size' => $page_size,
            'offset' => $offset,
        ];

        $endpoint = 'forms/' . $form_id . '/entries?'
            . http_build_query(
                $query_parameters,
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        $response = self::request($endpoint);
        if (is_wp_error($response)) { return $response; }

        if (
            !is_array($response) ||
            !isset($response['entries']) ||
            !is_array($response['entries']) ||
            !isset($response['total_count']) ||
            !is_numeric($response['total_count']) ||
            $response['total_count'] < 0
        ) {
            return new WP_Error(
                'tle_entries_request_failed',
                'Could not retrieve a valid entry page for form '
                    . $form_id . ' at offset ' . $offset . '.'
            );
        }

        return [
            'entries' => $response['entries'],
            'total_count' => (int) $response['total_count'],
        ];
    }

    /**
     * Retrieve one page.
     *
     * Preserves the existing array-returning interface.
     * Use get_all_entries() for complete reporting retrieval.
     *
     * @return array
     */
    public static function get_entries(
        $form_id,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        $response = self::get_entries_page(
            $form_id,
            $search_criteria,
            $sorting,
            $paging
        );

        return is_wp_error($response)
            ? []
            : $response['entries'];
    }

    /**
     * Retrieve all matching entries.
     *
     * Uses stable entry-ID ordering and verifies the total count.
     * Returns an error instead of a partial dataset on failure.
     *
     * @return array|WP_Error
     */
    public static function get_all_entries(
        $form_id,
        $search_criteria = [],
        $page_size = 100
    ) {
        $page_size = max(1, min(100, absint($page_size)));
        $offset = 0;
        $expected_total = null;
        $entries = [];
        $seen_ids = [];

        do {
            $response = self::get_entries_page(
                $form_id,
                $search_criteria,
                ['key' => 'id', 'direction' => 'ASC'],
                [
                    'page_size' => $page_size,
                    'offset' => $offset,
                ]
            );

            if (is_wp_error($response)) {
                return $response;
            }

            if ($expected_total === null) {
                $expected_total = $response['total_count'];
            } elseif ($expected_total !== $response['total_count']) {
                return new WP_Error(
                    'tle_entries_changed',
                    'The entry count changed during retrieval. Retry.'
                );
            }

            $page_entries = $response['entries'];

            if (empty($page_entries) && $offset < $expected_total) {
                return new WP_Error(
                    'tle_entries_incomplete',
                    'An entry page was empty before retrieval completed.'
                );
            }

            foreach ($page_entries as $entry) {
                if (!is_array($entry) || empty($entry['id'])) {
                    return new WP_Error(
                        'tle_invalid_entry',
                        'An entry was missing its submission ID.'
                    );
                }

                $entry_id = (string) $entry['id'];

                if (isset($seen_ids[$entry_id])) {
                    return new WP_Error(
                        'tle_duplicate_entry_page',
                        'Entry pages overlapped. Retrieval was stopped.'
                    );
                }

                $seen_ids[$entry_id] = true;
                $entries[] = $entry;
            }

            $offset += count($page_entries);
        } while ($offset < $expected_total);

        if (count($entries) !== $expected_total) {
            return new WP_Error(
                'tle_entries_count_mismatch',
                'Retrieved entries did not match the API total count.'
            );
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
     * Read the raw score assigned to a selected survey choice.
     *
     * Does not apply psychometric reverse scoring.
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

        // Likert responses use row_value:choice_value.
        if (strpos($response_value, ':') !== false) {
            $parts = explode(':', $response_value);
            $response_value = end($parts);
        }

        foreach ($field['choices'] as $choice) {
            if (
                isset($choice['value']) &&
                (string) $choice['value'] === $response_value
            ) {
                if (
                    isset($choice['score']) &&
                    $choice['score'] !== '' &&
                    is_numeric($choice['score'])
                ) {
                    return floatval($choice['score']);
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Build and cache the field lookup for this PHP request.
     *
     * Subfields inherit their parent field's scoring choices.
     */
    private static function get_form_fields($form_id)
    {
        static $cache = [];

        $form_id = absint($form_id);

        if (isset($cache[$form_id])) {
            return $cache[$form_id];
        }

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

            $fields[(string) $field['id']] = $field;

            if (!empty($field['inputs']) && is_array($field['inputs'])) {
                foreach ($field['inputs'] as $input) {
                    if (isset($input['id'])) {
                        $fields[(string) $input['id']] = $field;
                    }
                }
            }
        }

        $cache[$form_id] = $fields;

        return $fields;
    }

    public static function normalise_entry($entry, $form_key)
    {
        $mapping = TLE_Form_Registry::profile($form_key);
        if (is_wp_error($mapping)) { return []; }
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

            $records[] = TLE_Evaluation_Data_Service::normalise_record([
                'form_id' => $form_id,
                'form_key' => $mapping['form_key'],
                'item_id' => $mapping['questions'][(string) $field_id]['id'] ?? '',
                'section' => $mapping['questions'][(string) $field_id]['section'] ?? '',
                'capability' => $mapping['questions'][(string) $field_id]['capability'] ?? '',
                'scoring_rule' => $mapping['questions'][(string) $field_id]['scoring'] ?? [],
                'scoring_confirmed' => !empty($mapping['scoring_confirmed']),
                'location' => $get_value('location'),
                'sector' => $get_value('sector'),
                'participant_key' => $get_value('participant_key'),
                'program' => $get_value('program'),
                'program_group' => $get_value('program_group'),
                'cohort' => $get_value('cohort'),
                'evaluation_stage' => $evaluation_stage,
                'submission_id' => $entry['id'] ?? 0,
                'submission_date' => $entry['date_created'] ?? '',
                'question' => (string) $field_id,
                'score' => self::get_score_from_field(
                    $field,
                    $response
                ),
                'response' => is_scalar($response)
                    ? (string) $response
                    : '',
            ]);
        }

        return $records;
    }

    /**
     * Retrieve and normalise one page.
     *
     * @return array
     */
    public static function get_normalised_entries(
        $form_key,
        $search_criteria = [],
        $sorting = [],
        $paging = []
    ) {
        $mapping = TLE_Form_Registry::profile($form_key);
        if (is_wp_error($mapping)) { return []; }
        $entries = self::get_entries(
            $mapping['form_id'],
            $search_criteria,
            $sorting,
            $paging
        );

        $records = [];

        foreach ($entries as $entry) {
            foreach (self::normalise_entry($entry, $form_key) as $record) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Retrieve and normalise all matching entries.
     *
     * Callers must check is_wp_error() before generating a report.
     *
     * @return array|WP_Error
     */
    public static function get_all_normalised_entries(
        $form_key,
        $search_criteria = [],
        $page_size = 100
    ) {
        $mapping = TLE_Form_Registry::profile($form_key);
        if (is_wp_error($mapping)) { return $mapping; }
        if (empty($mapping['enabled']) || empty($mapping['evaluation_stage'])) { return new WP_Error('tle_form_setup_required', 'Enable the form and configure its evaluation stage in Evaluation Forms.', ['status' => 422]); }
        if (!empty($mapping['issues']) || !empty($mapping['ambiguous_fields'])) { return new WP_Error('tle_form_mapping_issues', 'Resolve the form mapping issues before reporting.', ['status' => 422, 'issues' => $mapping['issues'], 'ambiguous_fields' => $mapping['ambiguous_fields']]); }
        if (empty($mapping['questions'])) { return new WP_Error('tle_no_matched_questions', 'No questions matched the capability document. Configure question overrides.', ['status' => 422]); }
        $form_id = $mapping['form_id'];
        $entries = self::get_all_entries(
            $form_id,
            $search_criteria,
            $page_size
        );

        if (is_wp_error($entries)) {
            return $entries;
        }

        $records = [];

        foreach ($entries as $entry) {
            foreach (self::normalise_entry($entry, $form_key) as $record) {
                $records[] = $record;
            }
        }

        return $records;
    }
}