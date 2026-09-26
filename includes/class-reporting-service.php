<?php

/**
 * Reporting Service
 *
 * Applies user-selected filters and reporting options
 * to normalised evaluation data.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_Reporting_Service
{
    /**
     * Filter normalised evaluation records.
     *
     * @param array $records Normalised evaluation records.
     * @param array $filters User-selected filters.
     * @return array
     */
    public static function filter_records($records, $filters = [])
    {
        if (empty($records)) {
            return [];
        }

        return array_values(
            array_filter(
                $records,
                static function ($record) use ($filters) {
                    if (
                        !empty($filters['program']) &&
                        $record['program'] !== $filters['program']
                    ) {
                        return false;
                    }

                    if (
                        !empty($filters['cohort']) &&
                        $record['cohort'] !== $filters['cohort']
                    ) {
                        return false;
                    }

                    if (
                        !empty($filters['evaluation_stage']) &&
                        $record['evaluation_stage'] !== $filters['evaluation_stage']
                    ) {
                        return false;
                    }

                    return true;
                }
            )
        );
    }

    /**
     * Calculate average score for each question and evaluation stage.
     *
     * @param array $records Normalised evaluation records.
     * @return array
     */
    public static function calculate_question_averages($records)
    {
        $grouped = [];

        foreach ($records as $record) {
            if (
                empty($record['question']) ||
                $record['score'] === null ||
                !is_numeric($record['score'])
            ) {
                continue;
            }

            $question = $record['question'];
            $stage = $record['evaluation_stage'];

            if (!isset($grouped[$question])) {
                $grouped[$question] = [];
            }

            if (!isset($grouped[$question][$stage])) {
                $grouped[$question][$stage] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }

            $grouped[$question][$stage]['total'] += floatval(
                $record['score']
            );

            $grouped[$question][$stage]['count']++;
        }

        $results = [];

        foreach ($grouped as $question => $stages) {
            $results[$question] = [];

            foreach ($stages as $stage => $values) {
                if ($values['count'] < 1) {
                    continue;
                }

                $results[$question][$stage] = round(
                    $values['total'] / $values['count'],
                    2
                );
            }
        }

        return $results;
    }

    /**
     * Calculate change between two evaluation stages.
     *
     * @param array  $question_averages Question averages.
     * @param string $from_stage Starting stage.
     * @param string $to_stage Ending stage.
     * @return array
     */
    public static function calculate_change(
        $question_averages,
        $from_stage,
        $to_stage
    ) {
        $changes = [];

        foreach ($question_averages as $question => $stages) {
            if (
                !isset($stages[$from_stage]) ||
                !isset($stages[$to_stage])
            ) {
                continue;
            }

            $changes[$question] = round(
                $stages[$to_stage] - $stages[$from_stage],
                2
            );
        }

        return $changes;
    }

    /**
     * Generate a report from normalised evaluation data.
     *
     * @param array $records Normalised evaluation records.
     * @param array $filters User-selected report filters and options.
     * @return array
     */
    public static function generate_report($records, $filters = [])
    {
        $filtered_records = self::filter_records(
            $records,
            $filters
        );

        $question_averages = self::calculate_question_averages(
            $filtered_records
        );

        $comparison = !empty($filters['comparison'])
            ? sanitize_text_field($filters['comparison'])
            : 'Pre-program / Completion / 3-Month Delay';

        $changes = [];

        if ($comparison === 'Pre-program / Completion') {
            $changes = self::calculate_change(
                $question_averages,
                'pre_program',
                'completion'
            );
        }

        if ($comparison === 'Pre-program / Completion / 3-Month Delay') {
            $changes = self::calculate_change(
                $question_averages,
                'pre_program',
                'delay'
            );
        }

        return [
            'filters' => $filters,
            'comparison' => $comparison,

            'participant_count' => count(
                array_unique(
                    array_filter(
                        array_column(
                            $filtered_records,
                            'participant_key'
                        )
                    )
                )
            ),

            'record_count' => count($filtered_records),

            'question_averages' => $question_averages,

            'change' => $changes,

            'records' => $filtered_records,
        ];
    }
}