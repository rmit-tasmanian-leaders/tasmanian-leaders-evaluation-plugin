<?php

/**
 * Gravity Forms field mapping configuration.
 *
 * Maps Gravity Forms fields to the normalised evaluation structure.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

return [

    /*
     * ELF-I
     *
     * Form 37: ELF1 - Pre-program survey
     */
    'ELF-I' => [
        'form_id' => 37,

        'evaluation_stage' => 'pre_program',

        'fields' => [
            'participant_key' => '61',
            'program_group' => '55',
            'program' => '56',
            'cohort' => '59',
        ],

        'evaluation_fields' => [
            '12.1',
            '12.2',
            '12.3',
            '12.4',
            '12.5',
            '17.1',
            '17.2',
            '17.3',
            '17.4',
            '18.2',
            '18.3',
            '18.4',
            '18.5',
            '14',
            '13',
            '19',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '37.1',
            '37.2',
            '37.3',
            '37.4',
            '37.5',
            '37.6',
            '37.7',
            '39',
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '47',
        ],
    ],

    /*
     * ELF-E
     *
     * Form 40: ELF2 - Program completion survey
     */
    'ELF-E' => [
        'form_id' => 40,

        'evaluation_stage' => 'completion',

        'fields' => [
            'participant_key' => '',
            'program_group' => '',
            'program' => '',
            'cohort' => '',
        ],

        'evaluation_fields' => [
            '12.1',
            '12.2',
            '12.3',
            '12.4',
            '12.5',
            '17.1',
            '17.2',
            '17.3',
            '17.4',
            '18.2',
            '18.3',
            '18.4',
            '18.5',
            '14',
            '13',
            '19',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '37.1',
            '37.2',
            '37.3',
            '37.4',
            '37.5',
            '37.6',
            '37.7',
            '39',
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '47',
        ],
    ],

    /*
     * ELF-D
     *
     * Form 43: ELF3 - 3-month delayed survey
     */
    'ELF-D' => [
        'form_id' => 43,

        'evaluation_stage' => 'delay',

        'fields' => [
            'participant_key' => '',
            'program_group' => '',
            'program' => '',
            'cohort' => '',
        ],

        'evaluation_fields' => [
            '12.1',
            '12.2',
            '12.3',
            '12.4',
            '12.5',
            '17.1',
            '17.2',
            '17.3',
            '17.4',
            '18.2',
            '18.3',
            '18.4',
            '18.5',
            '14',
            '13',
            '19',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '37.1',
            '37.2',
            '37.3',
            '37.4',
            '37.5',
            '37.6',
            '37.7',
            '39',
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '47',
        ],
    ],
];