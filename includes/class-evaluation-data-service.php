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
     * Resolve program/cohort context from entry metadata and configured
     * historical rules. Does not modify the source entry.
     *
     * @param array $entry Raw Gravity Forms entry.
     * @param array $mapping Effective form profile.
     * @return array|WP_Error
     */
    public static function resolve_entry_context($entry, $mapping)
    {
        if (!is_array($entry) || !is_array($mapping)) {
            return new WP_Error(
                'tle_invalid_context_arguments',
                'Entry and form mapping must be arrays.'
            );
        }

        $context = [];

        foreach (['program', 'cohort', 'program_group'] as $name) {
            $field_id = $mapping['fields'][$name] ?? '';
            $value = $field_id !== ''
                ? ($entry[$field_id] ?? '')
                : '';

            if (!is_scalar($value) && $value !== null) {
                return new WP_Error(
                    'tle_invalid_context',
                    'An entry contains an invalid context value.'
                );
            }

            $context[$name] = trim((string) $value);
        }

        $context['program'] = TLE_Report_Service::canonical_program(
            $context['program']
        );

        $candidates = [];
        $rules = get_option('tle_historical_context_rules_v1', []);

        if (!is_array($rules)) {
            return new WP_Error(
                'tle_invalid_context_rules',
                'Historical context rules must be an array.'
            );
        }

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                return new WP_Error(
                    'tle_invalid_context_rule',
                    'A historical context rule is invalid.'
                );
            }

            if (
                empty($rule['enabled']) ||
                (int) ($rule['form_id'] ?? 0) !==
                    (int) ($mapping['form_id'] ?? 0) ||
                ($rule['evaluation_stage'] ?? '') !==
                    ($mapping['evaluation_stage'] ?? '')
            ) {
                continue;
            }

            foreach (
                [
                    'timezone',
                    'start_date',
                    'end_date',
                    'program',
                    'cohort',
                    'program_group',
                ] as $name
            ) {
                if (
                    isset($rule[$name]) &&
                    !is_scalar($rule[$name])
                ) {
                    return new WP_Error(
                        'tle_invalid_context_rule',
                        'Historical rule values must be text or numbers.'
                    );
                }
            }

            $candidate = [
                'program' => TLE_Report_Service::canonical_program(
                    $rule['program'] ?? ''
                ),
                'cohort' => trim((string) ($rule['cohort'] ?? '')),
                'program_group' => trim(
                    (string) ($rule['program_group'] ?? '')
                ),
            ];

            if (
                $candidate['program'] === '' ||
                $candidate['cohort'] === ''
            ) {
                return new WP_Error(
                    'tle_incomplete_context_rule',
                    'A historical rule requires a program and cohort.'
                );
            }

            try {
                $timezone = new DateTimeZone(
                    (string) ($rule['timezone'] ?? 'Australia/Hobart')
                );

                $start_text = (string) ($rule['start_date'] ?? '');
                $end_text = (string) ($rule['end_date'] ?? '');

                $start = DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $start_text,
                    $timezone
                );

                $end = DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $end_text,
                    $timezone
                );

                if (
                    !$start ||
                    !$end ||
                    $start->format('Y-m-d') !== $start_text ||
                    $end->format('Y-m-d') !== $end_text ||
                    $end < $start
                ) {
                    throw new Exception('Invalid rule dates.');
                }
            } catch (Exception $exception) {
                return new WP_Error(
                    'tle_invalid_context_rule',
                    'A historical context rule has invalid dates or timezone.'
                );
            }

            $date_value = $entry['date_created'] ?? '';

            if (!is_scalar($date_value)) {
                return new WP_Error(
                    'tle_invalid_submission_date',
                    'The entry submission date is invalid.'
                );
            }

            $date_text = (string) $date_value;

            $submitted = DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                $date_text,
                new DateTimeZone('UTC')
            );

            if (
                !$submitted ||
                $submitted->format('Y-m-d H:i:s') !== $date_text
            ) {
                return new WP_Error(
                    'tle_invalid_submission_date',
                    'The entry submission date is invalid.'
                );
            }

            // End dates are inclusive in the configured local timezone.
            if (
                $submitted < $start ||
                $submitted >= $end->modify('+1 day')
            ) {
                continue;
            }

            $candidates[wp_json_encode($candidate)] = $candidate;
        }

        if (count($candidates) > 1) {
            return new WP_Error(
                'tle_ambiguous_context',
                'Multiple historical identities match this entry.'
            );
        }

        $source = 'metadata';

        if ($candidates) {
            $candidate = reset($candidates);

            // Check every populated value before filling any blanks.
            foreach ($context as $name => $value) {
                if (
                    $value !== '' &&
                    $candidate[$name] !== '' &&
                    $value !== $candidate[$name]
                ) {
                    return new WP_Error(
                        'tle_context_conflict',
                        'Entry metadata conflicts with a historical rule.'
                    );
                }
            }

            foreach ($context as $name => $value) {
                if ($value === '' && $candidate[$name] !== '') {
                    $context[$name] = $candidate[$name];
                    $source = 'historical_rule';
                }
            }
        }

        $resolved = (
            $context['program'] !== '' &&
            $context['cohort'] !== ''
        );

        return [
            'resolved' => $resolved,
            'source' => $resolved ? $source : 'unresolved',
            'context' => $context,
        ];
    }

    /**
     * Create a normalised evaluation record.
     *
     * @param array $data Raw evaluation data.
     * @return array
     */
    public static function normalise_record($data)
    {
        return [
            'form_id' => absint($data['form_id'] ?? 0),
            'form_key' => sanitize_text_field($data['form_key'] ?? ''),
            'item_id' => sanitize_text_field($data['item_id'] ?? ''),
            'section' => sanitize_text_field($data['section'] ?? ''),
            'scoring_rule' => (array) ($data['scoring_rule'] ?? []),
            'scoring_confirmed' => !empty($data['scoring_confirmed']),
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
                && is_numeric($data['score'])
                && is_finite((float) $data['score'])
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