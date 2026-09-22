<?php

/**
 * Gravity Forms field mapping configuration.
 *
 * Maps Gravity Forms fields to the normalised evaluation structure.
 *
 * Production field IDs should be added after the Tasmanian Leaders
 * Gravity Forms are inspected.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

return [
    /*
     * Example structure:
     *
     * 'ELF-I' => [
     *     'form_id' => 0,
     *     'fields' => [
     *         'participant_key' => '',
     *         'program' => '',
     *         'cohort' => '',
     *         'evaluation_stage' => '',
     *     ],
     * ],
     */

    'ELF-I' => [
        'form_id' => 0,
        'fields' => [
            'participant_key' => '',
            'program' => '',
            'cohort' => '',
            'evaluation_stage' => '',
        ],
    ],

    'ELF-E' => [
        'form_id' => 0,
        'fields' => [
            'participant_key' => '',
            'program' => '',
            'cohort' => '',
            'evaluation_stage' => '',
        ],
    ],

    'ELF-D' => [
        'form_id' => 0,
        'fields' => [
            'participant_key' => '',
            'program' => '',
            'cohort' => '',
            'evaluation_stage' => '',
        ],
    ],
];
