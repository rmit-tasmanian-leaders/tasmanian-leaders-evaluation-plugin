<?php

/**
 * Front-End Evaluation Dashboard Template
 *
 * Displays the proof-of-concept Tasmanian Leaders evaluation dashboard
 * inside a normal WordPress page.
 *
 * @package TasmanianLeadersEvaluation
 */

// Prevent direct access to this template outside WordPress.
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Prototype evaluation data.
 *
 * This data is temporary and exists only to demonstrate the embedded
 * WordPress dashboard architecture.
 *
 * Future development should replace this with data supplied through
 * the evaluation data service.
 */
$dashboard_data = [
    'program' => 'I-LEAD Young Professionals',
    'cohort' => '2026',
    'evaluation_point' => 'Final Evaluation',
    'comparison' => 'Pre-program / Completion / 3-Month Delay',

    'insight' => [
        [
            'capability' => 'Self-awareness',
            'pre_program' => '4.8',
            'completion' => '5.3',
            'delay' => '5.5',
            'change' => '+0.7',
        ],
        [
            'capability' => 'Social awareness',
            'pre_program' => '5.0',
            'completion' => '5.5',
            'delay' => '5.6',
            'change' => '+0.6',
        ],
        [
            'capability' => 'Clarity of purpose',
            'pre_program' => '4.7',
            'completion' => '5.4',
            'delay' => '5.6',
            'change' => '+0.9',
        ],
    ],
];
?>

<div class="tle-dashboard">

    <header class="tle-dashboard__header">
        <p class="tle-dashboard__eyebrow">
            <?php echo esc_html('Tasmanian Leaders'); ?>
        </p>

        <h1 class="tle-dashboard__title">
            <?php echo esc_html('Evaluation Dashboard'); ?>
        </h1>

        <p class="tle-dashboard__intro">
            <?php
            echo esc_html(
                'This prototype demonstrates how the evaluation reporting interface can be embedded directly into a protected WordPress website page.'
            );
            ?>
        </p>
    </header>

    <section class="tle-dashboard__card" aria-labelledby="tle-report-details-heading">

        <h2 id="tle-report-details-heading" class="tle-dashboard__section-title">
            <?php echo esc_html('Report Configuration'); ?>
        </h2>

        <div class="tle-dashboard__details">

            <div class="tle-dashboard__detail">
                <span class="tle-dashboard__label">
                    <?php echo esc_html('Program'); ?>
                </span>

                <span class="tle-dashboard__value">
                    <?php echo esc_html($dashboard_data['program']); ?>
                </span>
            </div>

            <div class="tle-dashboard__detail">
                <span class="tle-dashboard__label">
                    <?php echo esc_html('Cohort'); ?>
                </span>

                <span class="tle-dashboard__value">
                    <?php echo esc_html($dashboard_data['cohort']); ?>
                </span>
            </div>

            <div class="tle-dashboard__detail">
                <span class="tle-dashboard__label">
                    <?php echo esc_html('Evaluation'); ?>
                </span>

                <span class="tle-dashboard__value">
                    <?php echo esc_html($dashboard_data['evaluation_point']); ?>
                </span>
            </div>

            <div class="tle-dashboard__detail">
                <span class="tle-dashboard__label">
                    <?php echo esc_html('Comparison'); ?>
                </span>

                <span class="tle-dashboard__value">
                    <?php echo esc_html($dashboard_data['comparison']); ?>
                </span>
            </div>

        </div>
    </section>

    <section class="tle-dashboard__card" aria-labelledby="tle-insight-heading">

        <div class="tle-dashboard__section-header">
            <div>
                <p class="tle-dashboard__section-kicker">
                    <?php echo esc_html('ELF Capability'); ?>
                </p>

                <h2 id="tle-insight-heading" class="tle-dashboard__section-title">
                    <?php echo esc_html('Insight'); ?>
                </h2>
            </div>

            <p class="tle-dashboard__section-description">
                <?php
                echo esc_html(
                    'Insight reflects how participants understand themselves, their environment and the factors that influence their leadership decisions.'
                );
                ?>
            </p>
        </div>

        <div class="tle-dashboard__table-wrapper">

            <table class="tle-dashboard__table">

                <thead>
                    <tr>
                        <th scope="col">
                            <?php echo esc_html('Capability'); ?>
                        </th>

                        <th scope="col">
                            <?php echo esc_html('Pre-program'); ?>
                        </th>

                        <th scope="col">
                            <?php echo esc_html('Completion'); ?>
                        </th>

                        <th scope="col">
                            <?php echo esc_html('3-Month Delay'); ?>
                        </th>

                        <th scope="col">
                            <?php echo esc_html('Change'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($dashboard_data['insight'] as $result) : ?>

                        <tr>
                            <th scope="row">
                                <?php echo esc_html($result['capability']); ?>
                            </th>

                            <td>
                                <?php echo esc_html($result['pre_program']); ?>
                            </td>

                            <td>
                                <?php echo esc_html($result['completion']); ?>
                            </td>

                            <td>
                                <?php echo esc_html($result['delay']); ?>
                            </td>

                            <td>
                                <?php echo esc_html($result['change']); ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <p class="tle-dashboard__note">
            <?php
            echo esc_html(
                'Prototype data is shown on a 1-7 scale and will be replaced by integrated evaluation data in future development.'
            );
            ?>
        </p>

    </section>

    <section class="tle-dashboard__card tle-dashboard__architecture-note">

        <h2 class="tle-dashboard__section-title">
            <?php echo esc_html('Integration Prototype'); ?>
        </h2>

        <p>
            <?php
            echo esc_html(
                'This page is rendered by the Tasmanian Leaders WordPress plugin through a shortcode rather than through the WordPress Admin interface.'
            );
            ?>
        </p>

    </section>

</div>