<?php

/**
 * Reporting Service
 *
 * Filters evaluation records and calculates reporting results.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_Reporting_Service
{
    public static function filter_records($records, $filters = [])
    {
        return array_values(
            array_filter(
                $records,
                static function ($record) use ($filters) {
                    foreach (
                        ['program', 'cohort', 'evaluation_stage']
                        as $field
                    ) {
                        if (
                            !empty($filters[$field]) &&
                            ($record[$field] ?? '') !== $filters[$field]
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            )
        );
    }

    /**
     * Average each question by evaluation stage.
     *
     * Rounding can be disabled for intermediate calculations.
     */
    public static function calculate_question_averages(
        $records,
        $round_results = true
    ) {
        $grouped = [];

        foreach ($records as $record) {
            if (
                empty($record['question']) ||
                !isset($record['score']) ||
                !is_numeric($record['score'])
            ) {
                continue;
            }

            $question = (string) $record['question'];
            $stage = $record['evaluation_stage'];

            if (!isset($grouped[$question][$stage])) {
                $grouped[$question][$stage] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }

            $grouped[$question][$stage]['total'] += (float) $record['score'];
            $grouped[$question][$stage]['count']++;
        }

        $results = [];

        foreach ($grouped as $question => $stages) {
            foreach ($stages as $stage => $values) {
                $average = $values['total'] / $values['count'];

                $results[$question][$stage] = $round_results
                    ? round($average, 2)
                    : $average;
            }
        }

        return $results;
    }

    /**
     * Provisional Form 37 mapping inferred from wording.
     *
     * This is not an official Tasmanian Leaders scoring key.
     * Q26, Q27 and Q37.1 placement remains particularly uncertain.
     */
    public static function get_capability_questions()
    {
        return [
            'insight' => [
                'Self-awareness' => ['12.1', '12.5', '18.2', '18.3'],
                'Social awareness' => ['12.2', '14'],
                'Situational awareness' => ['12.4', '17.3'],
                'Clarity of purpose' => ['12.3', '17.1'],
                'Strategic foresight' => ['17.4', '13'],
                'Balanced processing' => ['18.5'],
                'Self-compassion' => ['17.2', '18.4'],
            ],
            'influence' => [
                'Tolerance for ambiguity' => ['19', '24', '25', '37.3'],
                'Capacity for informal influence' => ['29', '30'],
                'Tolerance for complexity' => ['26', '28', '37.6'],
                'Capacity for collaboration' => ['32', '33', '37.5'],
                'Capacity to establish networks' => ['27', '31', '34'],
                'Creative decision-making' => [
                    '37.1', '37.2', '37.4', '37.7',
                ],
            ],
            'impact' => [
                'Capacity to foster belonging' => ['40', '44'],
                'Capacity to foster intrinsic motivation' => [
                    '41', '42', '45',
                ],
                'Place-attachment' => ['43', '46'],
                'Extra-role behaviours' => ['39', '47'],
            ],
        ];
    }

    /**
     * Provisional reverse-scoring choices inferred from wording.
     *
     * These require confirmation against the intended methodology.
     * Q26 and Q37.1 retain their original direction pending review.
     */
    public static function get_reverse_scored_questions()
    {
        return [
            '12.2',
            '12.3',
            '12.5',
            '17.4',
            '18.2',
            '18.3',
            '19',
            '24',
            '25',
            '27',
            '28',
            '30',
            '31',
            '33',
            '37.5',
            '37.7',
            '44',
            '45',
            '46',
            '47',
        ];
    }

    /**
     * Convert a Form 37 raw score to a provisional 1-7 score.
     *
     * Q13 uses equal intervals between its five scored categories.
     * This is a development assumption, not a percentage-based scale.
     *
     * Q14 raw score 1 means N/A and is excluded.
     * Its agreement scores 2-8 become 1-7.
     *
     * Invalid and missing scores return null.
     */
    public static function prepare_capability_score($question, $score)
    {
        if ($score === null || !is_numeric($score)) {
            return null;
        }

        $question = (string) $question;
        $score = (float) $score;

        if (!is_finite($score) || floor($score) !== $score) {
            return null;
        }

        if ($question === '13') {
            if ($score < 1 || $score > 5) {
                return null;
            }

            return 1 + (($score - 1) * 6 / 4);
        }

        if ($question === '14') {
            if ($score < 2 || $score > 8) {
                return null;
            }

            return $score - 1;
        }

        if ($score < 1 || $score > 7) {
            return null;
        }

        if (
            in_array(
                $question,
                self::get_reverse_scored_questions(),
                true
            )
        ) {
            return 8 - $score;
        }

        return $score;
    }

    /**
     * Prepare a separate copy of Form 37 records for capability scoring.
     *
     * Other evaluation stages remain available as raw records,
     * but their capability scoring is pending form-specific verification.
     */
    public static function prepare_capability_records($records)
    {
        $prepared = [];

        foreach ($records as $record) {
            if (($record['evaluation_stage'] ?? '') !== 'pre_program') {
                continue;
            }

            $record['score'] = self::prepare_capability_score(
                $record['question'],
                $record['score'] ?? null
            );

            $prepared[] = $record;
        }

        return $prepared;
    }

    /**
     * Average available question means within each capability.
     *
     * Each question receives equal weight.
     * Missing question means are skipped.
     * Only final capability averages are rounded.
     */
    public static function calculate_capability_averages($question_averages)
    {
        $results = [];

        foreach (self::get_capability_questions() as $section => $capabilities) {
            $results[$section] = [];

            foreach ($capabilities as $capability => $questions) {
                $totals = [];
                $counts = [];

                foreach ($questions as $question) {
                    foreach ($question_averages[$question] ?? [] as $stage => $average) {
                        if ($average === null || !is_numeric($average)) {
                            continue;
                        }

                        $totals[$stage] = ($totals[$stage] ?? 0)
                            + (float) $average;
                        $counts[$stage] = ($counts[$stage] ?? 0) + 1;
                    }
                }

                $results[$section][$capability] = [];

                foreach ($totals as $stage => $total) {
                    $results[$section][$capability][$stage] = round(
                        $total / $counts[$stage],
                        2
                    );
                }
            }
        }

        return $results;
    }

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

    public static function generate_report($records, $filters = [])
    {
        $filtered_records = self::filter_records($records, $filters);

        // Preserve the existing raw question averages.
        $question_averages = self::calculate_question_averages(
            $filtered_records
        );

        // Score a separate copy; preserve original records and responses.
        $prepared_records = self::prepare_capability_records(
            $filtered_records
        );

        $adjusted_question_averages = self::calculate_question_averages(
            $prepared_records,
            false
        );

        $capability_averages = self::calculate_capability_averages(
            $adjusted_question_averages
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
                        array_column($filtered_records, 'participant_key')
                    )
                )
            ),

            'record_count' => count($filtered_records),
            'question_averages' => $question_averages,
            'capability_averages' => $capability_averages,

            'capability_scoring' => [
                'status' => 'provisional_adjusted',
                'scale_min' => 1,
                'scale_max' => 7,
                'supported_stages' => ['pre_program'],
                'question_weighting' => 'equal',
                'missing_questions' => 'skip',
                'q13_method' => 'rescale scored categories from 1-5 to 1-7',
                'q14_method' => 'exclude N/A; subtract 1 from scores 2-8',
                'reverse_questions' => self::get_reverse_scored_questions(),
                'unresolved_direction_questions' => ['26', '37.1'],
            ],

            // Existing change values remain based on raw question averages.
            'change' => $changes,
            'records' => $filtered_records,
        ];
    }
}