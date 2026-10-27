<?php

if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;

function tle_add_report_page()
{
    add_menu_page(
        'Evaluation Report',
        'Evaluation Report',
        'manage_options',
        'tle-evaluation-report',
        'tle_render_report_page',
        'dashicons-chart-bar'
    );
}
add_action('admin_menu', 'tle_add_report_page');


function tle_get_test_report_data()
{
    return [
        'program' => 'I-LEAD Young Professionals',
        'cohort' => '2026',
        'report_type' => 'ELF Evaluation Report',
        'evaluation_point' => 'Final Evaluation',
        'comparison' => 'Pre-program / Completion / 3-Month Delay',
        'benchmark' => 'Tasmanian Leaders Benchmark',

        // Temporary value retained so the current PDF export keeps working
        // while the prototype interface is being expanded.
        'average_score' => '5.6 / 7',

        'social_desirability' => [
            'impression_management' => '7%',
            'self_deception' => '22%',
        ],

        'insight' => [
            [
                'measure' => 'Self-awareness',
                'pre' => 4.8,
                'completion' => 5.3,
                'delay' => 5.5,
                'benchmark' => 5.1,
            ],
            [
                'measure' => 'Social awareness',
                'pre' => 5.0,
                'completion' => 5.5,
                'delay' => 5.6,
                'benchmark' => 5.2,
            ],
            [
                'measure' => 'Situational awareness',
                'pre' => 4.9,
                'completion' => 5.3,
                'delay' => 5.6,
                'benchmark' => 5.1,
            ],
            [
                'measure' => 'Clarity of purpose',
                'pre' => 4.7,
                'completion' => 5.4,
                'delay' => 5.6,
                'benchmark' => 5.0,
            ],
            [
                'measure' => 'Strategic foresight',
                'pre' => 4.6,
                'completion' => 5.0,
                'delay' => 5.4,
                'benchmark' => 4.9,
            ],
            [
                'measure' => 'Balanced processing',
                'pre' => 5.2,
                'completion' => 5.5,
                'delay' => 5.7,
                'benchmark' => 5.2,
            ],
            [
                'measure' => 'Self-compassion',
                'pre' => 4.5,
                'completion' => 5.1,
                'delay' => 5.5,
                'benchmark' => 4.9,
            ],
        ],

        'influence' => [
            [
                'measure' => 'Tolerance for ambiguity',
                'pre' => 4.4,
                'completion' => 4.9,
                'delay' => 5.1,
                'benchmark' => 4.8,
            ],
            [
                'measure' => 'Capacity for informal influence',
                'pre' => 4.8,
                'completion' => 5.3,
                'delay' => 5.5,
                'benchmark' => 5.0,
            ],
            [
                'measure' => 'Tolerance for complexity',
                'pre' => 4.7,
                'completion' => 5.2,
                'delay' => 5.5,
                'benchmark' => 4.9,
            ],
            [
                'measure' => 'Capacity for collaboration',
                'pre' => 5.1,
                'completion' => 5.6,
                'delay' => 5.8,
                'benchmark' => 5.2,
            ],
            [
                'measure' => 'Capacity to establish networks',
                'pre' => 4.6,
                'completion' => 5.2,
                'delay' => 5.5,
                'benchmark' => 4.9,
            ],
            [
                'measure' => 'Creative decision-making',
                'pre' => 4.5,
                'completion' => 5.1,
                'delay' => 5.4,
                'benchmark' => 4.8,
            ],
        ],

        'impact' => [
            [
                'measure' => 'Capacity to foster belonging',
                'pre' => 4.7,
                'completion' => 5.5,
                'delay' => 5.6,
                'benchmark' => 5.0,
            ],
            [
                'measure' => 'Capacity to foster intrinsic motivation',
                'pre' => 4.8,
                'completion' => 5.3,
                'delay' => 5.5,
                'benchmark' => 5.0,
            ],
            [
                'measure' => 'Place-attachment',
                'pre' => 5.0,
                'completion' => 5.4,
                'delay' => 5.3,
                'benchmark' => 5.1,
            ],
            [
                'measure' => 'Extra-role behaviours',
                'pre' => 5.1,
                'completion' => 5.5,
                'delay' => 5.7,
                'benchmark' => 5.2,
            ],
        ],
    ];
}

function tle_render_report_page()
{
    $data = tle_get_test_report_data();

    $sections = [
        'Insight' => $data['insight'],
        'Influence' => $data['influence'],
        'Impact' => $data['impact'],
    ];
    ?>

    <style>
        .tle-report-builder {
            max-width: 1200px;
            margin-top: 20px;
        }

        .tle-header {
            margin-bottom: 24px;
        }

        .tle-header h1 {
            font-size: 30px;
            margin-bottom: 6px;
        }

        .tle-header p {
            color: #646970;
            font-size: 14px;
            margin: 0;
        }

        .tle-card {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .tle-card h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .tle-config-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 18px;
        }

        .tle-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .tle-field select {
            width: 100%;
            max-width: none;
        }

        .tle-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .tle-summary-item {
            background: #f6f7f7;
            border-radius: 6px;
            padding: 16px;
        }

        .tle-summary-label {
            display: block;
            color: #646970;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .tle-summary-value {
            font-size: 16px;
            font-weight: 600;
        }

        .tle-bias-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .tle-bias-card {
            background: #f6f7f7;
            border-left: 4px solid #234b3b;
            padding: 18px;
            border-radius: 4px;
        }

        .tle-bias-card strong {
            display: block;
            font-size: 28px;
            margin-bottom: 4px;
        }

        .tle-report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .tle-report-table th,
        .tle-report-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #dcdcde;
            text-align: left;
        }

        .tle-report-table th {
            background: #f6f7f7;
            font-weight: 600;
        }

        .tle-section-heading {
            color: #234b3b;
            font-size: 24px;
            margin-bottom: 4px;
        }

        .tle-section-description {
            color: #646970;
            margin-top: 0;
        }

        .tle-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        @media (max-width: 900px) {
            .tle-config-grid,
            .tle-summary-grid,
            .tle-bias-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="wrap tle-report-builder">

        <div class="tle-header">
            <h1>ELF Report Builder</h1>
            <p>
                Preview and export Evaluation and Learning Framework reports for Tasmanian Leaders programs.
            </p>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

            <input type="hidden" name="action" value="tle_export_pdf">

            <?php wp_nonce_field('tle_export_pdf_action', 'tle_export_pdf_nonce'); ?>

            <div class="tle-card">
                <h2>Report Configuration</h2>

                <div class="tle-config-grid">

                    <div class="tle-field">
                        <label for="tle-program">Program</label>
                        <select id="tle-program" name="program">
                            <option selected>I-LEAD Young Professionals</option>
                            <option>I-LEAD Women in Industry</option>
                            <option>I-LEAD Tassie Wine</option>
                        </select>
                    </div>

                    <div class="tle-field">
                        <label for="tle-cohort">Cohort</label>
                        <select id="tle-cohort" name="cohort">
                            <option selected>2026</option>
                            <option>2025</option>
                        </select>
                    </div>

                    <div class="tle-field">
                        <label for="tle-report-type">Report Type</label>
                        <select id="tle-report-type" name="report_type">
                            <option>Initial Leadership Capability Survey</option>
                            <option selected>Final ELF Evaluation Report</option>
                        </select>
                    </div>

                    <div class="tle-field">
                        <label for="tle-comparison">Comparison</label>
                        <select id="tle-comparison" name="comparison">
                            <option selected>Pre-program / Completion / 3-Month Delay</option>
                            <option>Pre-program / Completion</option>
                            <option>Pre-program / Tasmanian Benchmark</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="tle-card">
                <h2>Report Overview</h2>

                <div class="tle-summary-grid">

                    <div class="tle-summary-item">
                        <span class="tle-summary-label">Program</span>
                        <span class="tle-summary-value" id="tle-overview-program">
                            <?php echo esc_html($data['program']); ?>
                        </span>
                    </div>

                    <div class="tle-summary-item">
                        <span class="tle-summary-label">Cohort</span>
                        <span class="tle-summary-value" id="tle-overview-cohort">
                            <?php echo esc_html($data['cohort']); ?>
                        </span>
                    </div>

                    <div class="tle-summary-item">
                        <span class="tle-summary-label">Evaluation</span>
                        <span class="tle-summary-value" id="tle-overview-evaluation">
                            <?php echo esc_html($data['evaluation_point']); ?>
                        </span>
                    </div>

                    <div class="tle-summary-item">
                        <span class="tle-summary-label">Benchmark</span>
                        <span class="tle-summary-value">
                            <?php echo esc_html($data['benchmark']); ?>
                        </span>
                    </div>

                </div>
            </div>

            <div class="tle-card">
                <h2>Gaining Context</h2>

                <p>
                    Social desirability indicators provide context for interpreting the cohort's
                    self-reported leadership capability results.
                </p>

                <div class="tle-bias-grid">

                    <div class="tle-bias-card">
                        <strong>
                            <?php echo esc_html($data['social_desirability']['impression_management']); ?>
                        </strong>
                        Impression management
                    </div>

                    <div class="tle-bias-card">
                        <strong>
                            <?php echo esc_html($data['social_desirability']['self_deception']); ?>
                        </strong>
                        Self-deception enhancement
                    </div>

                </div>
            </div>

            <?php foreach ($sections as $section_name => $measures) : ?>

                <div class="tle-card">

                    <h2 class="tle-section-heading">
                        <?php echo esc_html($section_name); ?>
                    </h2>

<p class="tle-section-description tle-final-description">
    Cohort capability results across the ELF evaluation points.
</p>

<p class="tle-section-description tle-initial-description" style="display: none;">
    Cohort capability results compared with the Tasmanian Leaders benchmark.
</p>

<table class="tle-report-table tle-final-preview">

    <thead>
        <tr>
            <th>Capability</th>
            <th>Pre-program</th>
            <th>Completion</th>
            <th>3-Month Delay</th>
            <th>Total Change</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($measures as $measure) : ?>

            <?php
            $change = $measure['delay'] - $measure['pre'];
            ?>

            <tr>
                <td>
                    <strong>
                        <?php echo esc_html($measure['measure']); ?>
                    </strong>
                </td>

                <td>
                    <?php echo esc_html(number_format($measure['pre'], 1)); ?>
                </td>

                <td>
                    <?php echo esc_html(number_format($measure['completion'], 1)); ?>
                </td>

                <td>
                    <?php echo esc_html(number_format($measure['delay'], 1)); ?>
                </td>

                <td>
                    <?php echo esc_html(($change >= 0 ? '+' : '') . number_format($change, 1)); ?>
                </td>
            </tr>

        <?php endforeach; ?>

    </tbody>

</table>

<table class="tle-report-table tle-initial-preview" style="display: none;">

    <thead>
        <tr>
            <th>Capability</th>
            <th>Pre-program</th>
            <th>Benchmark</th>
            <th>Difference</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($measures as $measure) : ?>

            <?php
            $difference = $measure['pre'] - $measure['benchmark'];
            ?>

            <tr>
                <td>
                    <strong>
                        <?php echo esc_html($measure['measure']); ?>
                    </strong>
                </td>

                <td>
                    <?php echo esc_html(number_format($measure['pre'], 1)); ?>
                </td>

                <td>
                    <?php echo esc_html(number_format($measure['benchmark'], 1)); ?>
                </td>

                <td>
                    <?php echo esc_html(($difference >= 0 ? '+' : '') . number_format($difference, 1)); ?>
                </td>
            </tr>

        <?php endforeach; ?>

    </tbody>

</table>

                </div>

            <?php endforeach; ?>

            <div class="tle-actions">
                <?php submit_button('Export ELF Report PDF', 'primary', 'submit', false); ?>
            </div>

        </form>

    </div>

<script>
const program = document.getElementById('tle-program');
const cohort = document.getElementById('tle-cohort');
const reportType = document.getElementById('tle-report-type');
const comparison = document.getElementById('tle-comparison');
const finalPreviews = document.querySelectorAll('.tle-final-preview');
const initialPreviews = document.querySelectorAll('.tle-initial-preview');
const finalDescriptions = document.querySelectorAll('.tle-final-description');
const initialDescriptions = document.querySelectorAll('.tle-initial-description');

const overviewProgram = document.getElementById('tle-overview-program');
const overviewCohort = document.getElementById('tle-overview-cohort');
const overviewEvaluation = document.getElementById('tle-overview-evaluation');

function updateReportPreview() {
    overviewProgram.textContent = program.value;
    overviewCohort.textContent = cohort.value;

    if (reportType.value === 'Initial Leadership Capability Survey') {
        overviewEvaluation.textContent = 'Initial Evaluation';
        comparison.value = 'Pre-program / Tasmanian Benchmark';

        finalPreviews.forEach(function (table) {
            table.style.display = 'none';
        });

        initialPreviews.forEach(function (table) {
            table.style.display = 'table';
        });

        finalDescriptions.forEach(function (description) {
            description.style.display = 'none';
        });

        initialDescriptions.forEach(function (description) {
            description.style.display = 'block';
        });

    } else {
        overviewEvaluation.textContent = 'Final Evaluation';
        comparison.value = 'Pre-program / Completion / 3-Month Delay';

        finalPreviews.forEach(function (table) {
            table.style.display = 'table';
        });

        initialPreviews.forEach(function (table) {
            table.style.display = 'none';
        });

        finalDescriptions.forEach(function (description) {
            description.style.display = 'block';
        });

        initialDescriptions.forEach(function (description) {
            description.style.display = 'none';
        });
    }
}

program.addEventListener('change', updateReportPreview);
cohort.addEventListener('change', updateReportPreview);
reportType.addEventListener('change', updateReportPreview);

updateReportPreview();
</script>

    <?php
}

function tle_export_pdf()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to export this report.');
    }

    check_admin_referer('tle_export_pdf_action', 'tle_export_pdf_nonce');

    $autoload = dirname(__DIR__) . '/vendor/autoload.php';

    if (!file_exists($autoload)) {
        wp_die('PDF library is not installed.');
    }

    require_once $autoload;

    $data = tle_get_test_report_data();

    $evaluation_points = isset($_POST['evaluation_points'])
        ? array_map(
            'sanitize_key',
            (array) wp_unslash($_POST['evaluation_points'])
        )
        : [];

    $include_manager = in_array(
        'manager',
        $evaluation_points,
        true
    );

    $comparison_program_values =
        isset($_POST['comparison_programs'])
            ? array_map(
                'sanitize_text_field',
                (array) wp_unslash(
                    $_POST['comparison_programs']
                )
            )
            : [];

    $comparison_cohort_values =
        isset($_POST['comparison_cohorts'])
            ? array_map(
                'sanitize_text_field',
                (array) wp_unslash(
                    $_POST['comparison_cohorts']
                )
            )
            : [];

    $comparison_selections = [];

    $comparison_selection_count = max(
        count($comparison_program_values),
        count($comparison_cohort_values)
    );

    if ($comparison_selection_count > 2) {
        wp_die('Select no more than two comparison programs.');
    }

    for (
        $comparison_index = 0;
        $comparison_index < $comparison_selection_count;
        $comparison_index++
    ) {
        $comparison_program_name =
            trim(
                (string) (
                    $comparison_program_values[$comparison_index]
                    ?? ''
                )
            );

        $comparison_cohort =
            trim(
                (string) (
                    $comparison_cohort_values[$comparison_index]
                    ?? ''
                )
            );

        if (
            $comparison_program_name === '' ||
            $comparison_cohort === ''
        ) {
            continue;
        }

        $comparison_selections[] = [
            'program' => $comparison_program_name,
            'cohort'  => $comparison_cohort,
        ];
    }

    /*
     * Keep the legacy program list available to existing validation
     * while retaining Program + Cohort pairs above.
     */
    $comparison_programs =
        array_column(
            $comparison_selections,
            'program'
        );

    // Program comparisons always use relative Pre-program -> Delayed growth.
    // The evaluation-point selector has been retired from the dashboard.
    $has_comparisons = !empty($comparison_selections);
    $manager_summary_text = isset($_POST['manager_summary_text'])
        ? sanitize_textarea_field(
            wp_unslash($_POST['manager_summary_text'])
        )
        : '';
    $report_title_input = isset($_POST['report_title'])
        ? sanitize_text_field(wp_unslash($_POST['report_title']))
        : '';

    $overview_intro_text = isset($_POST['overview_intro_text'])
        ? sanitize_textarea_field(wp_unslash($_POST['overview_intro_text']))
        : '';

    if (trim($overview_intro_text) === '') {
        $overview_intro_text = 'This report presents available participant feedback ' .
            'from pre-program, post-program and delayed follow-up surveys. ' .
            'The graph shows changes in self-assessed capabilities across ' .
            'Insight, Influence and Impact over time.';
    }
    $purpose_text = isset($_POST['purpose_text'])
        ? sanitize_textarea_field(wp_unslash($_POST['purpose_text']))
        : '';

    $highlight_text_1 = isset($_POST['highlight_text_1'])
        ? sanitize_text_field(wp_unslash($_POST['highlight_text_1']))
        : '';

    $highlight_text_2 = isset($_POST['highlight_text_2'])
        ? sanitize_text_field(wp_unslash($_POST['highlight_text_2']))
        : '';

    $highlight_text_3 = isset($_POST['highlight_text_3'])
        ? sanitize_text_field(wp_unslash($_POST['highlight_text_3']))
        : '';

    $highlight_text_4 = isset($_POST['highlight_text_4'])
        ? sanitize_text_field(wp_unslash($_POST['highlight_text_4']))
        : '';

    $insight_self_awareness_text = isset($_POST['insight_self_awareness_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_self_awareness_text']))
        : '';

    $insight_social_awareness_text = isset($_POST['insight_social_awareness_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_social_awareness_text']))
        : '';

    $insight_situational_awareness_text = isset($_POST['insight_situational_awareness_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_situational_awareness_text']))
        : '';

    $insight_clarity_purpose_text = isset($_POST['insight_clarity_purpose_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_clarity_purpose_text']))
        : '';

    $insight_strategic_foresight_text = isset($_POST['insight_strategic_foresight_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_strategic_foresight_text']))
        : '';

    $insight_balanced_processing_text = isset($_POST['insight_balanced_processing_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_balanced_processing_text']))
        : '';

    $insight_self_compassion_text = isset($_POST['insight_self_compassion_text'])
        ? sanitize_text_field(wp_unslash($_POST['insight_self_compassion_text']))
        : '';

    $insight_domain_intro = isset($_POST['insight_domain_intro'])
        ? sanitize_textarea_field(wp_unslash($_POST['insight_domain_intro']))
        : '';
    $insight_quote = isset($_POST['insight_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['insight_quote']))
        : '';

    $insight_comparison_text =
        isset($_POST['insight_comparison_text'])
            ? sanitize_textarea_field(
                wp_unslash(
                    $_POST['insight_comparison_text']
                )
            )
            : '';

    $influence_tolerance_ambiguity_text = isset($_POST['influence_tolerance_ambiguity_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_tolerance_ambiguity_text']))
        : '';

    $influence_informal_influence_text = isset($_POST['influence_informal_influence_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_informal_influence_text']))
        : '';

    $influence_tolerance_complexity_text = isset($_POST['influence_tolerance_complexity_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_tolerance_complexity_text']))
        : '';

    $influence_collaboration_text = isset($_POST['influence_collaboration_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_collaboration_text']))
        : '';

    $influence_networks_text = isset($_POST['influence_networks_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_networks_text']))
        : '';

    $influence_creative_decision_text = isset($_POST['influence_creative_decision_text'])
        ? sanitize_text_field(wp_unslash($_POST['influence_creative_decision_text']))
        : '';

    $influence_domain_intro = isset($_POST['influence_domain_intro'])
        ? sanitize_textarea_field(wp_unslash($_POST['influence_domain_intro']))
        : '';
    $influence_quote = isset($_POST['influence_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['influence_quote']))
        : '';

    $influence_comparison_text =
        isset($_POST['influence_comparison_text'])
            ? sanitize_textarea_field(
                wp_unslash(
                    $_POST['influence_comparison_text']
                )
            )
            : '';

    $impact_belonging_text = isset($_POST['impact_belonging_text'])
        ? sanitize_text_field(wp_unslash($_POST['impact_belonging_text']))
        : '';

    $impact_intrinsic_motivation_text = isset($_POST['impact_intrinsic_motivation_text'])
        ? sanitize_text_field(wp_unslash($_POST['impact_intrinsic_motivation_text']))
        : '';

    $impact_place_attachment_text = isset($_POST['impact_place_attachment_text'])
        ? sanitize_text_field(wp_unslash($_POST['impact_place_attachment_text']))
        : '';

    $impact_extra_role_text = isset($_POST['impact_extra_role_text'])
        ? sanitize_text_field(wp_unslash($_POST['impact_extra_role_text']))
        : '';

    $impact_domain_intro = isset($_POST['impact_domain_intro'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_domain_intro']))
        : '';
    $impact_quote = isset($_POST['impact_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_quote']))
        : '';

    $impact_comparison_text =
        isset($_POST['impact_comparison_text'])
            ? sanitize_textarea_field(
                wp_unslash(
                    $_POST['impact_comparison_text']
                )
            )
            : '';

    $report_colour_key = isset($_POST['report_colour'])
        ? sanitize_key(wp_unslash($_POST['report_colour']))
        : 'aqua';

    $report_colour_custom = isset($_POST['report_colour_custom'])
        ? sanitize_hex_color(wp_unslash($_POST['report_colour_custom']))
        : '';

    $report_colours = [
        'aqua'   => '#a2f8ff',
        'green'  => '#5af474',
        'yellow' => '#fff25c',
        'orange' => '#ff643c',
        'pink'   => '#ffb6ff',

        // Retained for backwards compatibility with earlier dashboard exports.
        'teal'   => '#4f7f84',
        'coral'  => '#ff756b',
        'lime'   => '#b8f06a',
        'purple' => '#c249df',
    ];

    if (
        $report_colour_key === 'custom' &&
        !empty($report_colour_custom)
    ) {
        $report_colour = $report_colour_custom;
    } else {
        $report_colour = isset($report_colours[$report_colour_key])
            ? $report_colours[$report_colour_key]
            : $report_colours['aqua'];
    }

    $mix_report_colour = static function (
        $hex,
        $target = '#ffffff',
        $amount = 0.5
    ) {
        $hex = ltrim($hex, '#');
        $target = ltrim($target, '#');

        $source_red = hexdec(substr($hex, 0, 2));
        $source_green = hexdec(substr($hex, 2, 2));
        $source_blue = hexdec(substr($hex, 4, 2));

        $target_red = hexdec(substr($target, 0, 2));
        $target_green = hexdec(substr($target, 2, 2));
        $target_blue = hexdec(substr($target, 4, 2));

        $red = (int) round(
            $source_red + (($target_red - $source_red) * $amount)
        );
        $green = (int) round(
            $source_green + (($target_green - $source_green) * $amount)
        );
        $blue = (int) round(
            $source_blue + (($target_blue - $source_blue) * $amount)
        );

        return sprintf(
            '#%02x%02x%02x',
            $red,
            $green,
            $blue
        );
    };

    $report_colour_light = $mix_report_colour(
        $report_colour,
        '#ffffff',
        0.68
    );

    $before_bar_colour = $mix_report_colour(
        $report_colour,
        '#ffffff',
        0.42
    );

    $report_colour_rgb = sscanf(
        ltrim($report_colour, '#'),
        '%02x%02x%02x'
    );

    $report_colour_brightness = (
        ($report_colour_rgb[0] * 299) +
        ($report_colour_rgb[1] * 587) +
        ($report_colour_rgb[2] * 114)
    ) / 1000;

    $recommendation_text_colour =
        $report_colour_brightness >= 155
            ? '#111111'
            : '#ffffff';

    $cover_image_data = '';

    if (
        isset($_FILES['cover_image']) &&
        isset($_FILES['cover_image']['error']) &&
        $_FILES['cover_image']['error'] === UPLOAD_ERR_OK
    ) {
        $cover_tmp = $_FILES['cover_image']['tmp_name'];
        $cover_name = sanitize_file_name($_FILES['cover_image']['name']);

        $cover_check = wp_check_filetype_and_ext(
            $cover_tmp,
            $cover_name
        );

        $allowed_cover_types = [
            'image/jpeg',
            'image/png',
        ];

        if (
            !empty($cover_check['type']) &&
            in_array($cover_check['type'], $allowed_cover_types, true)
        ) {
            $cover_contents = file_get_contents($cover_tmp);

            if ($cover_contents !== false) {
                $cover_image_data =
                    'data:' .
                    $cover_check['type'] .
                    ';base64,' .
                    base64_encode($cover_contents);
            }
        }
    }

    /*
     * Program and cohort now come from the dashboard selection.
     * Available programs are determined by the reporting backend rather
     * than a hard-coded frontend allow-list.
     */
    $allowed_report_types = [
        'Initial Leadership Capability Survey',
        'Final ELF Evaluation Report',
    ];

    $program = isset($_POST['program'])
        ? sanitize_text_field(wp_unslash($_POST['program']))
        : $data['program'];

    $cohort = isset($_POST['cohort'])
        ? sanitize_text_field(wp_unslash($_POST['cohort']))
        : $data['cohort'];

    if ($program === '') {
        $program = $data['program'];
    }

    if ($cohort === '') {
        $cohort = $data['cohort'];
    }

    $report_type = isset($_POST['report_type'])
        ? sanitize_text_field(wp_unslash($_POST['report_type']))
        : $data['report_type'];

    if (!in_array($report_type, $allowed_report_types, true)) {
        $report_type = $data['report_type'];
    }

    $comparison = isset($_POST['comparison'])
        ? sanitize_text_field(wp_unslash($_POST['comparison']))
        : $data['comparison'];
$evaluation_point = (
    $report_type === 'Initial Leadership Capability Survey'
)
    ? 'Initial Evaluation'
    : 'Final Evaluation';

$report_title = (
    $report_type === 'Initial Leadership Capability Survey'
)
    ? 'Initial Leadership Capability Survey'
    : 'ELF Evaluation Report';

if ($report_title_input !== '') {
    $report_title = $report_title_input;
}

if ($purpose_text === '') {
    $purpose_text = 'A comparison between the selected evaluation points for this program.';
}

    /*
     * Load the primary program from the central reporting backend.
     *
     * Do not treat an absent stage as zero. The available stage list below
     * is derived from actual non-null capability scores returned for this
     * program and cohort.
     */
    $live_report_data = tle_get_report_data(
        $program,
        $cohort
    );

    if (is_wp_error($live_report_data)) {
        wp_die(
            'Unable to load evaluation report data: ' .
            esc_html($live_report_data->get_error_message())
        );
    }

    $data = $live_report_data;

    $primary_stage_labels = [
        'pre'        => 'Pre-program',
        'completion' => 'Post-program',
        'delay'      => 'Delayed',
    ];

    $primary_available_stages = [];

    foreach ($primary_stage_labels as $stage_key => $stage_label) {
        $stage_has_scores = false;

        foreach (['insight', 'influence', 'impact'] as $section_key) {
            foreach ($data[$section_key] ?? [] as $measure) {
                if (
                    array_key_exists($stage_key, $measure) &&
                    $measure[$stage_key] !== null &&
                    is_numeric($measure[$stage_key])
                ) {
                    $stage_has_scores = true;
                    break 2;
                }
            }
        }

        if ($stage_has_scores) {
            $primary_available_stages[$stage_key] = $stage_label;
        }
    }

    if (empty($primary_available_stages)) {
        wp_die(
            'No capability scores are available for the selected program and cohort.'
        );
    }

    /*
     * Enforce comparison requirements server-side as well as in the
     * dashboard UI. This prevents direct POST requests from generating
     * an invalid single-stage report.
     */
    if (
        count($primary_available_stages) === 1 &&
        !$has_comparisons
    ) {
        wp_die(
            'This program has one available evaluation point. Select a program with all three evaluation stages.'
        );
    }

    if ($has_comparisons && count($primary_available_stages) !== 3) {
        wp_die(
            'Comparison reports require Pre-program, Post-program and Delayed scores.'
        );
    }
    /*
     * Keep automatically generated cover wording truthful when the
     * selected program has only one real evaluation stage.
     * Preserve genuinely customised purpose text.
     */
    if (count($primary_available_stages) === 1) {
        $automatic_purpose_text =
            'An evaluation of ' .
            $program .
            '. Showcasing progress before and after the program.';

        if (
            trim($purpose_text) === '' ||
            trim($purpose_text) === $automatic_purpose_text
        ) {
            $single_stage_cover_label =
                reset($primary_available_stages);

            $purpose_text =
                'An evaluation of ' .
                $program .
                '. Showing results from the ' .
                $single_stage_cover_label .
                ' evaluation.';
        }
    }

    $is_initial_report = (
    $report_type === 'Initial Leadership Capability Survey'
);

if ($is_initial_report) {
    $metric_headers = '
        <tr>
            <th>Capability</th>
            <th>Pre-program</th>
            <th>Benchmark</th>
            <th>Difference</th>
        </tr>
    ';
} else {
    $metric_headers = '
        <tr>
            <th>Capability</th>
            <th>Pre-program</th>
            <th>Completion</th>
            <th>3-Month Delay</th>
            <th>Change</th>
        </tr>
    ';
}

$pdf_results_context = $is_initial_report
    ? 'The results below compare pre-program cohort scores with the Tasmanian Leaders benchmark.'
    : 'The results below show changes across the selected ELF evaluation points.';

    $insight_rows = '';

foreach ($data['insight'] as $measure) {

    if ($is_initial_report) {
        $difference = $measure['pre'] - $measure['benchmark'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $benchmark_width = ($measure['benchmark'] / 7) * 100;

        $insight_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['benchmark'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($benchmark_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($difference >= 0 ? '+' : '') . number_format($difference, 1)) . '
                </td>
            </tr>
        ';

    } else {
        $change = $measure['delay'] - $measure['pre'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $completion_width = ($measure['completion'] / 7) * 100;
        $delay_width = ($measure['delay'] / 7) * 100;

        $insight_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['completion'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($completion_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['delay'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($delay_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($change >= 0 ? '+' : '') . number_format($change, 1)) . '
                </td>
            </tr>
        ';
    }
}

$influence_rows = '';

foreach ($data['influence'] as $measure) {

    if ($is_initial_report) {
        $difference = $measure['pre'] - $measure['benchmark'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $benchmark_width = ($measure['benchmark'] / 7) * 100;

        $influence_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['benchmark'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($benchmark_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($difference >= 0 ? '+' : '') . number_format($difference, 1)) . '
                </td>
            </tr>
        ';

    } else {
        $change = $measure['delay'] - $measure['pre'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $completion_width = ($measure['completion'] / 7) * 100;
        $delay_width = ($measure['delay'] / 7) * 100;

        $influence_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['completion'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($completion_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['delay'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($delay_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($change >= 0 ? '+' : '') . number_format($change, 1)) . '
                </td>
            </tr>
        ';
    }
}

$impact_rows = '';

foreach ($data['impact'] as $measure) {

    if ($is_initial_report) {
        $difference = $measure['pre'] - $measure['benchmark'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $benchmark_width = ($measure['benchmark'] / 7) * 100;

        $impact_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['benchmark'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($benchmark_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($difference >= 0 ? '+' : '') . number_format($difference, 1)) . '
                </td>
            </tr>
        ';

    } else {
        $change = $measure['delay'] - $measure['pre'];

        $pre_width = ($measure['pre'] / 7) * 100;
        $completion_width = ($measure['completion'] / 7) * 100;
        $delay_width = ($measure['delay'] / 7) * 100;

        $impact_rows .= '
            <tr>
                <td class="metric-name">
                    ' . esc_html($measure['measure']) . '
                </td>

                <td>
                    ' . esc_html(number_format($measure['pre'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($pre_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['completion'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($completion_width) . '%;"></div>
                    </div>
                </td>

                <td>
                    ' . esc_html(number_format($measure['delay'], 1)) . '
                    <div class="score-track">
                        <div class="score-fill" style="width: ' . esc_attr($delay_width) . '%;"></div>
                    </div>
                </td>

                <td class="change-positive">
                    ' . esc_html(($change >= 0 ? '+' : '') . number_format($change, 1)) . '
                </td>
            </tr>
        ';
    }
}

$cover_media_html = '';

if ($cover_image_data !== '') {
    $cover_media_html = '
        <div class="cover-image-wrap">
            <img
                class="cover-image"
                src="' . esc_attr($cover_image_data) . '"
                alt=""
            >
        </div>
    ';
} else {
    $cover_media_html = '
        <div class="cover-image-placeholder">
            Cover image
        </div>
    ';
}
$overview_sections = [
    'Insight' => $data['insight'],
    'Influence' => $data['influence'],
    'Impact' => $data['impact'],
];

$overview_averages = [];

foreach ($overview_sections as $section_name => $measures) {
    $overview_averages[$section_name] = [
        'pre' => 0,
        'completion' => 0,
        'delay' => 0,
    ];

    foreach ($measures as $measure) {
        $overview_averages[$section_name]['pre'] += $measure['pre'];
        $overview_averages[$section_name]['completion'] += $measure['completion'];
        $overview_averages[$section_name]['delay'] += $measure['delay'];
    }

    $measure_count = count($measures);

    foreach (['pre', 'completion', 'delay'] as $point) {
        $overview_averages[$section_name][$point] =
            ($overview_averages[$section_name][$point] / $measure_count / 7) * 100;
    }
}

$chart_y = static function ($percentage) {
    return 220 - ($percentage * 1.7);
};

$insight_points =
    '70,' . $chart_y($overview_averages['Insight']['pre']) . ' ' .
    '300,' . $chart_y($overview_averages['Insight']['completion']) . ' ' .
    '530,' . $chart_y($overview_averages['Insight']['delay']);

$influence_points =
    '70,' . $chart_y($overview_averages['Influence']['pre']) . ' ' .
    '300,' . $chart_y($overview_averages['Influence']['completion']) . ' ' .
    '530,' . $chart_y($overview_averages['Influence']['delay']);

$impact_points =
    '70,' . $chart_y($overview_averages['Impact']['pre']) . ' ' .
    '300,' . $chart_y($overview_averages['Impact']['completion']) . ' ' .
    '530,' . $chart_y($overview_averages['Impact']['delay']);

$overview_svg = '
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="260" viewBox="0 0 600 260">
    <rect width="600" height="260" fill="#ffffff"/>

    <g stroke="#dddddd" stroke-width="1">
        <line x1="70" y1="50" x2="550" y2="50"/>
        <line x1="70" y1="84" x2="550" y2="84"/>
        <line x1="70" y1="118" x2="550" y2="118"/>
        <line x1="70" y1="152" x2="550" y2="152"/>
        <line x1="70" y1="186" x2="550" y2="186"/>
        <line x1="70" y1="220" x2="550" y2="220"/>
    </g>

    <g fill="#555555" font-family="DejaVu Sans" font-size="12">
        <text x="35" y="53">100%</text>
        <text x="42" y="87">80%</text>
        <text x="42" y="121">60%</text>
        <text x="42" y="155">40%</text>
        <text x="42" y="189">20%</text>
        <text x="49" y="223">0%</text>

        <text x="48" y="244">Pre-program</text>
        <text x="265" y="244">Post-program</text>
        <text x="510" y="244">Delayed</text>
    </g>

    <polyline
        points="' . $insight_points . '"
        fill="none"
        stroke="' . esc_attr($report_colour) . '"
        stroke-width="4"
    />

    <polyline
        points="' . $influence_points . '"
        fill="none"
        stroke="#111111"
        stroke-width="2"
        stroke-dasharray="7,5"
    />

    <polyline
        points="' . $impact_points . '"
        fill="none"
        stroke="#777777"
        stroke-width="2"
        stroke-dasharray="2,4"
    />

    <g fill="' . esc_attr($report_colour) . '">
        <circle cx="70" cy="' . $chart_y($overview_averages['Insight']['pre']) . '" r="6"/>
        <circle cx="300" cy="' . $chart_y($overview_averages['Insight']['completion']) . '" r="6"/>
        <circle cx="530" cy="' . $chart_y($overview_averages['Insight']['delay']) . '" r="6"/>
    </g>

    <g fill="#111111">
        <circle cx="70" cy="' . $chart_y($overview_averages['Influence']['pre']) . '" r="4"/>
        <circle cx="300" cy="' . $chart_y($overview_averages['Influence']['completion']) . '" r="4"/>
        <circle cx="530" cy="' . $chart_y($overview_averages['Influence']['delay']) . '" r="4"/>
    </g>

    <g fill="#777777">
        <circle cx="70" cy="' . $chart_y($overview_averages['Impact']['pre']) . '" r="4"/>
        <circle cx="300" cy="' . $chart_y($overview_averages['Impact']['completion']) . '" r="4"/>
        <circle cx="530" cy="' . $chart_y($overview_averages['Impact']['delay']) . '" r="4"/>
    </g>
</svg>
';

$overview_chart_data =
    'data:image/svg+xml;base64,' .
    base64_encode($overview_svg);

$growth_items = [];

foreach ($overview_sections as $section_name => $measures) {
    foreach ($measures as $measure) {
        $growth = (($measure['delay'] - $measure['pre']) / $measure['pre']) * 100;

        $growth_items[] = [
            'section' => $section_name,
            'measure' => $measure['measure'],
            'growth' => $growth,
        ];
    }
}

usort(
    $growth_items,
    static function ($a, $b) {
        return $b['growth'] <=> $a['growth'];
    }
);

$top_highlights = array_slice($growth_items, 0, 4);

$highlight_descriptions = [
    'Self-awareness' => $highlight_text_1,
    'Tolerance for ambiguity' => $highlight_text_2,
    'Creative decision-making' => $highlight_text_3,
    'Capacity to foster belonging' => $highlight_text_4,
];

$highlight_definitions = [
    'Self-awareness' => 'Understanding your own emotions, thoughts and behaviour.',
    'Social awareness' => 'Understanding the feelings and behaviour of people around you.',
    'Situational awareness' => 'Recognising what is happening in your surrounding environment.',
    'Clarity of purpose' => 'Connecting your leadership role with organisational goals.',
    'Strategic foresight' => 'Considering long-term strategy rather than only immediate tasks.',
    'Balanced processing' => 'Considering relevant information when making decisions that benefit others.',
    'Self-compassion' => 'Regulating yourself and navigating adversity with a sense of meaning.',
    'Tolerance for ambiguity' => 'Being comfortable when information is incomplete or uncertain.',
    'Tolerance for complexity' => 'Working effectively in situations that cannot be fully understood.',
    'Capacity for informal influence' => 'Inspiring others to pursue goals, regardless of formal authority.',
    'Capacity for collaboration' => 'Building teamwork through mutual respect and shared perspectives.',
    'Capacity to establish networks' => 'Building new relationships through social connections.',
    'Creative decision-making' => 'Using unconventional approaches to develop new ideas.',
    'Capacity to foster belonging' => 'Helping others form meaningful connections to a place.',
    'Capacity to foster intrinsic motivation' => 'Helping others find satisfaction beyond external rewards.',
    'Place-attachment' => 'Feeling connected to Tasmania and considering it in decisions.',
    'Extra-role behaviours' => 'Contributing beyond requirements to support people and communities.',
];
$highlight_cards = [];

foreach ($top_highlights as $item) {
    $description = (
        isset($highlight_descriptions[$item['measure']]) &&
        trim($highlight_descriptions[$item['measure']]) !== ''
    )
        ? $highlight_descriptions[$item['measure']]
        : ($highlight_definitions[$item['measure']] ??
            sprintf(
                '%s increased by %s%%.',
                $item['measure'],
                round($item['growth'])
            ));

    $highlight_cards[] = '
        <td class="overview-highlight-card">
            <div class="overview-highlight-percent">
                +' . esc_html(round($item['growth'])) . '%
            </div>

            <div class="overview-highlight-name">
                ' . esc_html($item['measure']) . '
            </div>

            <div class="overview-highlight-description">
                ' . esc_html($description) . '
            </div>
        </td>
    ';
}

$highlights_html = '';

if (!empty($highlight_cards)) {
    $highlights_html .= '
        <table class="overview-highlights-table">
            <tr>
                ' . ($highlight_cards[0] ?? '') . '
                ' . ($highlight_cards[1] ?? '') . '
            </tr>
            <tr>
                ' . ($highlight_cards[2] ?? '') . '
                ' . ($highlight_cards[3] ?? '') . '
            </tr>
        </table>
    ';
}
$section_chart_data = [];
$section_movers_chart_data = [];
$section_growth_html = [];


$section_chart_y = static function ($percentage) {
    return 205 - ($percentage * 1.55);
};

foreach ($overview_sections as $section_name => $measures) {

    $averages = [
        'pre' => 0,
        'completion' => 0,
        'delay' => 0,
    ];

    foreach ($measures as $measure) {
        $averages['pre'] += $measure['pre'];
        $averages['completion'] += $measure['completion'];
        $averages['delay'] += $measure['delay'];
    }

    $count = count($measures);

    foreach (['pre', 'completion', 'delay'] as $point) {
        $averages[$point] = ($averages[$point] / $count / 7) * 100;
    }

    $section_points =
        '70,' . $section_chart_y($averages['pre']) . ' ' .
        '300,' . $section_chart_y($averages['completion']) . ' ' .
        '530,' . $section_chart_y($averages['delay']);

    $section_svg = '
    <svg xmlns="http://www.w3.org/2000/svg" width="600" height="240" viewBox="0 0 600 240">
        <rect width="600" height="240" fill="#ffffff"/>

        <g stroke="#dddddd" stroke-width="1">
            <line x1="70" y1="50" x2="550" y2="50"/>
            <line x1="70" y1="81" x2="550" y2="81"/>
            <line x1="70" y1="112" x2="550" y2="112"/>
            <line x1="70" y1="143" x2="550" y2="143"/>
            <line x1="70" y1="174" x2="550" y2="174"/>
            <line x1="70" y1="205" x2="550" y2="205"/>
        </g>

        <g fill="#555555" font-family="DejaVu Sans" font-size="12">
            <text x="35" y="53">100%</text>
            <text x="42" y="84">80%</text>
            <text x="42" y="115">60%</text>
            <text x="42" y="146">40%</text>
            <text x="42" y="177">20%</text>
            <text x="49" y="208">0%</text>

            <text x="48" y="229">Pre-program</text>
            <text x="265" y="229">Post-program</text>
            <text x="510" y="229">Delayed</text>
        </g>

        <polyline
            points="' . $section_points . '"
            fill="none"
            stroke="' . esc_attr($report_colour) . '"
            stroke-width="4"
        />

        <g fill="' . esc_attr($report_colour) . '">
            <circle cx="70" cy="' . $section_chart_y($averages['pre']) . '" r="6"/>
            <circle cx="300" cy="' . $section_chart_y($averages['completion']) . '" r="6"/>
            <circle cx="530" cy="' . $section_chart_y($averages['delay']) . '" r="6"/>
        </g>
    </svg>
    ';

    $section_chart_data[$section_name] =
        'data:image/svg+xml;base64,' .
        base64_encode($section_svg);

    $movers = [];

    foreach ($measures as $measure) {
        $before = ($measure['pre'] / 7) * 100;
        $after = ($measure['delay'] / 7) * 100;

        $movers[] = [
            'measure' => $measure['measure'],
            'before' => $before,
            'after' => $after,
            'increase' => $after - $before,
        ];
    }

    usort(
        $movers,
        static function ($a, $b) {
            return $b['increase'] <=> $a['increase'];
        }
    );

    $movers = array_slice($movers, 0, 3);

    $bar_positions = [105, 275, 445];
    $bar_svg_content = '';
    $growth_lines = '';

    foreach ($movers as $index => $mover) {
        $x = $bar_positions[$index];

        $after_height = $mover['after'] * 1.45;
        $before_height = $mover['before'] * 1.45;

        $after_y = 190 - $after_height;
        $before_y = 190 - $before_height;

        $bar_svg_content .= '
            <rect
                x="' . $x . '"
                y="' . $after_y . '"
                width="42"
                height="' . $after_height . '"
                fill="' . esc_attr($report_colour) . '"
            />

            <rect
                x="' . ($x + 44) . '"
                y="' . $before_y . '"
                width="42"
                height="' . $before_height . '"
                fill="' . esc_attr($before_bar_colour) . '"
            />

            <text
                x="' . ($x + 42) . '"
                y="215"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="10.5"
                font-weight="600"
                fill="#111111"
            >' . esc_html($mover['measure']) . '</text>
        ';

        $growth_lines .= '
            <div class="growth-line">
                ' . ($index + 1) . '. ' .
                esc_html($mover['measure']) .
                ' increased by ' .
                esc_html(round($mover['increase'])) .
                ' percentage points.
            </div>
        ';
    }

    $movers_svg = '
    <svg xmlns="http://www.w3.org/2000/svg" width="600" height="230" viewBox="0 0 600 230">
        <rect width="600" height="230" fill="#ffffff"/>

        <g font-family="DejaVu Sans" font-size="10" fill="#555555">
            <rect x="430" y="12" width="12" height="12" fill="' . esc_attr($report_colour) . '"/>
            <text x="448" y="22">After</text>

            <rect x="500" y="12" width="12" height="12" fill="' . esc_attr($before_bar_colour) . '"/>
            <text x="518" y="22">Before</text>
        </g>

        <g stroke="#dddddd" stroke-width="1">
            <line x1="70" y1="45" x2="550" y2="45"/>
            <line x1="70" y1="74" x2="550" y2="74"/>
            <line x1="70" y1="103" x2="550" y2="103"/>
            <line x1="70" y1="132" x2="550" y2="132"/>
            <line x1="70" y1="161" x2="550" y2="161"/>
            <line x1="70" y1="190" x2="550" y2="190"/>
        </g>

        <g fill="#555555" font-family="DejaVu Sans" font-size="12">
            <text x="35" y="48">100%</text>
            <text x="42" y="77">80%</text>
            <text x="42" y="106">60%</text>
            <text x="42" y="135">40%</text>
            <text x="42" y="164">20%</text>
            <text x="49" y="193">0%</text>
        </g>

        ' . $bar_svg_content . '
    </svg>
    ';

    $section_movers_chart_data[$section_name] =
        'data:image/svg+xml;base64,' .
        base64_encode($movers_svg);

    $section_growth_html[$section_name] = $growth_lines;
}

/*
 * Final Sprint 2 Insight report visualisation.
 *
 * This overrides the earlier prototype Insight chart/output only.
 * Influence and Impact continue using the previous rendering until
 * their final UX pass is applied.
 */

$insight_measures = $overview_sections['Insight'];

$insight_text_map = [
    'Self-awareness' => $insight_self_awareness_text,
    'Social awareness' => $insight_social_awareness_text,
    'Situational awareness' => $insight_situational_awareness_text,
    'Clarity of purpose' => $insight_clarity_purpose_text,
    'Strategic foresight' => $insight_strategic_foresight_text,
    'Balanced processing' => $insight_balanced_processing_text,
    'Self-compassion' => $insight_self_compassion_text,
];

/*
 * Build the all-capability Insight line chart.
 * The y-axis adapts to the range of the available prototype data
 * instead of always forcing 0–100%.
 */

$insight_percentages = [];

foreach ($insight_measures as $measure) {
    foreach (['pre', 'completion', 'delay'] as $point) {
        $insight_percentages[] = ($measure[$point] / 7) * 100;
    }
}

$insight_axis_min = max(
    0,
    floor((min($insight_percentages) - 5) / 5) * 5
);

$insight_axis_max = min(
    100,
    ceil((max($insight_percentages) + 5) / 5) * 5
);

if (($insight_axis_max - $insight_axis_min) < 20) {
    $insight_axis_min = max(0, $insight_axis_min - 10);
    $insight_axis_max = min(100, $insight_axis_max + 10);
}

$insight_plot_top = 36;
$insight_plot_bottom = 205;
$insight_plot_height = $insight_plot_bottom - $insight_plot_top;
$insight_axis_range = max(
    1,
    $insight_axis_max - $insight_axis_min
);

$insight_chart_y = static function ($percentage) use (
    $insight_plot_bottom,
    $insight_plot_height,
    $insight_axis_min,
    $insight_axis_range
) {
    return $insight_plot_bottom -
        (
            (($percentage - $insight_axis_min) / $insight_axis_range) *
            $insight_plot_height
        );
};

$insight_x_positions = [
    'pre' => 100,
    'completion' => 305,
    'delay' => 510,
];

$insight_line_colours = [
    '#0072B2', // Blue
    '#C04827', // Rust orange
    '#00866B', // Teal
    '#7B43A4', // Violet
    '#9C6900', // Dark gold
    '#B02D62', // Magenta
    '#222222', // Charcoal
];
$insight_line_dashes = ['none', '8 4', '2 3', '10 3 2 3', '12 4', '5 2 1 2', '3 4'];

$insight_grid = '';

for (
    $grid_percentage = $insight_axis_min;
    $grid_percentage <= $insight_axis_max;
    $grid_percentage += 10
) {
    $grid_y = $insight_chart_y($grid_percentage);

    $insight_grid .= '
        <line
            x1="65"
            y1="' . $grid_y . '"
            x2="550"
            y2="' . $grid_y . '"
        />

        <text
            x="54"
            y="' . ($grid_y + 4) . '"
            text-anchor="end"
        >' . esc_html($grid_percentage) . '%</text>
    ';
}

$insight_lines = '';
$insight_legend = '';

foreach ($insight_measures as $index => $measure) {
    $colour = $insight_line_colours[
        $index % count($insight_line_colours)
    ];
    $dash = $insight_line_dashes[$index % count($insight_line_dashes)];

    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $points =
        $insight_x_positions['pre'] . ',' .
        $insight_chart_y($pre_percentage) . ' ' .
        $insight_x_positions['completion'] . ',' .
        $insight_chart_y($completion_percentage) . ' ' .
        $insight_x_positions['delay'] . ',' .
        $insight_chart_y($delay_percentage);

    $insight_lines .= '
        <polyline
            points="' . $points . '"
            fill="none"
            stroke="' . esc_attr($colour) . '"
            stroke-width="2.5"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <g fill="' . esc_attr($colour) . '">
            <circle
                cx="' . $insight_x_positions['pre'] . '"
                cy="' . $insight_chart_y($pre_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $insight_x_positions['completion'] . '"
                cy="' . $insight_chart_y($completion_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $insight_x_positions['delay'] . '"
                cy="' . $insight_chart_y($delay_percentage) . '"
                r="4"
            />
        </g>
    ';

    $legend_column = $index % 2;
    $legend_row = floor($index / 2);

    $legend_x = $legend_column === 0
        ? 72
        : 320;

    $legend_y = 239 + ($legend_row * 14);

    $insight_legend .= '
        <line
            x1="' . $legend_x . '"
            y1="' . ($legend_y - 3) . '"
            x2="' . ($legend_x + 18) . '"
            y2="' . ($legend_y - 3) . '"
            stroke="' . esc_attr($colour) . '"
            stroke-width="3"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <text
            x="' . ($legend_x + 24) . '"
            y="' . $legend_y . '"
        >' . esc_html($measure['measure']) . '</text>
    ';
}

$insight_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="300"
    viewBox="0 0 600 300"
>
    <rect width="600" height="300" fill="#ffffff"/>

    <g
        stroke="#dedede"
        stroke-width="1"
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        ' . $insight_grid . '
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        <text x="75" y="224">Pre-program</text>
        <text x="270" y="224">Post-program</text>
        <text x="490" y="224">Delayed</text>

        ' . $insight_legend . '
    </g>

    ' . $insight_lines . '
</svg>
';

$section_chart_data['Insight'] =
    'data:image/svg+xml;base64,' .
    base64_encode($insight_svg);

/*
 * Key Growth: rank all Insight capabilities by pre-to-delayed
 * percentage-point increase, then show the top three with
 * Pre-program, Post-program and Delayed bars.
 */

$insight_growth_items = [];

foreach ($insight_measures as $measure) {
    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $insight_growth_items[] = [
        'measure' => $measure['measure'],
        'pre' => $pre_percentage,
        'completion' => $completion_percentage,
        'delay' => $delay_percentage,
        'increase' => $delay_percentage - $pre_percentage,
    ];
}

usort(
    $insight_growth_items,
    static function ($first, $second) {
        return $second['increase'] <=> $first['increase'];
    }
);

$insight_key_growth = array_slice(
    $insight_growth_items,
    0,
    3
);

$build_key_growth_line_svg =
    static function (
        array $items,
        array $series
    ) {
        if (
            empty($items) ||
            empty($series)
        ) {
            return '';
        }

        $all_percentages = [];

        foreach ($items as $item) {
            foreach ($series as $series_item) {
                $key =
                    $series_item['key'];

                if (
                    isset($item[$key]) &&
                    is_numeric($item[$key])
                ) {
                    $all_percentages[] =
                        (float) $item[$key];
                }
            }
        }

        if (empty($all_percentages)) {
            return '';
        }

        $axis_min = max(
            0,
            floor(
                (
                    min($all_percentages) -
                    10
                ) / 10
            ) * 10
        );

        $axis_max = min(
            100,
            ceil(
                (
                    max($all_percentages) +
                    10
                ) / 10
            ) * 10
        );

        if (
            ($axis_max - $axis_min) < 20
        ) {
            $axis_min =
                max(
                    0,
                    $axis_min - 10
                );

            $axis_max =
                min(
                    100,
                    $axis_max + 10
                );
        }

        $plot_left = 75;
        $plot_right = 540;
        $plot_top = 38;
        $plot_bottom = 150;

        $plot_width =
            $plot_right - $plot_left;

        $plot_height =
            $plot_bottom - $plot_top;

        $axis_range =
            max(
                1,
                $axis_max - $axis_min
            );

        $chart_y =
            static function ($percentage) use (
                $plot_bottom,
                $plot_height,
                $axis_min,
                $axis_range
            ) {
                return
                    $plot_bottom -
                    (
                        (
                            (
                                $percentage -
                                $axis_min
                            ) /
                            $axis_range
                        ) *
                        $plot_height
                    );
            };

        $grid = '';
        $axis_labels = '';

        for (
            $grid_percentage = $axis_min;
            $grid_percentage <= $axis_max;
            $grid_percentage += 10
        ) {
            $grid_y =
                $chart_y(
                    $grid_percentage
                );

            $grid .= '
                <line
                    x1="' . $plot_left . '"
                    y1="' . $grid_y . '"
                    x2="' . $plot_right . '"
                    y2="' . $grid_y . '"
                />
            ';

            $axis_labels .= '
                <text
                    x="58"
                    y="' . ($grid_y + 3) . '"
                    text-anchor="end"
                >' .
                    esc_html(
                        $grid_percentage
                    ) .
                '%</text>
            ';
        }

        $item_count =
            count($items);

        $slot_width =
            $plot_width /
            max(
                1,
                $item_count
            );

        $item_x = [];
        $x_labels = '';

        foreach (
            $items
            as $index => $item
        ) {
            $x =
                $plot_left +
                ($slot_width * $index) +
                ($slot_width / 2);

            $item_x[$index] = $x;

            $label =
                (string) $item['measure'];

            $words =
                preg_split(
                    '/\s+/',
                    trim($label)
                ) ?: [];

            $lines = [$label];

            if (count($words) > 1) {
                $best_split = 1;
                $best_difference =
                    PHP_INT_MAX;

                for (
                    $split = 1;
                    $split < count($words);
                    $split++
                ) {
                    $first =
                        implode(
                            ' ',
                            array_slice(
                                $words,
                                0,
                                $split
                            )
                        );

                    $second =
                        implode(
                            ' ',
                            array_slice(
                                $words,
                                $split
                            )
                        );

                    $difference =
                        abs(
                            strlen($first) -
                            strlen($second)
                        );

                    if (
                        $difference <
                        $best_difference
                    ) {
                        $best_difference =
                            $difference;

                        $best_split =
                            $split;
                    }
                }

                $lines = [
                    implode(
                        ' ',
                        array_slice(
                            $words,
                            0,
                            $best_split
                        )
                    ),
                    implode(
                        ' ',
                        array_slice(
                            $words,
                            $best_split
                        )
                    ),
                ];
            }

            foreach (
                $lines
                as $line_index => $line
            ) {
                $x_labels .= '
                    <text
                        x="' . $x . '"
                        y="' .
                            (
                                172 +
                                ($line_index * 9)
                            ) .
                        '"
                        text-anchor="middle"
                        font-family="DejaVu Sans"
                        font-size="7"
                        font-weight="600"
                        fill="#111111"
                    >' .
                        esc_html(
                            $line
                        ) .
                    '</text>
                ';
            }
        }

        $series_svg = '';
        $legend_svg = '';
        $legend_x = 275;

        foreach (
            $series
            as $series_index =>
                $series_item
        ) {
            $points = [];
            $circles = '';

            foreach (
                $items
                as $index => $item
            ) {
                $key =
                    $series_item['key'];

                if (
                    !isset($item[$key]) ||
                    !is_numeric($item[$key])
                ) {
                    continue;
                }

                $x = $item_x[$index];

                $y =
                    $chart_y(
                        (float) $item[$key]
                    );

                $points[] =
                    $x . ',' . $y;

                $circles .= '
                    <circle
                        cx="' . $x . '"
                        cy="' . $y . '"
                        r="4"
                        fill="' .
                            esc_attr(
                                $series_item[
                                    'colour'
                                ]
                            ) .
                        '"
                    />
                ';
            }

            $dash =
                !empty(
                    $series_item['dash']
                )
                    ? ' stroke-dasharray="' .
                        esc_attr(
                            $series_item[
                                'dash'
                            ]
                        ) .
                        '"'
                    : '';

            if (count($points) >= 2) {
                $series_svg .= '
                    <polyline
                        points="' .
                            esc_attr(
                                implode(
                                    ' ',
                                    $points
                                )
                            ) .
                        '"
                        fill="none"
                        stroke="' .
                            esc_attr(
                                $series_item[
                                    'colour'
                                ]
                            ) .
                        '"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        ' . $dash . '
                    />

                    ' . $circles . '
                ';
            } else {
                $series_svg .=
                    $circles;
            }

            $current_legend_x =
                $legend_x +
                ($series_index * 105);

            $legend_svg .= '
                <line
                    x1="' .
                        $current_legend_x .
                    '"
                    y1="16"
                    x2="' .
                        (
                            $current_legend_x +
                            18
                        ) .
                    '"
                    y2="16"
                    stroke="' .
                        esc_attr(
                            $series_item[
                                'colour'
                            ]
                        ) .
                    '"
                    stroke-width="3"
                    ' . $dash . '
                />

                <circle
                    cx="' .
                        (
                            $current_legend_x +
                            9
                        ) .
                    '"
                    cy="16"
                    r="3"
                    fill="' .
                        esc_attr(
                            $series_item[
                                'colour'
                            ]
                        ) .
                    '"
                />

                <text
                    x="' .
                        (
                            $current_legend_x +
                            24
                        ) .
                    '"
                    y="19"
                    font-family="DejaVu Sans"
                    font-size="8"
                    fill="#555555"
                >' .
                    esc_html(
                        $series_item[
                            'label'
                        ]
                    ) .
                '</text>
            ';
        }

        return '
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="600"
            height="205"
            viewBox="0 0 600 205"
        >
            <rect
                width="600"
                height="205"
                fill="#ffffff"
            />

            ' . $legend_svg . '

            <g
                stroke="#dedede"
                stroke-width="1"
            >
                ' . $grid . '
            </g>

            <g
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="8"
            >
                ' . $axis_labels . '
            </g>

            ' . $series_svg . '
            ' . $x_labels . '
        </svg>
        ';
    };

$insight_bar_pre = $report_colour_light;
$insight_bar_post = $before_bar_colour;
$insight_bar_delayed = $report_colour;

$insight_key_growth_content = '';
$insight_group_x = [105, 275, 445];

foreach ($insight_key_growth as $index => $item) {
    $group_x = $insight_group_x[$index];

    $pre_height = $item['pre'] * 1.35;
    $post_height = $item['completion'] * 1.35;
    $delay_height = $item['delay'] * 1.35;

    $pre_y = 170 - $pre_height;
    $post_y = 170 - $post_height;
    $delay_y = 170 - $delay_height;

    $insight_key_growth_content .= '
        <rect
            x="' . $group_x . '"
            y="' . $pre_y . '"
            width="25"
            height="' . $pre_height . '"
            fill="' . esc_attr($insight_bar_pre) . '"
        />

        <rect
            x="' . ($group_x + 28) . '"
            y="' . $post_y . '"
            width="25"
            height="' . $post_height . '"
            fill="' . esc_attr($insight_bar_post) . '"
        />

        <rect
            x="' . ($group_x + 56) . '"
            y="' . $delay_y . '"
            width="25"
            height="' . $delay_height . '"
            fill="' . esc_attr($insight_bar_delayed) . '"
        />

        <text
            x="' . ($group_x + 40) . '"
            y="192"
            text-anchor="middle"
            font-family="DejaVu Sans"
            font-size="9"
            font-weight="600"
            fill="#111111"
        >' . esc_html($item['measure']) . '</text>
    ';
}

$insight_key_growth_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="205"
    viewBox="0 0 600 205"
>
    <rect width="600" height="205" fill="#ffffff"/>

    <g
        font-family="DejaVu Sans"
        font-size="9"
        fill="#555555"
    >
        <rect
            x="325"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($insight_bar_pre) . '"
        />
        <text x="341" y="17">Pre-program</text>

        <rect
            x="410"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($insight_bar_post) . '"
        />
        <text x="426" y="17">Post-program</text>

        <rect
            x="503"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($insight_bar_delayed) . '"
        />
        <text x="519" y="17">Delayed</text>
    </g>

    <g stroke="#dedede" stroke-width="1">
        <line x1="65" y1="35" x2="550" y2="35"/>
        <line x1="65" y1="62" x2="550" y2="62"/>
        <line x1="65" y1="89" x2="550" y2="89"/>
        <line x1="65" y1="116" x2="550" y2="116"/>
        <line x1="65" y1="143" x2="550" y2="143"/>
        <line x1="65" y1="170" x2="550" y2="170"/>
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        <text x="54" y="39" text-anchor="end">100%</text>
        <text x="54" y="66" text-anchor="end">80%</text>
        <text x="54" y="93" text-anchor="end">60%</text>
        <text x="54" y="120" text-anchor="end">40%</text>
        <text x="54" y="147" text-anchor="end">20%</text>
        <text x="54" y="174" text-anchor="end">0%</text>
    </g>

    ' . $insight_key_growth_content . '
</svg>
';

$section_movers_chart_data['Insight'] =
    'data:image/svg+xml;base64,' .
    base64_encode($insight_key_growth_svg);

/* Sprint 3 direction-aware growth summary */

/*
 * Generate automatic summary wording from the real score movement.
 * Manually entered dashboard text still takes precedence.
 */
$build_change_summary_text = static function (float $change) {
    $rounded_change = round($change);
    $absolute_change = abs($rounded_change);
    $point_label = ($absolute_change == 1)
        ? 'percentage point'
        : 'percentage points';

    if ($change > 0.05) {
        return sprintf(
            'Participants demonstrated positive growth, increasing by %s %s.',
            $absolute_change,
            $point_label
        );
    }

    if ($change < -0.05) {
        return sprintf(
            "Participants' average score decreased by %s %s.",
            $absolute_change,
            $point_label
        );
    }

    return "Participants' average score was unchanged between the first and final available evaluation points.";
};

/*
 * Growth Summary contains every Insight capability, ordered by
 * pre-to-delayed change, and displays its editable dashboard text.
 */

$insight_growth_summary_html = '';

foreach ($insight_growth_items as $item) {
    $custom_text = isset($insight_text_map[$item['measure']])
        ? trim($insight_text_map[$item['measure']])
        : '';

    if ($custom_text === '') {
        $custom_text = $build_change_summary_text(
            (float) $item['increase']
        );
    }

    $insight_growth_summary_html .= '
        <div class="growth-summary-line">
            <strong>
                ' . esc_html($item['measure']) . '
            </strong>

            <div>
                ' . esc_html($custom_text) . '
            </div>
        </div>
    ';
}

/*
 * Participant quote is optional and anonymous.
 * Scale the text according to its length.
 */

/*
 * Automatically fit participant quotes inside their PDF panels.
 * Use actual Dompdf font widths rather than character thresholds.
 */
$fit_participant_quote = static function ($quote, $section) {
    static $font_metrics = null;
    static $font = null;
    static $metrics_dompdf = null;

    if ($font_metrics === null) {
        $metrics_dompdf = new Dompdf();
        $font_metrics = $metrics_dompdf->getFontMetrics();
        $font = $font_metrics->getFont('DejaVu Sans', 'bold');
    }

    // Conservative space budgets for the three report panels.
    $heights = [
        'insight' => 185,
        'influence' => 120,
        'impact' => 95,
    ];

    $available_height = $heights[$section] ?? 95;
    $available_width = 208;
    $text = '“' . trim((string) $quote) . '”';
    $paragraphs = preg_split('/\R/u', $text);

    if (!$paragraphs || !$font) {
        return '13px';
    }

    for ($size = 160; $size >= 7; $size--) {
        $font_points = $size * 0.75;
        $lines = 0;
        $fits_width = true;

        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/u', trim($paragraph));

            if (!$words || $words[0] === '') {
                $lines++;
                continue;
            }

            $current_line = '';

            foreach ($words as $word) {
                // Avoid splitting words across lines.
                $word_width = $font_metrics->getTextWidth(
                    $word, $font, $font_points
                );

                if ($word_width > $available_width) {
                    $fits_width = false;
                    break;
                }

                $candidate = $current_line === ''
                    ? $word
                    : $current_line . ' ' . $word;

                $candidate_width = $font_metrics->getTextWidth(
                    $candidate, $font, $font_points
                );

                if ($candidate_width <= $available_width) {
                    $current_line = $candidate;
                } else {
                    $lines++;
                    $current_line = $word;
                }
            }

            if (!$fits_width) {
                break;
            }

            if ($current_line !== '') {
                $lines++;
            }
        }

        if (!$fits_width || $lines === 0) {
            continue;
        }

        // Allow for Dompdf font ascent and line spacing.
        $text_height = $font_points * (
            1.17 + max(0, $lines - 1) * 1.65
        );

        if ($text_height <= $available_height) {
            return $size . 'px';
        }
    }

    return '7px';
};

$insight_quote_html = '';

if (trim($insight_quote) !== '') {
    $insight_quote_size = $fit_participant_quote($insight_quote, 'insight');

    $insight_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >

            <div
                class="participant-quote-text"
                style="font-size: ' . esc_attr($insight_quote_size) . ';"
            >
                &ldquo;' .
                nl2br(esc_html($insight_quote)) .
                '&rdquo;
            </div>
        </td>
    ';

    $insight_growth_summary_class =
        'growth-summary-box growth-summary-box--with-quote';
} else {
    $insight_growth_summary_class =
        'growth-summary-box growth-summary-box--full';
}

/*
 * Final Sprint 2 Influence report visualisation.
 */

$influence_measures = $overview_sections['Influence'];

$influence_text_map = [
    'Tolerance for ambiguity' => $influence_tolerance_ambiguity_text,
    'Capacity for informal influence' => $influence_informal_influence_text,
    'Tolerance for complexity' => $influence_tolerance_complexity_text,
    'Capacity for collaboration' => $influence_collaboration_text,
    'Capacity to establish networks' => $influence_networks_text,
    'Creative decision-making' => $influence_creative_decision_text,
];

/*
 * All-capability Influence line chart with dynamic vertical scale.
 */

$influence_percentages = [];

foreach ($influence_measures as $measure) {
    foreach (['pre', 'completion', 'delay'] as $point) {
        $influence_percentages[] = ($measure[$point] / 7) * 100;
    }
}

$influence_axis_min = max(
    0,
    floor((min($influence_percentages) - 5) / 5) * 5
);

$influence_axis_max = min(
    100,
    ceil((max($influence_percentages) + 5) / 5) * 5
);

if (($influence_axis_max - $influence_axis_min) < 20) {
    $influence_axis_min = max(0, $influence_axis_min - 10);
    $influence_axis_max = min(100, $influence_axis_max + 10);
}

$influence_plot_top = 36;
$influence_plot_bottom = 205;
$influence_plot_height =
    $influence_plot_bottom - $influence_plot_top;

$influence_axis_range = max(
    1,
    $influence_axis_max - $influence_axis_min
);

$influence_chart_y = static function ($percentage) use (
    $influence_plot_bottom,
    $influence_plot_height,
    $influence_axis_min,
    $influence_axis_range
) {
    return $influence_plot_bottom -
        (
            (($percentage - $influence_axis_min) / $influence_axis_range) *
            $influence_plot_height
        );
};

$influence_x_positions = [
    'pre' => 100,
    'completion' => 305,
    'delay' => 510,
];

$influence_line_colours = [
    '#0072B2', // Blue
    '#C04827', // Rust orange
    '#00866B', // Teal
    '#7B43A4', // Violet
    '#9C6900', // Dark gold
    '#B02D62', // Magenta
];
$influence_line_dashes = ['none', '8 4', '2 3', '10 3 2 3', '12 4', '5 2 1 2'];

$influence_grid = '';

for (
    $grid_percentage = $influence_axis_min;
    $grid_percentage <= $influence_axis_max;
    $grid_percentage += 10
) {
    $grid_y = $influence_chart_y($grid_percentage);

    $influence_grid .= '
        <line
            x1="65"
            y1="' . $grid_y . '"
            x2="550"
            y2="' . $grid_y . '"
        />

        <text
            x="54"
            y="' . ($grid_y + 4) . '"
            text-anchor="end"
        >' . esc_html($grid_percentage) . '%</text>
    ';
}

$influence_lines = '';
$influence_legend = '';

foreach ($influence_measures as $index => $measure) {
    $colour = $influence_line_colours[
        $index % count($influence_line_colours)
    ];
    $dash = $influence_line_dashes[$index % count($influence_line_dashes)];

    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $points =
        $influence_x_positions['pre'] . ',' .
        $influence_chart_y($pre_percentage) . ' ' .
        $influence_x_positions['completion'] . ',' .
        $influence_chart_y($completion_percentage) . ' ' .
        $influence_x_positions['delay'] . ',' .
        $influence_chart_y($delay_percentage);

    $influence_lines .= '
        <polyline
            points="' . $points . '"
            fill="none"
            stroke="' . esc_attr($colour) . '"
            stroke-width="2.5"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <g fill="' . esc_attr($colour) . '">
            <circle
                cx="' . $influence_x_positions['pre'] . '"
                cy="' . $influence_chart_y($pre_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $influence_x_positions['completion'] . '"
                cy="' . $influence_chart_y($completion_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $influence_x_positions['delay'] . '"
                cy="' . $influence_chart_y($delay_percentage) . '"
                r="4"
            />
        </g>
    ';

    $legend_column = $index % 2;
    $legend_row = floor($index / 2);

    $legend_x = $legend_column === 0
        ? 72
        : 320;

    $legend_y = 239 + ($legend_row * 14);

    $influence_legend .= '
        <line
            x1="' . $legend_x . '"
            y1="' . ($legend_y - 3) . '"
            x2="' . ($legend_x + 18) . '"
            y2="' . ($legend_y - 3) . '"
            stroke="' . esc_attr($colour) . '"
            stroke-width="3"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <text
            x="' . ($legend_x + 24) . '"
            y="' . $legend_y . '"
        >' . esc_html($measure['measure']) . '</text>
    ';
}

$influence_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="290"
    viewBox="0 0 600 290"
>
    <rect width="600" height="290" fill="#ffffff"/>

    <g
        stroke="#dedede"
        stroke-width="1"
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        ' . $influence_grid . '
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="9"
    >
        <text x="75" y="224">Pre-program</text>
        <text x="270" y="224">Post-program</text>
        <text x="490" y="224">Delayed</text>

        ' . $influence_legend . '
    </g>

    ' . $influence_lines . '
</svg>
';

$section_chart_data['Influence'] =
    'data:image/svg+xml;base64,' .
    base64_encode($influence_svg);

/*
 * Key Growth — highest three pre-to-delayed improvements.
 */

$influence_growth_items = [];

foreach ($influence_measures as $measure) {
    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $influence_growth_items[] = [
        'measure' => $measure['measure'],
        'pre' => $pre_percentage,
        'completion' => $completion_percentage,
        'delay' => $delay_percentage,
        'increase' => $delay_percentage - $pre_percentage,
    ];
}

usort(
    $influence_growth_items,
    static function ($first, $second) {
        return $second['increase'] <=> $first['increase'];
    }
);

$influence_key_growth = array_slice(
    $influence_growth_items,
    0,
    3
);

$influence_bar_pre = $report_colour_light;
$influence_bar_post = $before_bar_colour;
$influence_bar_delayed = $report_colour;

$influence_key_growth_content = '';
$influence_group_x = [105, 275, 445];

foreach ($influence_key_growth as $index => $item) {
    $group_x = $influence_group_x[$index];

    $pre_height = $item['pre'] * 1.35;
    $post_height = $item['completion'] * 1.35;
    $delay_height = $item['delay'] * 1.35;

    $pre_y = 170 - $pre_height;
    $post_y = 170 - $post_height;
    $delay_y = 170 - $delay_height;

    $influence_key_growth_content .= '
        <rect
            x="' . $group_x . '"
            y="' . $pre_y . '"
            width="25"
            height="' . $pre_height . '"
            fill="' . esc_attr($influence_bar_pre) . '"
        />

        <rect
            x="' . ($group_x + 28) . '"
            y="' . $post_y . '"
            width="25"
            height="' . $post_height . '"
            fill="' . esc_attr($influence_bar_post) . '"
        />

        <rect
            x="' . ($group_x + 56) . '"
            y="' . $delay_y . '"
            width="25"
            height="' . $delay_height . '"
            fill="' . esc_attr($influence_bar_delayed) . '"
        />

        <text
            x="' . ($group_x + 40) . '"
            y="192"
            text-anchor="middle"
            font-family="DejaVu Sans"
            font-size="7.5"
            font-weight="600"
            fill="#111111"
        >' . esc_html($item['measure']) . '</text>
    ';
}

$influence_key_growth_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="205"
    viewBox="0 0 600 205"
>
    <rect width="600" height="205" fill="#ffffff"/>

    <g
        font-family="DejaVu Sans"
        font-size="9"
        fill="#555555"
    >
        <rect
            x="325"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($influence_bar_pre) . '"
        />
        <text x="341" y="17">Pre-program</text>

        <rect
            x="410"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($influence_bar_post) . '"
        />
        <text x="426" y="17">Post-program</text>

        <rect
            x="503"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($influence_bar_delayed) . '"
        />
        <text x="519" y="17">Delayed</text>
    </g>

    <g stroke="#dedede" stroke-width="1">
        <line x1="65" y1="35" x2="550" y2="35"/>
        <line x1="65" y1="62" x2="550" y2="62"/>
        <line x1="65" y1="89" x2="550" y2="89"/>
        <line x1="65" y1="116" x2="550" y2="116"/>
        <line x1="65" y1="143" x2="550" y2="143"/>
        <line x1="65" y1="170" x2="550" y2="170"/>
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        <text x="54" y="39" text-anchor="end">100%</text>
        <text x="54" y="66" text-anchor="end">80%</text>
        <text x="54" y="93" text-anchor="end">60%</text>
        <text x="54" y="120" text-anchor="end">40%</text>
        <text x="54" y="147" text-anchor="end">20%</text>
        <text x="54" y="174" text-anchor="end">0%</text>
    </g>

    ' . $influence_key_growth_content . '
</svg>
';

$section_movers_chart_data['Influence'] =
    'data:image/svg+xml;base64,' .
    base64_encode($influence_key_growth_svg);

/*
 * Growth Summary ÔÇö all six capabilities, ordered by growth.
 */

$influence_growth_summary_html = '';

foreach ($influence_growth_items as $item) {
    $custom_text = isset($influence_text_map[$item['measure']])
        ? trim($influence_text_map[$item['measure']])
        : '';

    if ($custom_text === '') {
        $custom_text = $build_change_summary_text(
            (float) $item['increase']
        );
    }

    $influence_growth_summary_html .= '
        <div class="growth-summary-line">
            <strong>
                ' . esc_html($item['measure']) . '
            </strong>

            <div>
                ' . esc_html($custom_text) . '
            </div>
        </div>
    ';
}

/*
 * Optional anonymous participant quote.
 */

$influence_quote_html = '';

if (trim($influence_quote) !== '') {
    $influence_quote_size = $fit_participant_quote($influence_quote, 'influence');

    $influence_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >

            <div
                class="participant-quote-text"
                style="font-size: ' . esc_attr($influence_quote_size) . ';"
            >
                &ldquo;' .
                nl2br(esc_html($influence_quote)) .
                '&rdquo;
            </div>
        </td>
    ';

    $influence_growth_summary_class =
        'growth-summary-box growth-summary-box--with-quote';
} else {
    $influence_growth_summary_class =
        'growth-summary-box growth-summary-box--full';
}

/*
 * Final Sprint 2 Impact report visualisation.
 */

$impact_measures = $overview_sections['Impact'];

$impact_text_map = [
    'Capacity to foster belonging' => $impact_belonging_text,
    'Capacity to foster intrinsic motivation' => $impact_intrinsic_motivation_text,
    'Place-attachment' => $impact_place_attachment_text,
    'Extra-role behaviours' => $impact_extra_role_text,
];

/*
 * All-capability Impact line chart with dynamic vertical scale.
 */

$impact_percentages = [];

foreach ($impact_measures as $measure) {
    foreach (['pre', 'completion', 'delay'] as $point) {
        $impact_percentages[] = ($measure[$point] / 7) * 100;
    }
}

$impact_axis_min = max(
    0,
    floor((min($impact_percentages) - 5) / 5) * 5
);

$impact_axis_max = min(
    100,
    ceil((max($impact_percentages) + 5) / 5) * 5
);

if (($impact_axis_max - $impact_axis_min) < 20) {
    $impact_axis_min = max(0, $impact_axis_min - 10);
    $impact_axis_max = min(100, $impact_axis_max + 10);
}

$impact_plot_top = 36;
$impact_plot_bottom = 205;
$impact_plot_height =
    $impact_plot_bottom - $impact_plot_top;

$impact_axis_range = max(
    1,
    $impact_axis_max - $impact_axis_min
);

$impact_chart_y = static function ($percentage) use (
    $impact_plot_bottom,
    $impact_plot_height,
    $impact_axis_min,
    $impact_axis_range
) {
    return $impact_plot_bottom -
        (
            (($percentage - $impact_axis_min) / $impact_axis_range) *
            $impact_plot_height
        );
};

$impact_x_positions = [
    'pre' => 100,
    'completion' => 305,
    'delay' => 510,
];

$impact_line_colours = [
    '#0072B2', // Blue
    '#C04827', // Rust orange
    '#00866B', // Teal
    '#7B43A4', // Violet
];
$impact_line_dashes = ['none', '8 4', '2 3', '10 3 2 3'];

$impact_grid = '';

for (
    $grid_percentage = $impact_axis_min;
    $grid_percentage <= $impact_axis_max;
    $grid_percentage += 10
) {
    $grid_y = $impact_chart_y($grid_percentage);

    $impact_grid .= '
        <line
            x1="65"
            y1="' . $grid_y . '"
            x2="550"
            y2="' . $grid_y . '"
        />

        <text
            x="54"
            y="' . ($grid_y + 4) . '"
            text-anchor="end"
        >' . esc_html($grid_percentage) . '%</text>
    ';
}

$impact_lines = '';
$impact_legend = '';

foreach ($impact_measures as $index => $measure) {
    $colour = $impact_line_colours[
        $index % count($impact_line_colours)
    ];
    $dash = $impact_line_dashes[$index % count($impact_line_dashes)];

    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $points =
        $impact_x_positions['pre'] . ',' .
        $impact_chart_y($pre_percentage) . ' ' .
        $impact_x_positions['completion'] . ',' .
        $impact_chart_y($completion_percentage) . ' ' .
        $impact_x_positions['delay'] . ',' .
        $impact_chart_y($delay_percentage);

    $impact_lines .= '
        <polyline
            points="' . $points . '"
            fill="none"
            stroke="' . esc_attr($colour) . '"
            stroke-width="2.5"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <g fill="' . esc_attr($colour) . '">
            <circle
                cx="' . $impact_x_positions['pre'] . '"
                cy="' . $impact_chart_y($pre_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $impact_x_positions['completion'] . '"
                cy="' . $impact_chart_y($completion_percentage) . '"
                r="4"
            />
            <circle
                cx="' . $impact_x_positions['delay'] . '"
                cy="' . $impact_chart_y($delay_percentage) . '"
                r="4"
            />
        </g>
    ';

    $legend_column = $index % 2;
    $legend_row = floor($index / 2);

    $legend_x = $legend_column === 0
        ? 72
        : 320;

    $legend_y = 239 + ($legend_row * 14);

    $impact_legend .= '
        <line
            x1="' . $legend_x . '"
            y1="' . ($legend_y - 3) . '"
            x2="' . ($legend_x + 18) . '"
            y2="' . ($legend_y - 3) . '"
            stroke="' . esc_attr($colour) . '"
            stroke-width="3"
            stroke-dasharray="' . esc_attr($dash) . '"
        />

        <text
            x="' . ($legend_x + 24) . '"
            y="' . $legend_y . '"
        >' . esc_html($measure['measure']) . '</text>
    ';
}

$impact_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="275"
    viewBox="0 0 600 275"
>
    <rect width="600" height="275" fill="#ffffff"/>

    <g
        stroke="#dedede"
        stroke-width="1"
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        ' . $impact_grid . '
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="9"
    >
        <text x="75" y="224">Pre-program</text>
        <text x="270" y="224">Post-program</text>
        <text x="490" y="224">Delayed</text>

        ' . $impact_legend . '
    </g>

    ' . $impact_lines . '
</svg>
';

$section_chart_data['Impact'] =
    'data:image/svg+xml;base64,' .
    base64_encode($impact_svg);

/*
 * Key Growth — top three Impact capabilities.
 */

$impact_growth_items = [];

foreach ($impact_measures as $measure) {
    $pre_percentage = ($measure['pre'] / 7) * 100;
    $completion_percentage = ($measure['completion'] / 7) * 100;
    $delay_percentage = ($measure['delay'] / 7) * 100;

    $impact_growth_items[] = [
        'measure' => $measure['measure'],
        'pre' => $pre_percentage,
        'completion' => $completion_percentage,
        'delay' => $delay_percentage,
        'increase' => $delay_percentage - $pre_percentage,
    ];
}

usort(
    $impact_growth_items,
    static function ($first, $second) {
        return $second['increase'] <=> $first['increase'];
    }
);

$impact_key_growth = array_slice(
    $impact_growth_items,
    0,
    3
);

$impact_bar_pre = $report_colour_light;
$impact_bar_post = $before_bar_colour;
$impact_bar_delayed = $report_colour;

$impact_key_growth_content = '';
$impact_group_x = [105, 275, 445];

foreach ($impact_key_growth as $index => $item) {
    $group_x = $impact_group_x[$index];

    $pre_height = $item['pre'] * 1.35;
    $post_height = $item['completion'] * 1.35;
    $delay_height = $item['delay'] * 1.35;

    $pre_y = 170 - $pre_height;
    $post_y = 170 - $post_height;
    $delay_y = 170 - $delay_height;

    $impact_key_growth_content .= '
        <rect
            x="' . $group_x . '"
            y="' . $pre_y . '"
            width="25"
            height="' . $pre_height . '"
            fill="' . esc_attr($impact_bar_pre) . '"
        />

        <rect
            x="' . ($group_x + 28) . '"
            y="' . $post_y . '"
            width="25"
            height="' . $post_height . '"
            fill="' . esc_attr($impact_bar_post) . '"
        />

        <rect
            x="' . ($group_x + 56) . '"
            y="' . $delay_y . '"
            width="25"
            height="' . $delay_height . '"
            fill="' . esc_attr($impact_bar_delayed) . '"
        />

        <text
            x="' . ($group_x + 40) . '"
            y="192"
            text-anchor="middle"
            font-family="DejaVu Sans"
            font-size="7.5"
            font-weight="600"
            fill="#111111"
        >' . esc_html($item['measure']) . '</text>
    ';
}

$impact_key_growth_svg = '
<svg
    xmlns="http://www.w3.org/2000/svg"
    width="600"
    height="205"
    viewBox="0 0 600 205"
>
    <rect width="600" height="205" fill="#ffffff"/>

    <g
        font-family="DejaVu Sans"
        font-size="9"
        fill="#555555"
    >
        <rect
            x="325"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($impact_bar_pre) . '"
        />
        <text x="341" y="17">Pre-program</text>

        <rect
            x="410"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($impact_bar_post) . '"
        />
        <text x="426" y="17">Post-program</text>

        <rect
            x="503"
            y="8"
            width="11"
            height="11"
            fill="' . esc_attr($impact_bar_delayed) . '"
        />
        <text x="519" y="17">Delayed</text>
    </g>

    <g stroke="#dedede" stroke-width="1">
        <line x1="65" y1="35" x2="550" y2="35"/>
        <line x1="65" y1="62" x2="550" y2="62"/>
        <line x1="65" y1="89" x2="550" y2="89"/>
        <line x1="65" y1="116" x2="550" y2="116"/>
        <line x1="65" y1="143" x2="550" y2="143"/>
        <line x1="65" y1="170" x2="550" y2="170"/>
    </g>

    <g
        fill="#555555"
        font-family="DejaVu Sans"
        font-size="10"
    >
        <text x="54" y="39" text-anchor="end">100%</text>
        <text x="54" y="66" text-anchor="end">80%</text>
        <text x="54" y="93" text-anchor="end">60%</text>
        <text x="54" y="120" text-anchor="end">40%</text>
        <text x="54" y="147" text-anchor="end">20%</text>
        <text x="54" y="174" text-anchor="end">0%</text>
    </g>

    ' . $impact_key_growth_content . '
</svg>
';

$section_movers_chart_data['Impact'] =
    'data:image/svg+xml;base64,' .
    base64_encode($impact_key_growth_svg);

/*
 * Growth Summary ÔÇö every Impact capability.
 */

$impact_growth_summary_html = '';

foreach ($impact_growth_items as $item) {
    $custom_text = isset($impact_text_map[$item['measure']])
        ? trim($impact_text_map[$item['measure']])
        : '';

    if ($custom_text === '') {
        $custom_text = $build_change_summary_text(
            (float) $item['increase']
        );
    }

    $impact_growth_summary_html .= '
        <div class="growth-summary-line">
            <strong>
                ' . esc_html($item['measure']) . '
            </strong>

            <div>
                ' . esc_html($custom_text) . '
            </div>
        </div>
    ';
}

/*
 * Optional anonymous participant quote.
 */

$impact_quote_html = '';

if (trim($impact_quote) !== '') {
    $impact_quote_size = $fit_participant_quote($impact_quote, 'impact');

    $impact_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >

            <div
                class="participant-quote-text"
                style="font-size: ' . esc_attr($impact_quote_size) . ';"
            >
                &ldquo;' .
                nl2br(esc_html($impact_quote)) .
                '&rdquo;
            </div>
        </td>
    ';

    $impact_growth_summary_class =
        'growth-summary-box growth-summary-box--with-quote';
} else {
    $impact_growth_summary_class =
        'growth-summary-box growth-summary-box--full';
}
/*
 * Sprint 3: stage-aware final rendering.
 *
 * Missing stages are unavailable, not zero. When only one real
 * evaluation stage exists, render bar charts and remove growth-only
 * content.
 */

$is_single_stage_report = (
    count($primary_available_stages) === 1
);

$section_summary_heading = 'Growth Summary';

$overview_highlights_section_html = '
    <h3 class="overview-subheading">
        Highlights
    </h3>

    ' . $highlights_html . '
';

$section_secondary_html = [
    'Insight' => '
        <h3 class="section-movers-title">
            Key Growth
        </h3>

        <div class="movers-chart">
            <img
                src="' . esc_attr($section_movers_chart_data['Insight']) . '"
                alt=""
            >
        </div>
    ',
    'Influence' => '
        <h3 class="section-movers-title">
            Key Growth
        </h3>

        <div class="movers-chart">
            <img
                src="' . esc_attr($section_movers_chart_data['Influence']) . '"
                alt=""
            >
        </div>
    ',
    'Impact' => '
        <h3 class="section-movers-title">
            Key Growth
        </h3>

        <div class="movers-chart">
            <img
                src="' . esc_attr($section_movers_chart_data['Impact']) . '"
                alt=""
            >
        </div>
    ',
];

if ($is_single_stage_report) {
    $single_stage_key = array_key_first(
        $primary_available_stages
    );

    $single_stage_label =
        $primary_available_stages[$single_stage_key];

    $section_summary_heading = 'Summary';
    $overview_highlights_section_html = '';

    foreach (array_keys($section_secondary_html) as $section_name) {
        $section_secondary_html[$section_name] = '';
    }

    $single_stage_average = static function (
        array $measures,
        string $stage_key
    ) {
        $scores = [];

        foreach ($measures as $measure) {
            if (
                array_key_exists($stage_key, $measure) &&
                $measure[$stage_key] !== null &&
                is_numeric($measure[$stage_key])
            ) {
                $scores[] = (float) $measure[$stage_key];
            }
        }

        return $scores
            ? array_sum($scores) / count($scores)
            : null;
    };

    /*
     * Single-stage Overview line chart.
     *
     * The three report sections remain the x-axis categories,
     * while the available evaluation stage is shown as one
     * continuous series.
     */
    $overview_values = [];

    foreach ($overview_sections as $section_name => $measures) {
        $average = $single_stage_average(
            $measures,
            $single_stage_key
        );

        if ($average !== null) {
            $overview_values[$section_name] =
                ($average / 7) * 100;
        }
    }

    $overview_x = [
        'Insight' => 125,
        'Influence' => 300,
        'Impact' => 475,
    ];

    $overview_percentages =
        !empty($overview_values)
            ? array_values($overview_values)
            : [0];

    $overview_axis_min = max(
        0,
        floor(
            (
                min($overview_percentages) - 5
            ) / 5
        ) * 5
    );

    $overview_axis_max = min(
        100,
        ceil(
            (
                max($overview_percentages) + 5
            ) / 5
        ) * 5
    );

    if (
        ($overview_axis_max - $overview_axis_min)
        < 20
    ) {
        $overview_axis_min =
            max(
                0,
                $overview_axis_min - 10
            );

        $overview_axis_max =
            min(
                100,
                $overview_axis_max + 10
            );
    }

    $overview_plot_top = 45;
    $overview_plot_bottom = 190;

    $overview_plot_height =
        $overview_plot_bottom -
        $overview_plot_top;

    $overview_axis_range =
        max(
            1,
            $overview_axis_max -
            $overview_axis_min
        );

    $overview_y =
        static function ($percentage) use (
            $overview_plot_bottom,
            $overview_plot_height,
            $overview_axis_min,
            $overview_axis_range
        ) {
            return
                $overview_plot_bottom -
                (
                    (
                        (
                            $percentage -
                            $overview_axis_min
                        ) /
                        $overview_axis_range
                    ) *
                    $overview_plot_height
                );
        };

    $overview_grid = '';
    $overview_axis_labels = '';

    for (
        $grid_percentage =
            $overview_axis_min;
        $grid_percentage <=
            $overview_axis_max;
        $grid_percentage += 10
    ) {
        $grid_y =
            $overview_y(
                $grid_percentage
            );

        $overview_grid .= '
            <line
                x1="70"
                y1="' . $grid_y . '"
                x2="550"
                y2="' . $grid_y . '"
            />
        ';

        $overview_axis_labels .= '
            <text
                x="54"
                y="' . ($grid_y + 4) . '"
                text-anchor="end"
            >' .
                esc_html(
                    $grid_percentage
                ) .
            '%</text>
        ';
    }

    $overview_points = [];
    $overview_markers = '';

    foreach (
        $overview_values
        as $section_name => $percentage
    ) {
        $x = $overview_x[$section_name];
        $y = $overview_y($percentage);

        $overview_points[] =
            $x . ',' . $y;

        $overview_markers .= '
            <circle
                cx="' . $x . '"
                cy="' . $y . '"
                r="6"
                fill="' .
                    esc_attr(
                        $report_colour
                    ) .
                '"
            />

            <text
                x="' . $x . '"
                y="' . ($y - 10) . '"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="9"
                font-weight="600"
                fill="#111111"
            >' .
                esc_html(
                    round($percentage)
                ) .
            '%</text>

            <text
                x="' . $x . '"
                y="216"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="11"
                font-weight="600"
                fill="#111111"
            >' .
                esc_html(
                    $section_name
                ) .
            '</text>
        ';
    }

    $overview_line = '';

    if (
        count($overview_points) >= 2
    ) {
        $overview_line = '
            <polyline
                points="' .
                    esc_attr(
                        implode(
                            ' ',
                            $overview_points
                        )
                    ) .
                '"
                fill="none"
                stroke="' .
                    esc_attr(
                        $report_colour
                    ) .
                '"
                stroke-width="4"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        ';
    }

    $overview_svg = '
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="600"
        height="245"
        viewBox="0 0 600 245"
    >
        <rect
            width="600"
            height="245"
            fill="#ffffff"
        />

        <g
            stroke="#dddddd"
            stroke-width="1"
        >
            ' . $overview_grid . '
        </g>

        <g
            fill="#555555"
            font-family="DejaVu Sans"
            font-size="10"
        >
            ' . $overview_axis_labels . '


        </g>

        ' . $overview_line . '
        ' . $overview_markers . '
    </svg>
    ';

    $overview_chart_data =
        'data:image/svg+xml;base64,' .
        base64_encode($overview_svg);

    /*
     * Single-stage capability line chart.
     */
    $single_stage_section_chart = static function (
        array $measures,
        string $stage_key,
        string $stage_label,
        string $colour
    ) {
        $measures = array_values(
            array_filter(
                $measures,
                static function ($measure) use ($stage_key) {
                    return
                        array_key_exists(
                            $stage_key,
                            $measure
                        ) &&
                        $measure[$stage_key] !== null &&
                        is_numeric(
                            $measure[$stage_key]
                        );
                }
            )
        );

        if (empty($measures)) {
            return '';
        }

        $percentages = [];

        foreach ($measures as $measure) {
            $percentages[] =
                (
                    (float) $measure[$stage_key] /
                    7
                ) * 100;
        }

        $axis_min = max(
            0,
            floor(
                (
                    min($percentages) - 5
                ) / 5
            ) * 5
        );

        $axis_max = min(
            100,
            ceil(
                (
                    max($percentages) + 5
                ) / 5
            ) * 5
        );

        if (
            ($axis_max - $axis_min) < 20
        ) {
            $axis_min =
                max(
                    0,
                    $axis_min - 10
                );

            $axis_max =
                min(
                    100,
                    $axis_max + 10
                );
        }

        $plot_left = 70;
        $plot_right = 550;
        $plot_top = 45;
        $plot_bottom = 185;

        $plot_width =
            $plot_right - $plot_left;

        $plot_height =
            $plot_bottom - $plot_top;

        $axis_range =
            max(
                1,
                $axis_max - $axis_min
            );

        $chart_y =
            static function ($percentage) use (
                $plot_bottom,
                $plot_height,
                $axis_min,
                $axis_range
            ) {
                return
                    $plot_bottom -
                    (
                        (
                            (
                                $percentage -
                                $axis_min
                            ) /
                            $axis_range
                        ) *
                        $plot_height
                    );
            };

        $grid = '';
        $axis_labels = '';

        for (
            $grid_percentage = $axis_min;
            $grid_percentage <= $axis_max;
            $grid_percentage += 10
        ) {
            $grid_y =
                $chart_y(
                    $grid_percentage
                );

            $grid .= '
                <line
                    x1="' . $plot_left . '"
                    y1="' . $grid_y . '"
                    x2="' . $plot_right . '"
                    y2="' . $grid_y . '"
                />
            ';

            $axis_labels .= '
                <text
                    x="54"
                    y="' . ($grid_y + 4) . '"
                    text-anchor="end"
                >' .
                    esc_html(
                        $grid_percentage
                    ) .
                '%</text>
            ';
        }

        $count = count($measures);

        $slot_width =
            $plot_width /
            max(
                1,
                $count
            );

        $points = [];
        $markers = '';

        foreach (
            $measures
            as $index => $measure
        ) {
            $percentage =
                (
                    (float) $measure[$stage_key] /
                    7
                ) * 100;

            $x =
                $plot_left +
                ($slot_width * $index) +
                ($slot_width / 2);

            $y = $chart_y($percentage);

            $points[] =
                $x . ',' . $y;

            $label =
                (string) $measure['measure'];

            $label_words =
                preg_split(
                    '/\s+/',
                    trim($label)
                ) ?: [];

            $label_lines = [$label];

            if (
                count($label_words) > 1
            ) {
                $best_split = 1;
                $best_difference =
                    PHP_INT_MAX;

                for (
                    $split = 1;
                    $split <
                        count($label_words);
                    $split++
                ) {
                    $first_line =
                        implode(
                            ' ',
                            array_slice(
                                $label_words,
                                0,
                                $split
                            )
                        );

                    $second_line =
                        implode(
                            ' ',
                            array_slice(
                                $label_words,
                                $split
                            )
                        );

                    $difference =
                        abs(
                            strlen($first_line) -
                            strlen($second_line)
                        );

                    if (
                        $difference <
                        $best_difference
                    ) {
                        $best_difference =
                            $difference;

                        $best_split =
                            $split;
                    }
                }

                $label_lines = [
                    implode(
                        ' ',
                        array_slice(
                            $label_words,
                            0,
                            $best_split
                        )
                    ),
                    implode(
                        ' ',
                        array_slice(
                            $label_words,
                            $best_split
                        )
                    ),
                ];
            }

            $label_svg = '';

            foreach (
                $label_lines
                as $line_index =>
                    $label_line
            ) {
                $label_svg .= '
                    <text
                        x="' . $x . '"
                        y="' .
                            (
                                207 +
                                ($line_index * 9)
                            ) .
                        '"
                        text-anchor="middle"
                        font-family="DejaVu Sans"
                        font-size="7"
                        fill="#333333"
                    >' .
                        esc_html(
                            $label_line
                        ) .
                    '</text>
                ';
            }

            $markers .= '
                <circle
                    cx="' . $x . '"
                    cy="' . $y . '"
                    r="5"
                    fill="' .
                        esc_attr(
                            $colour
                        ) .
                    '"
                />

                <text
                    x="' . $x . '"
                    y="' . ($y - 8) . '"
                    text-anchor="middle"
                    font-family="DejaVu Sans"
                    font-size="8"
                    font-weight="600"
                    fill="#111111"
                >' .
                    esc_html(
                        round($percentage)
                    ) .
                '%</text>

                ' . $label_svg . '
            ';
        }

        $line = '';

        if (count($points) >= 2) {
            $line = '
                <polyline
                    points="' .
                        esc_attr(
                            implode(
                                ' ',
                                $points
                            )
                        ) .
                    '"
                    fill="none"
                    stroke="' .
                        esc_attr(
                            $colour
                        ) .
                    '"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            ';
        }

        $svg = '
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="600"
            height="240"
            viewBox="0 0 600 240"
        >
            <rect
                width="600"
                height="240"
                fill="#ffffff"
            />

            <g
                stroke="#dddddd"
                stroke-width="1"
            >
                ' . $grid . '
            </g>

            <g
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="10"
            >
                ' . $axis_labels . '

                <line
                    x1="438"
                    y1="20"
                    x2="458"
                    y2="20"
                    stroke="' .
                        esc_attr(
                            $colour
                        ) .
                    '"
                    stroke-width="3"
                />

                <circle
                    cx="448"
                    cy="20"
                    r="4"
                    fill="' .
                        esc_attr(
                            $colour
                        ) .
                    '"
                />

                <text
                    x="466"
                    y="24"
                >' .
                    esc_html(
                        $stage_label
                    ) .
                '</text>
            </g>

            ' . $line . '
            ' . $markers . '
        </svg>
        ';

        return
            'data:image/svg+xml;base64,' .
            base64_encode($svg);
    };

    foreach ($overview_sections as $section_name => $measures) {
        $section_chart_data[$section_name] =
            $single_stage_section_chart(
                $measures,
                $single_stage_key,
                $single_stage_label,
                $report_colour
            );
    }

    /*
     * Single-stage report text.
     */
    $single_stage_summary = static function (
        array $measures,
        array $text_map,
        string $stage_key,
        string $stage_label
    ) {

$html = '';

        foreach ($measures as $measure) {
            if (
                !array_key_exists($stage_key, $measure) ||
                $measure[$stage_key] === null ||
                !is_numeric($measure[$stage_key])
            ) {
                continue;
            }

            $custom_text = isset($text_map[$measure['measure']])
                ? trim($text_map[$measure['measure']])
                : '';
            if (
                stripos(
                    $custom_text,
                    'Participants demonstrated positive growth in '
                ) === 0
            ) {
                $custom_text = '';
            }


            if ($custom_text === '') {
                $custom_text = sprintf(
                    '%s average score: %s out of 7.',
                    $stage_label,
                    number_format(
                        (float) $measure[$stage_key],
                        2
                    )
                );
            }

            $html .= '
                <div class="growth-summary-line">
                    <strong>
                        ' . esc_html($measure['measure']) . '
                    </strong>

                    <div>
                        ' . esc_html($custom_text) . '
                    </div>
                </div>
            ';
        }

        return $html;
    };

    $insight_growth_summary_html =
        $single_stage_summary(
            $insight_measures,
            $insight_text_map,
            $single_stage_key,
            $single_stage_label
        );

    $influence_growth_summary_html =
        $single_stage_summary(
            $influence_measures,
            $influence_text_map,
            $single_stage_key,
            $single_stage_label
        );

    $impact_growth_summary_html =
        $single_stage_summary(
            $impact_measures,
            $impact_text_map,
            $single_stage_key,
            $single_stage_label
        );
}
/*
 * Sprint 3: two-stage report rendering.
 *
 * The legacy multi-stage charts assume all three evaluation stages are
 * present. When exactly two real stages exist, rebuild the primary and
 * Key Growth charts using only those stages so missing data is never
 * rendered as zero.
 */
if (count($primary_available_stages) === 2) {
    $two_stage_keys = array_keys($primary_available_stages);
    $two_stage_labels = array_values($primary_available_stages);

    $two_stage_average = static function (
        array $measures,
        string $stage_key
    ) {
        $scores = [];

        foreach ($measures as $measure) {
            if (
                array_key_exists($stage_key, $measure) &&
                $measure[$stage_key] !== null &&
                is_numeric($measure[$stage_key])
            ) {
                $scores[] = (float) $measure[$stage_key];
            }
        }

        return $scores
            ? array_sum($scores) / count($scores)
            : null;
    };

    /*
     * Overview: show only the two real evaluation points.
     */
    $two_stage_overview_x = [
        $two_stage_keys[0] => 100,
        $two_stage_keys[1] => 510,
    ];

    $two_stage_overview_styles = [
        'Insight' => [
            'colour' => $report_colour,
            'width' => 4,
            'dash' => '',
            'radius' => 6,
        ],
        'Influence' => [
            'colour' => '#111111',
            'width' => 2,
            'dash' => '7,5',
            'radius' => 4,
        ],
        'Impact' => [
            'colour' => '#777777',
            'width' => 2,
            'dash' => '2,4',
            'radius' => 4,
        ],
    ];

    /*
     * Scale the Overview to the real data range with approximately
     * 10 percentage points of context above and below.
     */
    $two_stage_overview_percentages = [];

    foreach ($overview_sections as $overview_measures) {
        foreach ($two_stage_keys as $stage_key) {
            $average = $two_stage_average(
                $overview_measures,
                $stage_key
            );

            if ($average !== null) {
                $two_stage_overview_percentages[] =
                    ($average / 7) * 100;
            }
        }
    }

    $two_stage_overview_axis_min = max(
        0,
        floor(
            (
                min($two_stage_overview_percentages) - 5
            ) / 5
        ) * 5
    );

    $two_stage_overview_axis_max = min(
        100,
        ceil(
            (
                max($two_stage_overview_percentages) + 5
            ) / 5
        ) * 5
    );

    if (
        (
            $two_stage_overview_axis_max -
            $two_stage_overview_axis_min
        ) < 20
    ) {
        $two_stage_overview_axis_min =
            max(
                0,
                $two_stage_overview_axis_min - 10
            );

        $two_stage_overview_axis_max =
            min(
                100,
                $two_stage_overview_axis_max + 10
            );
    }

    $two_stage_overview_plot_top = 50;
    $two_stage_overview_plot_bottom = 220;

    $two_stage_overview_plot_height =
        $two_stage_overview_plot_bottom -
        $two_stage_overview_plot_top;

    $two_stage_overview_axis_range =
        max(
            1,
            $two_stage_overview_axis_max -
            $two_stage_overview_axis_min
        );

    $two_stage_overview_y =
        static function ($percentage) use (
            $two_stage_overview_plot_bottom,
            $two_stage_overview_plot_height,
            $two_stage_overview_axis_min,
            $two_stage_overview_axis_range
        ) {
            return
                $two_stage_overview_plot_bottom -
                (
                    (
                        (
                            $percentage -
                            $two_stage_overview_axis_min
                        ) /
                        $two_stage_overview_axis_range
                    ) *
                    $two_stage_overview_plot_height
                );
        };

    $two_stage_overview_grid = '';
    $two_stage_overview_axis_labels = '';

    for (
        $grid_percentage =
            $two_stage_overview_axis_min;
        $grid_percentage <=
            $two_stage_overview_axis_max;
        $grid_percentage += 10
    ) {
        $grid_y =
            $two_stage_overview_y(
                $grid_percentage
            );

        $two_stage_overview_grid .= '
            <line
                x1="70"
                y1="' . $grid_y . '"
                x2="550"
                y2="' . $grid_y . '"
            />
        ';

        $two_stage_overview_axis_labels .= '
            <text
                x="54"
                y="' . ($grid_y + 4) . '"
                text-anchor="end"
            >' .
                esc_html(
                    $grid_percentage
                ) .
            '%</text>
        ';
    }

    $two_stage_overview_series = '';

    foreach ($overview_sections as $section_name => $measures) {
        $style = $two_stage_overview_styles[$section_name];
        $points = [];
        $circles = '';

        foreach ($two_stage_keys as $stage_key) {
            $average = $two_stage_average(
                $measures,
                $stage_key
            );

            if ($average === null) {
                continue;
            }

            $percentage = ($average / 7) * 100;
            $x = $two_stage_overview_x[$stage_key];
            $y = $two_stage_overview_y($percentage);

            $points[] = $x . ',' . $y;

            $circles .= '
                <circle
                    cx="' . $x . '"
                    cy="' . $y . '"
                    r="' . $style['radius'] . '"
                    fill="' . esc_attr($style['colour']) . '"
                />
            ';
        }

        if (count($points) >= 2) {
            $dash_attribute = $style['dash'] !== ''
                ? ' stroke-dasharray="' .
                    esc_attr($style['dash']) .
                    '"'
                : '';

            $two_stage_overview_series .= '
                <polyline
                    points="' . esc_attr(implode(' ', $points)) . '"
                    fill="none"
                    stroke="' . esc_attr($style['colour']) . '"
                    stroke-width="' . esc_attr($style['width']) . '"
                    ' . $dash_attribute . '
                />

                ' . $circles . '
            ';
        }
    }

    $two_stage_overview_labels = '';

    foreach ($two_stage_keys as $stage_key) {
        $two_stage_overview_labels .= '
            <text
                x="' . $two_stage_overview_x[$stage_key] . '"
                y="244"
                text-anchor="middle"
            >' .
                esc_html(
                    $primary_available_stages[$stage_key]
                ) .
            '</text>
        ';
    }

    $overview_svg = '
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="600"
        height="260"
        viewBox="0 0 600 260"
    >
        <rect width="600" height="260" fill="#ffffff"/>

        <g stroke="#dddddd" stroke-width="1">
            ' . $two_stage_overview_grid . '
        </g>

        <g
            fill="#555555"
            font-family="DejaVu Sans"
            font-size="12"
        >
            ' . $two_stage_overview_axis_labels . '

            ' . $two_stage_overview_labels . '
        </g>

        ' . $two_stage_overview_series . '
    </svg>
    ';

    $overview_chart_data =
        'data:image/svg+xml;base64,' .
        base64_encode($overview_svg);

    /*
     * Capability line chart using only the two real stages.
     */
    $two_stage_section_chart = static function (
        array $measures,
        array $stage_keys,
        array $stage_labels,
        array $line_colours
    ) {
        $percentages = [];

        foreach ($measures as $measure) {
            foreach ($stage_keys as $stage_key) {
                if (
                    isset($measure[$stage_key]) &&
                    is_numeric($measure[$stage_key])
                ) {
                    $percentages[] =
                        ((float) $measure[$stage_key] / 7) * 100;
                }
            }
        }

        if (!$percentages) {
            return '';
        }

        $axis_min = max(
            0,
            floor((min($percentages) - 5) / 5) * 5
        );

        $axis_max = min(
            100,
            ceil((max($percentages) + 5) / 5) * 5
        );

        if (($axis_max - $axis_min) < 20) {
            $axis_min = max(0, $axis_min - 10);
            $axis_max = min(100, $axis_max + 10);
        }

        $plot_top = 36;
        $plot_bottom = 205;
        $plot_height = $plot_bottom - $plot_top;
        $axis_range = max(1, $axis_max - $axis_min);

        $chart_y = static function ($percentage) use (
            $plot_bottom,
            $plot_height,
            $axis_min,
            $axis_range
        ) {
            return $plot_bottom -
                (
                    (($percentage - $axis_min) / $axis_range) *
                    $plot_height
                );
        };

        $x_positions = [
            $stage_keys[0] => 100,
            $stage_keys[1] => 510,
        ];

        $grid = '';

        for (
            $grid_percentage = $axis_min;
            $grid_percentage <= $axis_max;
            $grid_percentage += 10
        ) {
            $grid_y = $chart_y($grid_percentage);

            $grid .= '
                <line
                    x1="65"
                    y1="' . $grid_y . '"
                    x2="550"
                    y2="' . $grid_y . '"
                />

                <text
                    x="54"
                    y="' . ($grid_y + 4) . '"
                    text-anchor="end"
                >' . esc_html($grid_percentage) . '%</text>
            ';
        }

        $lines = '';
        $legend = '';

        foreach ($measures as $index => $measure) {
            $colour = $line_colours[
                $index % count($line_colours)
            ];

            $points = [];
            $circles = '';

            foreach ($stage_keys as $stage_key) {
                if (
                    !isset($measure[$stage_key]) ||
                    !is_numeric($measure[$stage_key])
                ) {
                    continue;
                }

                $percentage =
                    ((float) $measure[$stage_key] / 7) * 100;

                $x = $x_positions[$stage_key];
                $y = $chart_y($percentage);

                $points[] = $x . ',' . $y;

                $circles .= '
                    <circle
                        cx="' . $x . '"
                        cy="' . $y . '"
                        r="4"
                    />
                ';
            }

            if (count($points) >= 2) {
                $lines .= '
                    <polyline
                        points="' .
                            esc_attr(implode(' ', $points)) .
                        '"
                        fill="none"
                        stroke="' . esc_attr($colour) . '"
                        stroke-width="2.5"
                    />

                    <g fill="' . esc_attr($colour) . '">
                        ' . $circles . '
                    </g>
                ';
            }

            $legend_column = $index % 2;
            $legend_row = floor($index / 2);

            $legend_x = $legend_column === 0
                ? 72
                : 320;

            $legend_y = 239 + ($legend_row * 14);

            $legend .= '
                <line
                    x1="' . $legend_x . '"
                    y1="' . ($legend_y - 3) . '"
                    x2="' . ($legend_x + 18) . '"
                    y2="' . ($legend_y - 3) . '"
                    stroke="' . esc_attr($colour) . '"
                    stroke-width="3"
                />

                <text
                    x="' . ($legend_x + 24) . '"
                    y="' . $legend_y . '"
                >' . esc_html($measure['measure']) . '</text>
            ';
        }

        $stage_label_html = '';

        foreach ($stage_keys as $stage_key) {
            $stage_label_html .= '
                <text
                    x="' . $x_positions[$stage_key] . '"
                    y="224"
                    text-anchor="middle"
                >' .
                    esc_html($stage_labels[$stage_key]) .
                '</text>
            ';
        }

        $svg = '
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="600"
            height="300"
            viewBox="0 0 600 300"
        >
            <rect width="600" height="300" fill="#ffffff"/>

            <g
                stroke="#dedede"
                stroke-width="1"
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="10"
            >
                ' . $grid . '
            </g>

            <g
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="10"
            >
                ' . $stage_label_html . '
                ' . $legend . '
            </g>

            ' . $lines . '
        </svg>
        ';

        return
            'data:image/svg+xml;base64,' .
            base64_encode($svg);
    };

    $two_stage_line_colours = [
        $report_colour,
        '#111111',
        '#666666',
        $mix_report_colour(
            $report_colour,
            '#111111',
            0.28
        ),
        $mix_report_colour(
            $report_colour,
            '#ffffff',
            0.28
        ),
        $mix_report_colour(
            $report_colour,
            '#111111',
            0.52
        ),
        $mix_report_colour(
            $report_colour,
            '#ffffff',
            0.48
        ),
    ];

    foreach ($overview_sections as $section_name => $measures) {
        $section_chart_data[$section_name] =
            $two_stage_section_chart(
                $measures,
                $two_stage_keys,
                $primary_available_stages,
                $two_stage_line_colours
            );
    }

    /*
     * Key Growth line chart: rank by change between the two real
     * stages and connect the stage values across the top three
     * capabilities.
     */
    $two_stage_growth_chart = static function (
        array $measures,
        array $stage_keys,
        array $stage_labels,
        string $first_colour,
        string $second_colour
    ) {
        $items = [];

        foreach ($measures as $measure) {
            $first_key = $stage_keys[0];
            $second_key = $stage_keys[1];

            if (
                !isset($measure[$first_key]) ||
                !is_numeric($measure[$first_key]) ||
                !isset($measure[$second_key]) ||
                !is_numeric($measure[$second_key])
            ) {
                continue;
            }

            $first =
                ((float) $measure[$first_key] / 7) * 100;

            $second =
                ((float) $measure[$second_key] / 7) * 100;

            $items[] = [
                'measure' => $measure['measure'],
                'first' => $first,
                'second' => $second,
                'increase' => $second - $first,
            ];
        }

        usort(
            $items,
            static function ($first, $second) {
                return
                    $second['increase'] <=>
                    $first['increase'];
            }
        );

        $items = array_slice($items, 0, 3);

        $centres = [150, 300, 450];
        $bars = '';

        foreach ($items as $index => $item) {
            $centre = $centres[$index];

            $first_height = $item['first'] * 1.35;
            $second_height = $item['second'] * 1.35;

            $first_y = 170 - $first_height;
            $second_y = 170 - $second_height;

            $bars .= '
                <rect
                    x="' . ($centre - 31) . '"
                    y="' . $first_y . '"
                    width="28"
                    height="' . $first_height . '"
                    fill="' . esc_attr($first_colour) . '"
                />

                <rect
                    x="' . ($centre + 3) . '"
                    y="' . $second_y . '"
                    width="28"
                    height="' . $second_height . '"
                    fill="' . esc_attr($second_colour) . '"
                />

                <text
                    x="' . $centre . '"
                    y="192"
                    text-anchor="middle"
                    font-family="DejaVu Sans"
                    font-size="7"
                    font-weight="600"
                    fill="#111111"
                >' . esc_html($item['measure']) . '</text>
            ';
        }

        $svg = '
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="600"
            height="205"
            viewBox="0 0 600 205"
        >
            <rect width="600" height="205" fill="#ffffff"/>

            <g
                font-family="DejaVu Sans"
                font-size="9"
                fill="#555555"
            >
                <rect
                    x="360"
                    y="8"
                    width="11"
                    height="11"
                    fill="' . esc_attr($first_colour) . '"
                />

                <text x="376" y="17">
                    ' . esc_html($stage_labels[$stage_keys[0]]) . '
                </text>

                <rect
                    x="470"
                    y="8"
                    width="11"
                    height="11"
                    fill="' . esc_attr($second_colour) . '"
                />

                <text x="486" y="17">
                    ' . esc_html($stage_labels[$stage_keys[1]]) . '
                </text>
            </g>

            <g stroke="#dedede" stroke-width="1">
                <line x1="65" y1="35" x2="550" y2="35"/>
                <line x1="65" y1="62" x2="550" y2="62"/>
                <line x1="65" y1="89" x2="550" y2="89"/>
                <line x1="65" y1="116" x2="550" y2="116"/>
                <line x1="65" y1="143" x2="550" y2="143"/>
                <line x1="65" y1="170" x2="550" y2="170"/>
            </g>

            <g
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="9"
            >
                <text x="38" y="38">100%</text>
                <text x="45" y="65">80%</text>
                <text x="45" y="92">60%</text>
                <text x="45" y="119">40%</text>
                <text x="45" y="146">20%</text>
                <text x="52" y="173">0%</text>
            </g>

            ' . $bars . '
        </svg>
        ';

        return
            'data:image/svg+xml;base64,' .
            base64_encode($svg);
    };

    foreach ($overview_sections as $section_name => $measures) {
        $section_movers_chart_data[$section_name] =
            $two_stage_growth_chart(
                $measures,
                $two_stage_keys,
                $primary_available_stages,
                $report_colour_light,
                $report_colour
            );
    }

    /*
     * Rebuild these blocks because the original strings were created
     * before the two-stage chart overrides above.
     */
    $section_secondary_html = [
        'Insight' => '
            <h3 class="section-movers-title">
                Key Growth
            </h3>

            <div class="movers-chart">
                <img
                    src="' .
                        esc_attr(
                            $section_movers_chart_data['Insight']
                        ) .
                    '"
                    alt=""
                >
            </div>
        ',
        'Influence' => '
            <h3 class="section-movers-title">
                Key Growth
            </h3>

            <div class="movers-chart">
                <img
                    src="' .
                        esc_attr(
                            $section_movers_chart_data['Influence']
                        ) .
                    '"
                    alt=""
                >
            </div>
        ',
        'Impact' => '
            <h3 class="section-movers-title">
                Key Growth
            </h3>

            <div class="movers-chart">
                <img
                    src="' .
                        esc_attr(
                            $section_movers_chart_data['Impact']
                        ) .
                    '"
                    alt=""
                >
            </div>
        ',
    ];
}
/*
 * Phase 2D: full-stage, signed relative growth comparisons.
 * Single-program PDF rendering remains unchanged.
 */
if ($has_comparisons) {
    if (count($comparison_selections) > 2) {
        wp_die('Select no more than two comparison programs.');
    }

    $requested_selections = [
        [
            'program' => $program,
            'cohort' => $cohort,
            'is_primary' => true,
        ],
    ];

    foreach ($comparison_selections as $selection) {
        $requested_selections[] = [
            'program' => (string) $selection['program'],
            'cohort' => (string) $selection['cohort'],
            'is_primary' => false,
        ];
    }

    $all_program_report = TLE_Report_Service::get_report();
    if (is_wp_error($all_program_report)) {
        wp_die(
            'Unable to retrieve program comparisons: ' .
            esc_html($all_program_report->get_error_message())
        );
    }

    try {
        $comparison_rows = TLE_Comparison_Growth::build_series(
            $all_program_report['groups'],
            $requested_selections
        );
    } catch (InvalidArgumentException $error) {
        wp_die(
            'Unable to compare selected programs: ' .
            esc_html($error->getMessage())
        );
    }

    $comparison_section_keys = [
        'Insight' => 'insight',
        'Influence' => 'influence',
        'Impact' => 'impact',
    ];

    $comparison_text_map = [
        'Insight' => trim($insight_comparison_text),
        'Influence' => trim($influence_comparison_text),
        'Impact' => trim($impact_comparison_text),
    ];

    foreach ($comparison_section_keys as $section_name => $section_key) {
        try {
            $comparison_chart_data = TLE_Comparison_Chart::render(
                $comparison_rows,
                $section_key,
                $report_colour
            );
        } catch (InvalidArgumentException $error) {
            wp_die(
                'Unable to render comparison chart: ' .
                esc_html($error->getMessage())
            );
        }

        $comparison_context_html = '';
        if ($comparison_text_map[$section_name] !== '') {
            $comparison_context_html = '
                <div class="comparison-context-box">' .
                    nl2br(esc_html($comparison_text_map[$section_name])) .
                '</div>
            ';
        }

        $section_secondary_html[$section_name] = '
            <h3 class="section-movers-title">
                Program Growth Comparison
            </h3>
            <p class="comparison-growth-subtitle">Relative percentage growth from Pre-program to Delayed</p>
            <div class="movers-chart">
                <img src="' . esc_attr($comparison_chart_data) . '"
                     alt="Relative percentage growth by capability and program">
            </div>
            ' . $comparison_context_html . '
        ';
    }

    // Phase 3A: replace the legacy primary-only Growth Summary on
    // comparison reports. Non-comparison and Manager pages are unchanged.
    $posted_growth_summaries = [];
    if (
        isset($_POST['growth_summary_text']) &&
        is_array($_POST['growth_summary_text'])
    ) {
        $posted_growth_summaries = wp_unslash($_POST['growth_summary_text']);
    }
    $section_summary_heading = 'Key Growth Program Summaries';
    $insight_growth_summary_html = TLE_Comparison_Summary::render_section(
        $comparison_rows,
        'insight',
        $posted_growth_summaries
    );
    $influence_growth_summary_html = TLE_Comparison_Summary::render_section(
        $comparison_rows,
        'influence',
        $posted_growth_summaries
    );
    $impact_growth_summary_html = TLE_Comparison_Summary::render_section(
        $comparison_rows,
        'impact',
        $posted_growth_summaries
    );
}
/*
 * Sprint 3: Manager PDF pages.
 *
 * Manager results are filtered by the selected program and cohort.
 * Historical Form 50 submissions without matching context are never
 * attributed to the selected report.
 */
/*
 * Global PDF brand logo.
 * Must be available whether or not Manager pages are included.
 */
$brand_logo_path =
    dirname(__DIR__) .
    '/assets/images/tasmanian-leaders-logo-black.png';

$brand_logo_html =
    '<div class="brand">Tasmanian Leaders</div>';

if (is_readable($brand_logo_path)) {
    $brand_logo_data =
        'data:image/png;base64,' .
        base64_encode(
            file_get_contents($brand_logo_path)
        );

    $brand_logo_html =
        '<div class="brand">' .
            '<img src="' .
                esc_attr($brand_logo_data) .
                '" alt="Tasmanian Leaders">' .
        '</div>';
}

$total_pages = $include_manager ? 7 : 5;
$manager_pages_html = '';

if ($include_manager) {
    // Retrieve Manager responses for the primary selected program and cohort.
    $manager_filters = [
        'program' => $program,
        'cohort' => $cohort,
];
    $manager_service_file = dirname(__DIR__) . '/includes/class-manager-report-service.php';
    if (!class_exists('TLE_Manager_Report_Service') && is_readable($manager_service_file)) {
        require_once $manager_service_file;
    }
    if (!class_exists('TLE_Manager_Report_Service')) {
        wp_die('Manager reporting backend is missing.');
    }
    $manager_result = TLE_Manager_Report_Service::get_report($manager_filters);
    if (is_wp_error($manager_result)) {
        wp_die(esc_html('Unable to retrieve Manager results: ' . $manager_result->get_error_message()));
    }

    // Reuse the same validated dashboard colour used by the rest of this export.
    $manager_base_colour = $report_colour;
    if (strlen(ltrim($manager_base_colour, '#')) === 3) {
        $manager_short_hex = ltrim($manager_base_colour, '#');
        $manager_base_colour = '#' . $manager_short_hex[0] . $manager_short_hex[0] . $manager_short_hex[1] . $manager_short_hex[1] . $manager_short_hex[2] . $manager_short_hex[2];
    }
    $manager_summary_colour = $report_colour_light;
    $manager_text_colour = static function ($hex) {
        $hex = ltrim((string) $hex, '#');
        $channels = [];
        foreach ([0, 2, 4] as $offset) {
            $v = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $v <= 0.04045 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
        }
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
        return ($luminance + 0.05) / 0.05 >= 1.05 / ($luminance + 0.05) ? '#111111' : '#ffffff';
    };
    // Very dark selections need lighter tones so answer categories remain distinct.
    $manager_hex = ltrim($manager_base_colour, '#');
    $manager_is_dark = max(hexdec(substr($manager_hex, 0, 2)), hexdec(substr($manager_hex, 2, 2)), hexdec(substr($manager_hex, 4, 2))) < 65;
    $manager_tones = $manager_is_dark ? [
        $mix_report_colour($manager_base_colour, '#ffffff', 0.82),
        $mix_report_colour($manager_base_colour, '#ffffff', 0.62),
        $mix_report_colour($manager_base_colour, '#ffffff', 0.92),
        $mix_report_colour($manager_base_colour, '#ffffff', 0.32),
        $manager_base_colour,
    ] : [
        $mix_report_colour($manager_base_colour, '#000000', 0.72),
        $mix_report_colour($manager_base_colour, '#000000', 0.44),
        $mix_report_colour($manager_base_colour, '#ffffff', 0.78),
        $manager_base_colour,
        $mix_report_colour($manager_base_colour, '#000000', 0.18),
    ];
    $manager_colour = static function ($label) use ($manager_tones) {
        $indices = [
            'strongly disagree' => 0, 'disagree' => 1, 'neutral' => 2, 'agree' => 3, 'strongly agree' => 4,
            'extremely dissatisfied' => 0, 'somewhat dissatisfied' => 1, 'somewhat disatisfied' => 1,
            'somewhat satisfied' => 3, 'extremely satisfied' => 4, 'no' => 0, 'yes' => 3,
        ];
        return $manager_tones[$indices[strtolower(trim((string) $label))] ?? 2];
    };
    $manager_format_percentage = static function ($value) {
        return number_format((float) $value, 0, '.', '');
    };
    // Allocate whole-number labels within each rating group so totals stay consistent.
    // Backend percentages, response counts and bar widths are never modified.
    $manager_display_percentages = static function (array $question) {
        $labels = array_fill_keys(array_keys($question['categories']), 0);
        if ((int) $question['answered_count'] === 0) { return $labels; }
        $has_positive = $question['positive_percentage'] !== null;
        $positive_target = $has_positive ? (int) round($question['positive_percentage']) : 0;
        $groups = $has_positive ? [false, true] : [null];
        foreach ($groups as $positive) {
            $target = $positive === null ? 100 : ($positive ? $positive_target : 100 - $positive_target);
            $remainders = [];
            $allocated = 0;
            foreach ($question['categories'] as $index => $category) {
                if ($positive !== null && !empty($category['positive']) !== $positive) { continue; }
                if ($category['percentage'] === null || (float) $category['percentage'] <= 0) { continue; }
                $value = (float) $category['percentage'];
                $labels[$index] = (int) floor($value);
                $allocated += $labels[$index];
                $remainders[$index] = $value - $labels[$index];
            }
            // Stable sorting resolves equal remainders in the backend category order.
            arsort($remainders, SORT_NUMERIC);
            $remaining = $target - $allocated;
            foreach ($remainders as $index => $remainder) {
                if ($remaining <= 0) { break; }
                $labels[$index]++;
                $remaining--;
            }
        }
        return $labels;
    };
    $manager_legend = static function (array $categories) use ($manager_colour) {
        // Fixed columns and explicit text positions are reliable in Dompdf.
        $count = count($categories);
        if (!$count) { return ''; }
        $start = $count === 2 ? 250 : 0;
        $available = $count === 2 ? 340 : 840;
        $cell_width = $available / $count;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="840" height="58" viewBox="0 0 840 58">';
        foreach ($categories as $i => $category) {
            $x = $start + $i * $cell_width + 4;
            // Wrap long satisfaction labels rather than shrinking the entire legend.
            $words = preg_split('/\s+/', trim((string) $category['label']));
            $lines = [''];
            $limit = max(10, (int) floor(($cell_width - 32) / 9.4));
            foreach ($words as $word) {
                $index = count($lines) - 1;
                $candidate = trim($lines[$index] . ' ' . $word);
                if (strlen($candidate) > $limit && $lines[$index] !== '') { $lines[] = $word; }
                else { $lines[$index] = $candidate; }
            }
            $svg .= '<rect x="' . $x . '" y="9" width="20" height="13" fill="' . $manager_colour($category['label']) . '"/>';
            foreach ($lines as $line_index => $line) {
                $svg .= '<text x="' . ($x + 27) . '" y="' . (21 + 19 * $line_index)
                    . '" font-family="DejaVu Sans" font-size="16" fill="#111111">' . esc_html($line) . '</text>';
            }
        }
        return '<img class="manager-ux-legend-image" src="data:image/svg+xml;base64,'
            . base64_encode($svg . '</svg>') . '" alt="' . esc_attr(implode(', ', array_column($categories, 'label'))) . '">';
    };
    $manager_bar = static function (array $question) use ($manager_colour, $manager_text_colour, $manager_display_percentages) {
        if ((int) $question['answered_count'] === 0) {
            return '<div class="manager-ux-empty">Not available</div>';
        }
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="420" height="106" viewBox="0 0 420 106">';
        $x = 0.0;
        $callouts = [];
        $display_percentages = $manager_display_percentages($question);
        foreach ($question['categories'] as $category_index => $category) {
            $percentage = $category['percentage'];
            if ($percentage === null || (float) $percentage <= 0) { continue; }
            // Geometry and badges use original backend precision, never rounded labels.
            $width = (float) $percentage * 4.2;
            $colour = $manager_colour($category['label']);
            $svg .= '<rect x="' . number_format($x, 6, '.', '') . '" y="0" width="'
                . number_format($width, 6, '.', '') . '" height="72" fill="' . $colour . '"/>';
            $label = (string) $display_percentages[$category_index];
            $centre = $x + $width / 2;
            $text_width = strlen($label) * 7.5;
            if ($width >= $text_width + 6) {
                $svg .= '<text x="' . number_format($centre, 6, '.', '')
                    . '" y="42" text-anchor="middle" font-family="DejaVu Sans" font-size="13" fill="'
                    . $manager_text_colour($colour) . '">' . $label . '</text>';
            } else {
                // Small segments keep readable whole-number labels below the bar.
                $callouts[] = ['centre' => $centre, 'width' => $text_width, 'label' => $label];
            }
            $x += $width;
        }
        $right = 0.0;
        foreach ($callouts as &$callout) {
            $callout['x'] = max($callout['centre'], $callout['width'] / 2 + 2, $right + $callout['width'] / 2 + 7);
            $right = $callout['x'] + $callout['width'] / 2;
        }
        unset($callout);
        // Work back from the right edge so callouts cannot collide or leave the chart.
        $right = 418.0;
        for ($i = count($callouts) - 1; $i >= 0; $i--) {
            $callouts[$i]['x'] = min($callouts[$i]['x'], $right - $callouts[$i]['width'] / 2);
            $right = $callouts[$i]['x'] - $callouts[$i]['width'] / 2 - 7;
        }
        foreach ($callouts as $callout) {
            $svg .= '<path d="M' . number_format($callout['centre'], 3, '.', '') . ' 72 L'
                . number_format($callout['x'], 3, '.', '') . ' 84" fill="none" stroke="#555555" stroke-width="1"/>';
            $svg .= '<text x="' . number_format($callout['x'], 3, '.', '')
                . '" y="99" text-anchor="middle" font-family="DejaVu Sans" font-size="13" fill="#111111">'
                . $callout['label'] . '</text>';
        }
        return '<img class="manager-ux-bar" src="data:image/svg+xml;base64,' . base64_encode($svg . '</svg>')
            . '" alt="' . esc_attr($question['statement']) . '">';
    };
    $manager_row = static function (array $question) use ($manager_bar, $manager_base_colour, $manager_text_colour, $manager_format_percentage) {
        $has_rating = $question['positive_percentage'] !== null;
        $rating = $has_rating ? $manager_format_percentage($question['positive_percentage']) . '%' : 'N/A';
        $badge_label = stripos($question['statement'], 'short-term goals') !== false ? 'Agreement' : 'Positive';
        $background = $has_rating ? $manager_base_colour : '#e8ebe9';
        $foreground = $manager_text_colour($background);
        $badge_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="72" viewBox="0 0 150 72">'
            . '<rect width="150" height="72" fill="' . $background . '"/>'
            . '<text x="75" y="35" text-anchor="middle" font-family="DejaVu Sans" font-weight="bold" font-size="27" fill="' . $foreground . '">'
            . esc_html($rating) . '</text><text x="75" y="59" text-anchor="middle" font-family="DejaVu Sans" font-weight="bold" font-size="'
            . ($badge_label === 'Agreement' ? '15' : '18') . '" fill="' . $foreground . '">'
            . esc_html($has_rating ? $badge_label : 'Unavailable') . '</text></svg>';
        return '<tr class="manager-ux-row"><td class="manager-ux-statement">' . esc_html(str_replace('with the Tasmanian Leaders?', 'with Tasmanian Leaders?', (string) $question['statement']))
            . '</td><td class="manager-ux-chart-cell">' . $manager_bar($question) . '</td><td class="manager-ux-badge-cell">'
            . '<img class="manager-ux-badge-image" src="data:image/svg+xml;base64,' . base64_encode($badge_svg)
            . '" alt="' . esc_attr($rating . ' ' . ($has_rating ? $badge_label : 'Unavailable')) . '"></td></tr>';
    };

    $manager_page_specs = [];
    foreach ($manager_result['groups'] as $group) {
        foreach ($group['series'] as $series) {
            $questions = $series['questions'];
            // Present agreement items first, then satisfaction, employment and Yes/No,
            // as in the mock-up. Source order is stable within each section.
            $ordered = [];
            foreach ($questions as $i => $question) {
                $scale = (string) $question['scale'];
                $rank = strpos($scale, 'agreement') === 0 ? 0 : 1;
                if (stripos($question['statement'], 'employment opportunities') !== false) { $rank = 2; }
                if (count($question['categories']) === 2) { $rank = 3; }
                $ordered[] = ['rank' => $rank, 'index' => $i, 'question' => $question];
            }
            usort($ordered, static function ($a, $b) {
                return ($a['rank'] <=> $b['rank']) ?: ($a['index'] <=> $b['index']);
            });
            $questions = array_column($ordered, 'question');
            $chunks = array_chunk($questions, 9);
            if (!$chunks) { $chunks = [[]]; }
            foreach ($chunks as $chunk_index => $chunk) {
                $manager_page_specs[] = [
                    'questions' => $chunk, 'context' => $group['context'],
                    'title' => $series['title'], 'form_id' => $series['form_id'],
                    'submission_count' => $series['submission_count'],
                    'first_in_series' => $chunk_index === 0,
                ];
            }
        }
    }
    if (!$manager_page_specs) {
        $manager_page_specs[] = [
            'questions' => [], 'context' => [], 'title' => 'Manager responses',
            'form_id' => '', 'submission_count' => 0, 'first_in_series' => true,
        ];
    }
    $total_pages = 5 + count($manager_page_specs);
    foreach ($manager_page_specs as $page_index => $spec) {
        $first = $spec['first_in_series'];
        $context_parts = [];
        foreach (['program_group', 'program', 'cohort'] as $context_key) {
            if (!empty($spec['context'][$context_key])) { $context_parts[] = $spec['context'][$context_key]; }
        }
        $context_complete = !empty($spec['context']['program']) && !empty($spec['context']['cohort']);
        $scope = $context_complete ? implode(' / ', $context_parts)
            : 'All Manager responses - program/year unavailable';
        $html_rows = '';
        $last_scale = null;
        foreach ($spec['questions'] as $question) {
            $scale_key = json_encode(array_column($question['categories'], 'label'));
            if ($scale_key !== $last_scale) {
                $html_rows .= '<tr><td colspan="3" class="manager-ux-legend-cell">'
                    . $manager_legend($question['categories']) . '</td></tr>';
                $last_scale = $scale_key;
            }
            $html_rows .= $manager_row($question);
        }
        if ($html_rows === '') {
            $html_rows = '<tr><td colspan="3">No Manager responses are available.</td></tr>';
        }
        $summary = 'Managers were asked the extent to which they agreed with the following statements, after their employee had completed Tasmanian Leaders’ leadership development.';
        // Custom summary remains supported; it never replaces the scope notice.
        if (trim($manager_summary_text) !== '') { $summary = $manager_summary_text; }
        $manager_pages_html .= '<div class="page content-page manager-page manager-ux-page'
            . ($first ? '' : ' manager-ux-continued') . '">'
            . $brand_logo_html . '<div class="cover-rule"></div>'
            . '<div class="manager-ux-scope">' . esc_html($scope) . '</div>'
            . ($first ? '<h2 class="manager-ux-title">Manager feedback</h2>'
                . '<div class="manager-ux-summary" style="background:' . esc_attr($manager_summary_colour) . ';color:' . esc_attr($manager_text_colour($manager_summary_colour)) . ';">' . nl2br(esc_html($summary)) . '</div>' : '')
            . '<h3 class="manager-ux-heading">Statements</h3>'
            . '<p class="manager-ux-description">All numbers are percentages (%).</p>'
            . '<table class="manager-ux-table"><colgroup><col style="width:36%"><col style="width:48%"><col style="width:16%"></colgroup><tbody>'
            . '<tr class="manager-ux-widths"><td style="width:36%;padding:0;height:0;"></td><td style="width:48%;padding:0;height:0;"></td><td style="width:16%;padding:0;height:0;"></td></tr>' . $html_rows . '</tbody></table>'
            . '<div class="mock-page-footer">Page ' . (6 + $page_index) . ' of ' . (int) $total_pages . '</div>'
            . '<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div></div>';
    }
}

$tle_pdf_font_css = '';
$tle_pdf_font_files = [
    ['Mona Sans', 'normal', 'normal', 'MonaSans-Regular.ttf'],
    ['Mona Sans', 'bold', 'normal', 'MonaSans-Bold.ttf'],
    ['Mona Sans', 'normal', 'italic', 'MonaSans-Italic.ttf'],
    ['Mona Sans', 'bold', 'italic', 'MonaSans-BoldItalic.ttf'],
    ['Playfair Display', 'normal', 'normal', 'PlayfairDisplay-Regular.ttf'],
];
foreach ($tle_pdf_font_files as $tle_font) {
    $tle_path = dirname(__DIR__) . '/assets/fonts/' . $tle_font[3];
    if (!is_readable($tle_path)) {
        wp_die('PDF font asset missing: ' . esc_html($tle_font[3]));
    }
    $tle_pdf_font_css .= '@font-face { font-family: "' . $tle_font[0]
        . '"; font-weight: ' . $tle_font[1]
        . '; font-style: ' . $tle_font[2]
        . '; src: url("data:font/ttf;base64,'
        . base64_encode(file_get_contents($tle_path))
        . '") format("truetype"); }' . "\n";
}
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
' . $tle_pdf_font_css . '

    @page {
        margin: 0;
    }

    body {
        margin: 0;
        font-family: DejaVu Sans, sans-serif;
        color: #17211d;
    }

    .page {
    position: relative;
    min-height: 900px;
    padding: 65px 70px;
    }

    .cover {
        background: #ffffff;
        page-break-after: always;
        padding: 45px 70px 42px;
    }

    .brand {
        margin-bottom: 58px;
    }

    .brand img {
        display: block;
        width: 205px;
        height: auto;
    }

    .cover-rule {
        border-top: 3px solid #222222;
        margin-bottom: 34px;
    }

    .cover-program {
        margin-bottom: 14px;
        color: #111111;
        font-size: 17px;
        font-weight: bold;
    }

    .cover-title {
        max-width: 600px;
        margin: 0;
        color: #000000;
        font-size: 42px;
        font-weight: bold;
        line-height: 1.15;
    }

    .cover-year {
        display: inline-block;
        margin-top: 24px;
        padding: 9px 22px;
        border-radius: 22px;
        background: ' . esc_attr($report_colour) . ';
        color: ' . esc_attr($recommendation_text_colour) . ';
        font-size: 17px;
        font-weight: bold;
    }

    .cover-image-wrap {
        height: 350px;
        margin: 24px -70px 0;
        overflow: hidden;
    }

    .cover-image {
        width: 100%;
    }

    .cover-image-placeholder {
        height: 305px;
        margin: 24px -70px 0;
        padding-top: 45px;
        background: #f3f3f3;
        color: #777777;
        font-size: 15px;
        text-align: center;
    }

    .cover-purpose {
        margin-top: 45px;
        color: #222222;
        font-size: 14px;
        line-height: 1.55;
    }

    .cover-bottom {
        position: absolute;
        right: 70px;
        bottom: 45px;
        left: 70px;
        padding-top: 10px;
        border-top: 3px solid #222222;
        color: #333333;
        font-size: 10px;
        text-align: right;
    }

        .content-page {
        background: #ffffff;
        padding: 65px 70px;
    }

    .page-brand {
        color: #234b3b;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 70px;
    }

    .section-label {
        color: #646970;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .section-title {
        color: #234b3b;
        font-size: 46px;
        line-height: 1;
        margin: 0 0 28px 0;
    }

    .section-intro {
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 45px;
        max-width: 620px;
    }

    .context-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    }

    .context-box {
    background: #f3f5f4;
    border-left: 5px solid #234b3b;
    padding: 18px;
    vertical-align: top;
    width: 45%;
    }

    .context-spacer {
    width: 4%;
    }

    .context-number {
        color: #234b3b;
        font-size: 48px;
        font-weight: bold;
        line-height: 1;
        margin-bottom: 10px;
    }

    .context-name {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .context-description {
        color: #50575e;
        font-size: 12px;
        line-height: 1.5;
    }

    .context-note {
        margin-top: 45px;
        padding-top: 20px;
        border-top: 1px solid #d9dddb;
        color: #50575e;
        font-size: 13px;
        line-height: 1.6;
    }

    .overview-title {
        margin: 0 0 12px;
        color: #111111;
        font-size: 36px;
        line-height: 1;
    }

    /* Hide redundant legends for single-stage reports. */
    .overview-legend--hidden {
        display: none;
    }

    .overview-legend {
        margin-bottom: 12px;
        color: #444444;
        font-size: 11px;
    }

    .legend-item {
        display: inline-block;
        margin-right: 18px;
    }

    .legend-swatch {
        display: inline-block;
        width: 18px;
        height: 3px;
        margin-right: 5px;
        vertical-align: middle;
    }

    .overview-chart {
        width: 100%;
        margin-bottom: 14px;
    }

    .overview-chart img {
        width: 100%;
    }

    /* Explanation of what the Overview graph represents for participants. */
    .overview-explanation {
        margin: 0 2px 12px;
        color: #343a40;
        font-size: 11px;
        line-height: 1.35;
        page-break-inside: avoid;
    }
    .overview-subheading {
        margin: 18px 0 10px;
        color: #111111;
        font-size: 21px;
    }

    .overview-highlights-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin: -8px 0 0 -8px;
    }

    .overview-highlight-card {
        width: 50%;
        height: 92px;
        padding: 14px 16px;
        vertical-align: top;
        background: ' . esc_attr($report_colour_light) . ';
    }

    .overview-highlight-percent {
        margin-bottom: 2px;
        color: #111111;
        font-size: 25px;
        font-weight: bold;
        line-height: 1;
    }

    .overview-highlight-name {
        margin-bottom: 5px;
        color: #111111;
        font-size: 11px;
        font-weight: bold;
    }

    .overview-highlight-description {
        color: #222222;
        font-size: 10px;
        line-height: 1.3;
    }

    .mock-page-footer {
        position: absolute;
        right: 70px;
        bottom: 45px;
        left: 70px;
        padding-top: 9px;
        border-top: 3px solid #222222;
        color: #333333;
        font-size: 9px;
        text-align: right;
    }
    /* Phase 7E: compact, participant-friendly domain introduction. */
    .tle-domain-intro {
        margin: 0 0 4px;
        color: #434b50;
        font-size: 9px;
        line-height: 1.25;
        page-break-inside: avoid;
    }
    .section-report-title {
        margin: 0 0 4px;
        color: #111111;
        font-size: 32px;
        line-height: 1;
    }

    .section-chart,
    .movers-chart {
        width: 100%;
    }

    .section-chart img,
    .movers-chart img {
        width: 100%;
    }

    .section-movers-title {
        margin: 4px 0 4px;
        color: #111111;
        font-size: 20px;
    }

    .section-summary-table {
        width: 100%;
        margin-top: 8px;
        border-collapse: separate;
        border-spacing: 8px 0;
        margin-left: -8px;
    }

    .growth-box {
        width: 50%;
        padding: 15px;
        vertical-align: top;
    }

    .growth-box {
        background: ' . esc_attr($report_colour_light) . ';
        color: #111111;
    }


    .box-heading {
        margin-bottom: 12px;
        font-size: 19px;
        font-weight: bold;
    }

    .growth-line {
        margin-bottom: 11px;
        font-size: 10px;
        line-height: 1.35;
    }
    /* Section report single-page fit */

    .insight-page {
        padding-top: 46px;
        padding-bottom: 42px;
    }

    .insight-page .brand {
        margin-bottom: 36px;
        font-size: 32px;
    }

    .insight-page .cover-rule {
        margin-bottom: 23px;
    }

    .section-report-title {
        margin-bottom: 5px;
        font-size: 32px;
    }

    .section-chart {
        margin-bottom: 8px;
        overflow: visible;
    }

    .section-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .section-movers-title {
        margin: 8px 0 4px;
        font-size: 20px;
    }

    .movers-chart {
        margin-bottom: 6px;
        overflow: visible;
    }

    .movers-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .comparison-context-box {
        width: 86%;
        margin: 2px auto 6px;
        padding: 9px 12px;
        box-sizing: border-box;
        background: ' . esc_attr($report_colour_light) . ';
        color: #111111;
        font-size: 9px;
        line-height: 1.35;
        page-break-inside: avoid;
    }

    .section-summary-table {
        margin-top: 8px;
        page-break-inside: avoid;
    }

    .growth-box {
        height: 130px;
        padding: 14px;
        vertical-align: top;
    }

    .box-heading {
        margin-bottom: 9px;
        font-size: 18px;
    }

    .growth-line {
        margin-bottom: 8px;
        font-size: 10px;
        line-height: 1.35;
    }
    .page-number {
        position: absolute;
        left: 70px;
        bottom: 50px;
        font-size: 18px;
        font-weight: bold;
        color: #234b3b;
    }

    .insight-intro {
    font-size: 14px;
    line-height: 1.6;
    color: #50575e;
    margin-bottom: 32px;
    }

    .metric-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    }

    .metric-table th {
    background: #234b3b;
    color: #ffffff;
    font-size: 11px;
    text-align: left;
    padding: 12px 10px;
    }

    .metric-table td {
    border-bottom: 1px solid #d9dddb;
    padding: 14px 10px;
    font-size: 12px;
    }

    .metric-name {
    font-weight: bold;
    color: #17211d;
    }

    .change-positive {
    color: #234b3b;
    font-weight: bold;
    }

    .score-track {
    width: 100%;
    height: 8px;
    background: #e8ebe9;
    margin-top: 6px;
    }

    .score-fill {
    height: 8px;
    background: #234b3b;
    }

    .insight-page {
    page-break-before: always;
    }


    /* Final Sprint 2 Insight PDF page */

    .final-insight-page .section-chart {
        margin: 4px 0 4px;
    }

    .final-insight-page .section-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-insight-page .section-movers-title {
        margin: 2px 0 2px;
        font-size: 19px;
    }

    .final-insight-page .movers-chart {
        margin-bottom: 4px;
    }

    .final-insight-page .movers-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-insight-page .section-summary-table {
        width: 100%;
        margin: 6px 0 0;
        border-collapse: separate;
        border-spacing: 8px 0;
        page-break-inside: avoid;
    }

    .growth-summary-box,
    .participant-quote-box {
        padding: 12px 14px;
        vertical-align: top;
    }

    .growth-summary-box {
        background: ' . esc_attr($report_colour_light) . ';
        color: #111111;
    }

    .growth-summary-box--with-quote {
        width: 66%;
    }

    .growth-summary-box--full {
        width: 100%;
    }

    .participant-quote-box {
        width: 34%;
    }

    .growth-summary-line {
        margin-bottom: 6px;
        font-size: 8.5px;
        line-height: 1.25;
    }

    .growth-summary-line strong {
        display: block;
        margin-bottom: 1px;
        font-size: 9px;
    }

    .growth-summary-line strong span {
        margin-left: 4px;
        font-weight: normal;
    }

    .participant-quote-text {
        line-height: 1.45;
        font-style: italic;
    }

    /* Keep final Insight report on one A4 page. */

    .final-insight-page {
        padding-top: 38px;
        padding-bottom: 40px;
    }

    .final-insight-page .brand {
        margin-bottom: 18px;
        font-size: 30px;
    }

    .final-insight-page .cover-rule {
        margin-bottom: 13px;
    }

    .final-insight-page .section-report-title {
        margin-bottom: 0;
        font-size: 29px;
    }

    .final-insight-page .section-chart {
        width: 86%;
        margin: 0 auto 0;
    }

    .final-insight-page .section-movers-title {
        margin: 0 0 0;
        font-size: 18px;
    }

    .final-insight-page .movers-chart {
        width: 86%;
        margin: 0 auto 0;
    }

    .final-insight-page .comparison-context-box {
        margin-bottom: 4px;
    }

    .final-insight-page .section-summary-table {
        margin-top: 2px;
    }

    .final-insight-page .growth-summary-box,
    .final-insight-page .participant-quote-box {
        padding: 9px 12px;
    }

    .final-insight-page .box-heading {
        margin-bottom: 5px;
        font-size: 16px;
    }

    .final-insight-page .growth-summary-line {
        margin-bottom: 3px;
        font-size: 8px;
        line-height: 1.18;
    }

    .final-insight-page .growth-summary-line strong {
        margin-bottom: 0;
        font-size: 8.5px;
    }

    /* Final Sprint 2 Influence PDF page */

    .final-influence-page {
        padding-top: 38px;
        padding-bottom: 40px;
    }

    .final-influence-page .brand {
        margin-bottom: 18px;
        font-size: 30px;
    }

    .final-influence-page .cover-rule {
        margin-bottom: 13px;
    }

    .final-influence-page .section-report-title {
        margin-bottom: 0;
        font-size: 29px;
    }

    .final-influence-page .section-chart {
        width: 86%;
        margin: 0 auto;
    }

    .final-influence-page .section-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-influence-page .section-movers-title {
        margin: 0;
        font-size: 18px;
    }

    .final-influence-page .movers-chart {
        width: 86%;
        margin: 0 auto;
    }

    .final-influence-page .movers-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-influence-page .comparison-context-box {
        margin-bottom: 4px;
    }

    .final-influence-page .section-summary-table {
        margin-top: 2px;
    }

    .final-influence-page .growth-summary-box,
    .final-influence-page .participant-quote-box {
        padding: 9px 12px;
    }

    .final-influence-page .box-heading {
        margin-bottom: 5px;
        font-size: 16px;
    }

    .final-influence-page .growth-summary-line {
        margin-bottom: 3px;
        font-size: 8px;
        line-height: 1.18;
    }

    .final-influence-page .growth-summary-line strong {
        margin-bottom: 0;
        font-size: 8.5px;
    }

    /* Final Sprint 2 Impact PDF page */

    .final-impact-page {
        padding-top: 38px;
        padding-bottom: 40px;
    }

    .final-impact-page .brand {
        margin-bottom: 18px;
        font-size: 30px;
    }

    .final-impact-page .cover-rule {
        margin-bottom: 13px;
    }

    .final-impact-page .section-report-title {
        margin-bottom: 0;
        font-size: 29px;
    }

    .final-impact-page .section-chart {
        width: 86%;
        margin: 0 auto;
    }

    .final-impact-page .section-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-impact-page .section-movers-title {
        margin: 0;
        font-size: 18px;
    }

    .final-impact-page .movers-chart {
        width: 86%;
        margin: 0 auto;
    }

    .final-impact-page .movers-chart img {
        display: block;
        width: 100%;
        height: auto;
    }

    .final-impact-page .comparison-context-box {
        margin-bottom: 4px;
    }

    .final-impact-page .section-summary-table {
        margin-top: 2px;
    }

    .final-impact-page .growth-summary-box,
    .final-impact-page .participant-quote-box {
        padding: 9px 12px;
    }

    .final-impact-page .box-heading {
        margin-bottom: 5px;
        font-size: 16px;
    }

    .final-impact-page .growth-summary-line {
        margin-bottom: 4px;
        font-size: 8.5px;
        line-height: 1.2;
    }

    .final-impact-page .growth-summary-line strong {
        margin-bottom: 0;
        font-size: 9px;
    }

    /*
     * Single-stage reports have less vertical content than growth reports.
     * Scale the real charts and summary up so the page feels intentional
     * without changing multi-stage layouts.
     */
    .single-stage-section-page .section-chart {
        width: 96%;
        margin: 6px auto 8px;
    }

    .single-stage-section-page .section-movers-title {
        margin: 10px 0 5px;
        font-size: 20px;
    }

    .single-stage-section-page .movers-chart {
        width: 96%;
        margin: 0 auto 8px;
    }

    .single-stage-section-page .section-summary-table {
        margin-top: 10px;
    }

    .single-stage-section-page .growth-summary-box,
    .single-stage-section-page .participant-quote-box {
        padding: 14px 16px;
    }

    .single-stage-section-page .box-heading {
        margin-bottom: 8px;
        font-size: 18px;
    }

    .single-stage-section-page .growth-summary-line {
        margin-bottom: 5px;
        font-size: 9px;
        line-height: 1.25;
    }

    .single-stage-section-page .growth-summary-line strong {
        margin-bottom: 1px;
        font-size: 9.5px;
    }

    /* Prevent single-stage Insight content overlapping the footer. */
    .single-stage-section-page.final-insight-page .section-chart {
        width: 86%;
        margin: 0 auto;
    }

    .single-stage-section-page.final-insight-page .section-movers-title {
        margin: 0;
        font-size: 18px;
    }

    .single-stage-section-page.final-insight-page .movers-chart {
        width: 86%;
        margin: 0 auto;
    }

    .single-stage-section-page.final-insight-page .section-summary-table {
        margin-top: 2px;
    }

    /* Final participant summary layout */
    .final-insight-page .section-summary-table,
    .final-influence-page .section-summary-table,
    .final-impact-page .section-summary-table {
        width: 100%;
        margin-left: -12px;
        border-spacing: 12px 0;
        table-layout: fixed;
    }

    .section-summary-heading-row td {
        padding: 0 0 7px;
        color: #111111;
        background: transparent;
        font-size: 16px;
        font-weight: bold;
        line-height: 1.2;
        vertical-align: bottom;
    }

    .final-insight-page .growth-summary-box,
    .final-insight-page .participant-quote-box,
    .final-influence-page .growth-summary-box,
    .final-influence-page .participant-quote-box,
    .final-impact-page .growth-summary-box,
    .final-impact-page .participant-quote-box {
        padding: 12px 14px;
        vertical-align: top;
    }

    .participant-quote-text {
        font-weight: bold;
        font-style: normal;
        line-height: 1.28;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Centre automatically scaled quotes in their panels. */
    .final-insight-page .participant-quote-box,
    .final-influence-page .participant-quote-box,
    .final-impact-page .participant-quote-box {
        vertical-align: middle;
    }

    .participant-quote-text {
        line-height: 1.15;
        font-weight: bold;
        font-style: normal;
        word-wrap: break-word;
    }

    /* Comparison quotes share the existing two-column summary table. */
    /*
     * Comparison-only fixed-page fit. Dompdf otherwise pushes the atomic
     * two-column table onto an extra PDF page when there are 3 programs.
     * Scope this to comparisons; preserve legacy single-program layout.
     */
    .final-insight-page .section-summary-table.tle-comparison-side-by-side,
    .final-influence-page .section-summary-table.tle-comparison-side-by-side,
    .final-impact-page .section-summary-table.tle-comparison-side-by-side {
        position: absolute !important;
        top: 730px !important;
        left: 70px !important;
        width: 82% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 7px 0 !important;
        table-layout: fixed !important;
        page-break-inside: avoid;
    }

    .tle-comparison-side-by-side .section-summary-heading-row td {
        padding: 0 0 3px !important;
        font-size: 16px !important;
        line-height: 1.1 !important;
    }

    .tle-comparison-side-by-side .growth-summary-box,
    .tle-comparison-side-by-side .participant-quote-box {
        padding: 7px 9px !important;
        height: 250px !important;
        vertical-align: middle !important;
    }

    .tle-comparison-side-by-side .growth-summary-box--with-quote {
        width: 67% !important;
    }
    .tle-comparison-side-by-side .participant-quote-box {
        width: 33% !important;
    }

    .tle-comparison-side-by-side .tle-growth-program-summaries {
        font-size: 9px !important;
        line-height: 1.10 !important;
    }
    .tle-comparison-side-by-side .tle-growth-program {
        margin: 0 0 3px !important;
    }
    .tle-comparison-side-by-side .tle-growth-program-name {
        margin: 0 0 1px !important;
    }
    .tle-comparison-side-by-side .tle-growth-summary-line {
        margin: 0 !important;
    }

    /*
     * Comparison quotes use the same per-quote inline font-size calculated
     * by fit_participant_quote() as the no-comparison report.
     * Do not force a tiny size here.
     */
    .tle-comparison-side-by-side .participant-quote-text {
        overflow-wrap: break-word;
    }

    .comparison-growth-subtitle {
        margin: 0 0 6px;
        font-size: 10px;
        color: #555555;
        line-height: 1.25;
    }
    /* Sprint 3 Manager pages */

    .manager-page {
        page-break-before: always;
        page-break-inside: avoid;
        padding-top: 38px;
        padding-bottom: 42px;
    }

    .manager-page .section-report-title {
        margin-bottom: 16px;
    }

    .manager-summary-box {
        margin: 0 0 18px;
        padding: 14px 16px;
        background: #f3f3f3;
        color: #111111;
    }

    .manager-summary-text {
        font-size: 10px;
        line-height: 1.45;
    }

    .manager-statements-heading {
        margin: 0 0 8px;
        color: #111111;
        font-size: 18px;
    }

    .manager-statements-heading--continued {
        margin-top: 10px;
    }

    .manager-statements-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 7px;
        table-layout: fixed;
    }

    .manager-statements-table th {
        padding: 0 10px 4px;
        color: #555555;
        font-size: 8px;
        text-align: left;
        text-transform: uppercase;
    }

    .manager-statements-table th:last-child {
        width: 22%;
        text-align: center;
    }

    .manager-statement-cell {
        padding: 9px 12px;
        background: #f3f3f3;
        color: #111111;
        font-size: 9px;
        line-height: 1.3;
        vertical-align: middle;
    }

    .manager-rating-cell {
        width: 22%;
        padding: 9px 8px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    /* Single-stage Overview comparison summary. */

    .overview-comparison-glance {
        margin-top: 25px;
        padding: 17px 19px 18px;
        border: 1px solid #d9dddb;
        background: #f7f8f7;
    }

    .overview-comparison-heading {
        margin: 0 0 3px;
        color: #111111;
        font-size: 16px;
        font-weight: bold;
    }

    .overview-comparison-stage {
        margin-bottom: 11px;
        color: #6b706d;
        font-size: 8px;
        font-weight: bold;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .overview-comparison-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .overview-comparison-table thead th {
        padding: 0 7px 7px;
        border-bottom: 1px solid #bfc5c1;
        color: #555555;
        font-size: 8px;
        font-weight: bold;
        text-align: center;
    }

    .overview-comparison-table thead th:first-child {
        width: 43%;
        padding-left: 0;
        text-align: left;
    }

    .overview-comparison-table tbody th,
    .overview-comparison-table tbody td {
        padding: 10px 7px;
        border-bottom: 1px solid #e2e5e3;
        vertical-align: middle;
    }

    .overview-comparison-table tbody tr:last-child th,
    .overview-comparison-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .overview-comparison-table tbody th {
        padding-left: 0;
        color: #222222;
        font-size: 9px;
        font-weight: normal;
        text-align: left;
    }

    .overview-comparison-primary th {
        font-weight: bold;
    }

    .overview-comparison-swatch {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 6px;
        vertical-align: middle;
    }

    .overview-comparison-metric {
        text-align: center;
    }

    .overview-comparison-metric strong {
        display: block;
        color: #111111;
        font-size: 15px;
        line-height: 1;
    }

    .overview-comparison-metric span {
        display: block;
        margin-top: 3px;
        color: #777777;
        font-size: 7px;
    }

    /* Sprint 3 client branding refinement */

    /* Official logo at 50% of the cover-page size. */
    .content-page .brand {
        margin-bottom: 12px;
    }

    .content-page .brand img {
        width: 103px;
        height: auto;
    }

    /* Fine divider beneath the page branding. */
    .content-page .cover-rule {
        border-top-width: 1px;
        margin-bottom: 13px;
    }

    /* Lighter, more compact page footer. */
    .mock-page-footer {
        bottom: 38px;
        padding-top: 6px;
        border-top-width: 1px;
        font-size: 8px;
    }

    /* Cover footer uses the same fine rule. */
    .cover-bottom {
        padding-top: 6px;
        border-top-width: 1px;
        font-size: 9px;
    }

    /* Cohort year should read as text, not a button/pill. */
    .cover-year {
        display: block;
        width: auto;
        margin-top: 18px;
        padding: 0;
        border-radius: 0;
        background: transparent;
        color: #222222;
        font-size: 17px;
        font-weight: bold;
    }

    /* Sprint 3 PDF typography refinement */

    /*
     * Temporary display-font substitute.
     * Replace DejaVu Serif with approved Playfair/Larken font
     * once the font assets are added to the project.
     */
    .cover-title,
    .overview-title,
    .section-report-title {
        font-family: DejaVu Serif, serif;
        font-weight: normal;
    }

    .cover-title {
        font-size: 40px;
        line-height: 1.12;
    }

    .overview-title {
        font-size: 34px;
        line-height: 1.05;
    }

    .final-insight-page .section-report-title,
    .final-influence-page .section-report-title,
    .final-impact-page .section-report-title,
    .manager-page .section-report-title {
        font-size: 28px;
        line-height: 1.05;
    }

        /* Manager pages only: three-column UX layout, backed by service distributions. */
    .manager-ux-page {
        min-height: 0;
        height: 990px;
        padding: 38px 70px 42px;
        page-break-before: always;
        page-break-inside: avoid;
        font-family: DejaVu Sans, sans-serif;
    }
    .manager-ux-page .brand { margin-bottom: 12px; }
    .manager-ux-scope { font-size: 10px; font-weight: bold; margin: 15px 0 10px; color: #222; }
    .manager-ux-title { font-family: DejaVu Sans, sans-serif; font-size: 29px; margin: 0 0 20px; line-height: 1.15; }
    .manager-ux-summary { padding: 10px 12px; font-size: 10px; line-height: 1.35; margin-bottom: 15px; }
    .manager-ux-heading { font-size: 17px; margin: 0 0 10px; line-height: 1.2; }
    .manager-ux-description { margin: 0 0 5px; font-size: 10px; }
    .manager-ux-source { font-size: 7px; color: #555; margin-bottom: 9px; }
    .manager-ux-table { width: 100%; table-layout: fixed; border-collapse: collapse; border-spacing: 0; }
    .manager-ux-table td { vertical-align: middle; }
    .manager-ux-legend-cell { padding: 5px 0 6px; }
    .manager-ux-legend-image { display: block; width: 100%; height: 42px; }
    .manager-ux-rounding-note { margin: 0 0 5px; font-size: 8px; line-height: 1.3; color: #444; }
    .manager-ux-legend { text-align: center; font-size: 7px; line-height: 1.8; }
    .manager-ux-legend-item { display: inline-block; white-space: nowrap; margin-right: 9px; }
    .manager-ux-swatch { display: inline-block; width: 14px; height: 8px; margin-right: 4px; vertical-align: middle; }
    .manager-ux-row { page-break-inside: avoid; }
    .manager-ux-statement { width: 36%; padding: 3px 12px 3px 0; font-size: 10px; line-height: 1.35; background: white; color: #111; }
    .manager-ux-chart-cell { width: 48%; padding: 3px 0; border-top: 1px solid #d8d8e0; border-bottom: 1px solid #d8d8e0; border-left: 1px solid #888; }
    .manager-ux-bar { display: block; width: 100%; height: 54px; position: relative; top: 5px; }
    .manager-ux-badge-cell { width: 16%; padding: 3px 0 3px 12px; }
    .manager-ux-badge { text-align: center; height: 46px; }
    .manager-ux-number { display: block; font-size: 22px; font-weight: bold; padding-top: 2px; line-height: 24px; }
    .manager-ux-badge-label { display: block; font-size: 10px; font-weight: bold; line-height: 13px; }
    .manager-ux-badge-empty { background: #e8ebe9; color: #555; }
    .manager-ux-badge-empty .manager-ux-badge-label { font-size: 8px; }
    .manager-ux-empty { height: 46px; line-height: 46px; text-align: center; font-size: 9px; color: #555; background: #f3f3f3; }
    .manager-ux-continued .manager-ux-scope { margin-top: 0; margin-bottom: 16px; }

    .manager-ux-badge-image { display: block; width: 100%; height: 46px; }


    /* Client-approved PDF font specification and beneath-footer program labels. */
    body, .manager-ux-page, .manager-ux-title, .overview-title,
    .section-report-title, .section-title {
        font-family: "Mona Sans", DejaVu Sans, sans-serif;
    }
    .cover-title, .tle-footer-program {
        font-family: "Playfair Display", DejaVu Serif, serif;
        font-weight: normal;
    }
    .section-label, .manager-ux-scope { display: none !important; }
    /* Program/year shares the same baseline as the page count below the rule. */
    .tle-footer-program {
        position: absolute;
        bottom: 37px;
        left: 70px;
        right: 70px;
        color: #333333;
        text-align: left;
        font-size: 10px;
        line-height: 1.2;
    }

    /* Cover page-number footer sits 7px higher than content-page footers. */
    .cover .tle-footer-program {
        bottom: 44px;
    }
    </style>
</head>

<body>

<div class="page cover">

    ' . $brand_logo_html . '

    <div class="cover-rule"></div>

    <div class="cover-program">
        ' . esc_html($program) . '
    </div>

    <h1 class="cover-title">
        ' . esc_html($report_title) . '
    </h1>

    <div class="cover-year">
        ' . esc_html($cohort) . '
    </div>

    ' . $cover_media_html . '

    <div class="cover-purpose">
        ' . nl2br(esc_html($purpose_text)) . '
    </div>

    <div class="cover-bottom">
        Page 1 of ' . esc_html($total_pages) . '
    </div>
<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div>

</div>

<div class="page content-page">

    ' . $brand_logo_html . '

    <div class="cover-rule"></div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="overview-title">
        Overview
    </h2>

    <div class="overview-legend' . ($is_single_stage_report ? ' overview-legend--hidden' : '') . '">

        <span class="legend-item">
            <span
                class="legend-swatch"
                style="background: ' . esc_attr($report_colour) . ';"
            ></span>
            Insight
        </span>

        <span class="legend-item">
            <span
                class="legend-swatch"
                style="background: #111111;"
            ></span>
            Influence
        </span>

        <span class="legend-item">
            <span
                class="legend-swatch"
                style="background: #777777;"
            ></span>
            Impact
        </span>

    </div>

    <div class="overview-chart">
        <img src="' . esc_attr($overview_chart_data) . '" alt="">
    </div>

    <p class="overview-explanation">' . nl2br(esc_html($overview_intro_text)) . '</p>

    ' . $overview_highlights_section_html . '

    <div class="mock-page-footer">
        Page 2 of ' . esc_html($total_pages) . '
    </div>
<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div>

</div>
<div class="page content-page insight-page final-insight-page' . ($is_single_stage_report ? ' single-stage-section-page' : '') . '">

    ' . $brand_logo_html . '

    <div class="cover-rule"></div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-report-title">
        Insight
    </h2>

    ' . (trim($insight_domain_intro) === '' ? '' : '<p class="tle-domain-intro">' . esc_html($insight_domain_intro) . '</p>') . '

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Insight']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Insight'] . '

    <table class="section-summary-table' . ($has_comparisons ? ' tle-comparison-side-by-side' : '') . '">
        <tr class="section-summary-heading-row">
            <td>
                ' . esc_html($section_summary_heading) . '
            </td>
            ' . (trim($insight_quote_html) !== '' ? '<td>Participant Quote</td>' : '') . '
        </tr>
        <tr>
            <td class="' . esc_attr($insight_growth_summary_class) . '">
                ' . $insight_growth_summary_html . '
            </td>

            ' . $insight_quote_html . '

        </tr>
    </table>


    <div class="mock-page-footer">
        Page 3 of ' . esc_html($total_pages) . '
    </div>
<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div>

</div>
<div class="page content-page insight-page final-influence-page' . ($is_single_stage_report ? ' single-stage-section-page' : '') . '">

    ' . $brand_logo_html . '

    <div class="cover-rule"></div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-report-title">
        Influence
    </h2>

    ' . (trim($influence_domain_intro) === '' ? '' : '<p class="tle-domain-intro">' . esc_html($influence_domain_intro) . '</p>') . '

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Influence']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Influence'] . '

    <table class="section-summary-table' . ($has_comparisons ? ' tle-comparison-side-by-side' : '') . '">
        <tr class="section-summary-heading-row">
            <td>
                ' . esc_html($section_summary_heading) . '
            </td>
            ' . (trim($influence_quote_html) !== '' ? '<td>Participant Quote</td>' : '') . '
        </tr>
        <tr>
            <td class="' . esc_attr($influence_growth_summary_class) . '">
                ' . $influence_growth_summary_html . '
            </td>

            ' . $influence_quote_html . '

        </tr>
    </table>


    <div class="mock-page-footer">
        Page 4 of ' . esc_html($total_pages) . '
    </div>
<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div>

</div>
<div class="page content-page insight-page final-impact-page' . ($is_single_stage_report ? ' single-stage-section-page' : '') . '">

    ' . $brand_logo_html . '

    <div class="cover-rule"></div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-report-title">
        Impact
    </h2>

    ' . (trim($impact_domain_intro) === '' ? '' : '<p class="tle-domain-intro">' . esc_html($impact_domain_intro) . '</p>') . '

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Impact']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Impact'] . '

    <table class="section-summary-table' . ($has_comparisons ? ' tle-comparison-side-by-side' : '') . '">
        <tr class="section-summary-heading-row">
            <td>
                ' . esc_html($section_summary_heading) . '
            </td>
            ' . (trim($impact_quote_html) !== '' ? '<td>Participant Quote</td>' : '') . '
        </tr>
        <tr>
            <td class="' . esc_attr($impact_growth_summary_class) . '">
                ' . $impact_growth_summary_html . '
            </td>

            ' . $impact_quote_html . '

        </tr>
    </table>


    <div class="mock-page-footer">
        Page 5 of ' . esc_html($total_pages) . '
    </div>
<div class="tle-footer-program">' . esc_html($program) . ' &#183; ' . esc_html($cohort) . '</div>

</div>
' . $manager_pages_html . '

</body>
</html>
';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('evaluation-report.pdf', ['Attachment' => true]);

    exit;
}
add_action('admin_post_tle_export_pdf', 'tle_export_pdf');