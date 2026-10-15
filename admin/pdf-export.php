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

    $comparison_programs = isset($_POST['comparison_programs'])
        ? array_values(
            array_filter(
                array_map(
                    'sanitize_text_field',
                    (array) wp_unslash($_POST['comparison_programs'])
                )
            )
        )
        : [];

    $comparison_evaluation_point = isset($_POST['comparison_evaluation_point'])
        ? sanitize_key(
            wp_unslash($_POST['comparison_evaluation_point'])
        )
        : '';

    $allowed_comparison_points = [
        'pre-program',
        'completion',
        'three-month-delay',
    ];

    if (
        !in_array(
            $comparison_evaluation_point,
            $allowed_comparison_points,
            true
        )
    ) {
        $comparison_evaluation_point = '';
    }

    $comparison_evaluation_labels = [
        'pre-program'       => 'Pre-program',
        'completion'        => 'Post-program',
        'three-month-delay' => 'Delayed',
    ];

    $comparison_evaluation_label =
        $comparison_evaluation_point &&
        isset(
            $comparison_evaluation_labels[
                $comparison_evaluation_point
            ]
        )
            ? $comparison_evaluation_labels[
                $comparison_evaluation_point
            ]
            : '';

    $has_comparisons =
        !empty($comparison_programs) &&
        $comparison_evaluation_point !== '';

    $manager_summary_text = isset($_POST['manager_summary_text'])
        ? sanitize_textarea_field(
            wp_unslash($_POST['manager_summary_text'])
        )
        : '';
    $report_title_input = isset($_POST['report_title'])
        ? sanitize_text_field(wp_unslash($_POST['report_title']))
        : '';

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

    $insight_quote = isset($_POST['insight_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['insight_quote']))
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

    $influence_quote = isset($_POST['influence_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['influence_quote']))
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

    $impact_quote = isset($_POST['impact_quote'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_quote']))
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
    $comparison_primary_stage_map = [
        'pre-program'       => 'pre',
        'completion'        => 'completion',
        'three-month-delay' => 'delay',
    ];

    if (
        count($primary_available_stages) === 1 &&
        empty($comparison_programs)
    ) {
        wp_die(
            'This program has one available evaluation point. Select at least one comparison program to provide a basis for growth.'
        );
    }

    if (!empty($comparison_programs)) {
        if (!$has_comparisons) {
            wp_die(
                'Select one evaluation point for the comparison before exporting.'
            );
        }

        $comparison_primary_stage_key =
            $comparison_primary_stage_map[
                $comparison_evaluation_point
            ] ?? '';

        if (
            $comparison_primary_stage_key === '' ||
            !isset(
                $primary_available_stages[
                    $comparison_primary_stage_key
                ]
            )
        ) {
            wp_die(
                'The selected comparison evaluation point is not available for the primary program.'
            );
        }
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

$highlight_cards = [];

foreach ($top_highlights as $item) {
    $description = (
        isset($highlight_descriptions[$item['measure']]) &&
        trim($highlight_descriptions[$item['measure']]) !== ''
    )
        ? $highlight_descriptions[$item['measure']]
        : sprintf(
            '%s increased by %s%%.',
            $item['measure'],
            round($item['growth'])
        );

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
    floor((min($insight_percentages) - 10) / 10) * 10
);

$insight_axis_max = min(
    100,
    ceil((max($insight_percentages) + 10) / 10) * 10
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
    $report_colour,
    '#111111',
    '#666666',
    $mix_report_colour($report_colour, '#111111', 0.28),
    $mix_report_colour($report_colour, '#ffffff', 0.28),
    $mix_report_colour($report_colour, '#111111', 0.52),
    $mix_report_colour($report_colour, '#ffffff', 0.48),
];

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

$insight_quote_html = '';

if (trim($insight_quote) !== '') {
    $quote_length = strlen($insight_quote);

    if ($quote_length > 220) {
        $insight_quote_size = '9px';
    } elseif ($quote_length > 140) {
        $insight_quote_size = '10px';
    } elseif ($quote_length > 80) {
        $insight_quote_size = '11px';
    } else {
        $insight_quote_size = '13px';
    }

    $insight_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >
            <div class="box-heading">
                Participant Quote
            </div>

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
    floor((min($influence_percentages) - 10) / 10) * 10
);

$influence_axis_max = min(
    100,
    ceil((max($influence_percentages) + 10) / 10) * 10
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
    $report_colour,
    '#111111',
    '#666666',
    $mix_report_colour($report_colour, '#111111', 0.28),
    $mix_report_colour($report_colour, '#ffffff', 0.28),
    $mix_report_colour($report_colour, '#111111', 0.52),
];

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
 * Growth Summary — all six capabilities, ordered by growth.
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
    $quote_length = strlen($influence_quote);

    if ($quote_length > 220) {
        $influence_quote_size = '9px';
    } elseif ($quote_length > 140) {
        $influence_quote_size = '10px';
    } elseif ($quote_length > 80) {
        $influence_quote_size = '11px';
    } else {
        $influence_quote_size = '13px';
    }

    $influence_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >
            <div class="box-heading">
                Participant Quote
            </div>

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
    floor((min($impact_percentages) - 10) / 10) * 10
);

$impact_axis_max = min(
    100,
    ceil((max($impact_percentages) + 10) / 10) * 10
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
    $report_colour,
    '#111111',
    '#666666',
    $mix_report_colour($report_colour, '#111111', 0.35),
];

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
 * Growth Summary — every Impact capability.
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
    $quote_length = strlen($impact_quote);

    if ($quote_length > 220) {
        $impact_quote_size = '9px';
    } elseif ($quote_length > 140) {
        $impact_quote_size = '10px';
    } elseif ($quote_length > 80) {
        $impact_quote_size = '11px';
    } else {
        $impact_quote_size = '13px';
    }

    $impact_quote_html = '
        <td
            class="participant-quote-box"
            style="
                background: ' . esc_attr($report_colour) . ';
                color: ' . esc_attr($recommendation_text_colour) . ';
            "
        >
            <div class="box-heading">
                Participant Quote
            </div>

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
     * Overview bar chart.
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
        'Influence' => 275,
        'Impact' => 425,
    ];

    $overview_bars = '';

    foreach ($overview_values as $section_name => $percentage) {
        $x = $overview_x[$section_name];
        $height = $percentage * 1.45;
        $y = 190 - $height;

        $overview_bars .= '
            <rect
                x="' . $x . '"
                y="' . $y . '"
                width="70"
                height="' . $height . '"
                fill="' . esc_attr($report_colour) . '"
            />

            <text
                x="' . ($x + 35) . '"
                y="' . ($y - 7) . '"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="10"
                font-weight="600"
                fill="#111111"
            >' . esc_html(round($percentage)) . '%</text>

            <text
                x="' . ($x + 35) . '"
                y="216"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="11"
                font-weight="600"
                fill="#111111"
            >' . esc_html($section_name) . '</text>
        ';
    }

    $overview_svg = '
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="600"
        height="245"
        viewBox="0 0 600 245"
    >
        <rect width="600" height="245" fill="#ffffff"/>

        <g stroke="#dddddd" stroke-width="1">
            <line x1="70" y1="45" x2="550" y2="45"/>
            <line x1="70" y1="74" x2="550" y2="74"/>
            <line x1="70" y1="103" x2="550" y2="103"/>
            <line x1="70" y1="132" x2="550" y2="132"/>
            <line x1="70" y1="161" x2="550" y2="161"/>
            <line x1="70" y1="190" x2="550" y2="190"/>
        </g>

        <g
            fill="#555555"
            font-family="DejaVu Sans"
            font-size="10"
        >
            <text x="35" y="48">100%</text>
            <text x="42" y="77">80%</text>
            <text x="42" y="106">60%</text>
            <text x="42" y="135">40%</text>
            <text x="42" y="164">20%</text>
            <text x="49" y="193">0%</text>

            <rect
                x="448"
                y="14"
                width="12"
                height="12"
                fill="' . esc_attr($report_colour) . '"
            />

            <text x="466" y="24">
                ' . esc_html($single_stage_label) . '
            </text>
        </g>

        ' . $overview_bars . '
    </svg>
    ';

    $overview_chart_data =
        'data:image/svg+xml;base64,' .
        base64_encode($overview_svg);

    /*
     * Single-stage capability bar chart.
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
                        array_key_exists($stage_key, $measure) &&
                        $measure[$stage_key] !== null &&
                        is_numeric($measure[$stage_key]);
                }
            )
        );

        $count = max(1, count($measures));
        $plot_left = 70;
        $plot_width = 480;
        $slot_width = $plot_width / $count;
        $bar_width = min(42, $slot_width * 0.55);
        $bars = '';

        foreach ($measures as $index => $measure) {
            $percentage =
                ((float) $measure[$stage_key] / 7) * 100;

            $height = $percentage * 1.45;
            $y = 190 - $height;

            $centre =
                $plot_left +
                ($slot_width * $index) +
                ($slot_width / 2);

            $x = $centre - ($bar_width / 2);

            $label = (string) $measure['measure'];
            $label_words =
                preg_split(
                    '/\s+/',
                    trim($label)
                ) ?: [];

            $label_lines = [$label];

            if (count($label_words) > 1) {
                $best_split = 1;
                $best_difference = PHP_INT_MAX;

                for (
                    $split = 1;
                    $split < count($label_words);
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

                    if ($difference < $best_difference) {
                        $best_difference = $difference;
                        $best_split = $split;
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
            $label_start_y =
                count($label_lines) > 1
                    ? 211
                    : 216;

            foreach (
                $label_lines
                as $line_index =>
                    $label_line
            ) {
                $label_svg .= '
                    <text
                        x="' . $centre . '"
                        y="' .
                            (
                                $label_start_y +
                                ($line_index * 9)
                            ) .
                        '"
                        text-anchor="middle"
                        font-family="DejaVu Sans"
                        font-size="7"
                        fill="#333333"
                    >' .
                        esc_html($label_line) .
                    '</text>
                ';
            }

            $bars .= '
                <rect
                    x="' . $x . '"
                    y="' . $y . '"
                    width="' . $bar_width . '"
                    height="' . $height . '"
                    fill="' . esc_attr($colour) . '"
                />

                <text
                    x="' . $centre . '"
                    y="' . ($y - 5) . '"
                    text-anchor="middle"
                    font-family="DejaVu Sans"
                    font-size="8"
                    font-weight="600"
                    fill="#111111"
                >' . esc_html(round($percentage)) . '%</text>

                ' . $label_svg . '
            ';
        }

        $svg = '
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="600"
            height="240"
            viewBox="0 0 600 240"
        >
            <rect width="600" height="240" fill="#ffffff"/>

            <g stroke="#dddddd" stroke-width="1">
                <line x1="70" y1="45" x2="550" y2="45"/>
                <line x1="70" y1="74" x2="550" y2="74"/>
                <line x1="70" y1="103" x2="550" y2="103"/>
                <line x1="70" y1="132" x2="550" y2="132"/>
                <line x1="70" y1="161" x2="550" y2="161"/>
                <line x1="70" y1="190" x2="550" y2="190"/>
            </g>

            <g
                fill="#555555"
                font-family="DejaVu Sans"
                font-size="10"
            >
                <text x="35" y="48">100%</text>
                <text x="42" y="77">80%</text>
                <text x="42" y="106">60%</text>
                <text x="42" y="135">40%</text>
                <text x="42" y="164">20%</text>
                <text x="49" y="193">0%</text>

                <rect
                    x="448"
                    y="14"
                    width="12"
                    height="12"
                    fill="' . esc_attr($colour) . '"
                />

                <text x="466" y="24">
                    ' . esc_html($stage_label) . '
                </text>
            </g>

            ' . $bars . '
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
                min($two_stage_overview_percentages) -
                10
            ) / 10
        ) * 10
    );

    $two_stage_overview_axis_max = min(
        100,
        ceil(
            (
                max($two_stage_overview_percentages) +
                10
            ) / 10
        ) * 10
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
            floor((min($percentages) - 10) / 10) * 10
        );

        $axis_max = min(
            100,
            ceil((max($percentages) + 10) / 10) * 10
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
     * Key Growth chart: rank by change between the two real stages
     * and show exactly two bars per capability.
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
 * Sprint 3: live comparison rendering.
 *
 * Comparison data comes from the full reporting service rather than
 * the legacy PDF adapter because multiple program contexts must remain
 * separate report series.
 */
$comparison_stage_map = [
    'pre-program'       => 'pre_program',
    'completion'        => 'completion',
    'three-month-delay' => 'delay',
];

$comparison_backend_stage =
    isset($comparison_stage_map[$comparison_evaluation_point])
        ? $comparison_stage_map[$comparison_evaluation_point]
        : '';

$comparison_program_series = [];

if (
    $has_comparisons &&
    $comparison_backend_stage !== '' &&
    class_exists('TLE_Report_Service')
) {
    /*
     * Always include the primary program as the first comparison series.
     */
    $requested_comparison_programs = array_values(
        array_unique(
            array_merge(
                [$program],
                $comparison_programs
            )
        )
    );

    foreach (
        $requested_comparison_programs
        as $comparison_program_name
    ) {
        if (trim($comparison_program_name) === '') {
            continue;
        }

        $comparison_report =
            TLE_Report_Service::get_report(
                [
                    'program' =>
                        $comparison_program_name,
                    'cohort' =>
                        $cohort,
                    'evaluation_stage' =>
                        $comparison_backend_stage,
                ]
            );

        if (
            is_wp_error($comparison_report) ||
            empty($comparison_report['groups']) ||
            !is_array($comparison_report['groups'])
        ) {
            continue;
        }

        /*
         * A program/cohort/stage comparison must resolve to one
         * unambiguous reporting context. Do not combine unrelated
         * groups by averaging averages.
         */
        if (count($comparison_report['groups']) !== 1) {
            continue;
        }

        $comparison_group =
            $comparison_report['groups'][0];

        if (
            empty($comparison_group['capability_averages']) ||
            !is_array(
                $comparison_group['capability_averages']
            )
        ) {
            continue;
        }

        $program_capabilities = [];

        foreach (
            $comparison_group['capability_averages']
            as $section_key => $capabilities
        ) {
            if (!is_array($capabilities)) {
                continue;
            }

            foreach (
                $capabilities
                as $capability_name => $stage_values
            ) {
                if (
                    !is_array($stage_values) ||
                    !array_key_exists(
                        $comparison_backend_stage,
                        $stage_values
                    ) ||
                    $stage_values[
                        $comparison_backend_stage
                    ] === null ||
                    !is_numeric(
                        $stage_values[
                            $comparison_backend_stage
                        ]
                    )
                ) {
                    continue;
                }

                $program_capabilities[
                    $section_key
                ][
                    $capability_name
                ] = (float) $stage_values[
                    $comparison_backend_stage
                ];
            }
        }

        if (!empty($program_capabilities)) {
            $comparison_program_series[
                $comparison_program_name
            ] = $program_capabilities;
        }
    }

    /*
     * The primary program must have real data for the selected
     * comparison stage. Otherwise the comparison has no valid basis.
     */
    if (
        !isset(
            $comparison_program_series[$program]
        )
    ) {
        wp_die(
            esc_html(
                sprintf(
                    '%s data is not available for %s.',
                    $comparison_evaluation_label,
                    $program
                )
            )
        );
    }

    /*
     * At least one different program must also have real data.
     */
    $valid_comparison_programs =
        array_values(
            array_filter(
                array_keys(
                    $comparison_program_series
                ),
                static function (
                    $program_name
                ) use ($program) {
                    return $program_name !== $program;
                }
            )
        );

    if (empty($valid_comparison_programs)) {
        wp_die(
            esc_html(
                sprintf(
                    'No comparison program has %s data for the selected cohort.',
                    $comparison_evaluation_label
                )
            )
        );
    }

    /*
     * A single-stage primary report cannot show genuine growth.
     * Use the already-loaded live comparison data to make the
     * Overview useful rather than leaving the lower page empty.
     */
    if ($is_single_stage_report) {
        $overview_comparison_rows = '';

        $overview_comparison_sections = [
            'Insight'   => 'insight',
            'Influence' => 'influence',
            'Impact'    => 'impact',
        ];

        $overview_comparison_palette = [
            $report_colour,
            '#E66B5B',
            '#7A5AA6',
            '#93B83E',
            '#4F7F9B',
            '#D89A42',
        ];

        $overview_program_index = 0;

        foreach (
            $comparison_program_series
            as $comparison_program_name => $sections
        ) {
            $overview_metric_cells = '';
            $has_overview_scores = false;

            foreach (
                $overview_comparison_sections
                as $section_name => $section_key
            ) {
                $section_scores =
                    isset($sections[$section_key]) &&
                    is_array($sections[$section_key])
                        ? $sections[$section_key]
                        : [];

                $numeric_scores =
                    array_values(
                        array_filter(
                            $section_scores,
                            static function ($score) {
                                return is_numeric($score);
                            }
                        )
                    );

                if (empty($numeric_scores)) {
                    $overview_metric_cells .= '
                        <td class="overview-comparison-metric">
                            <strong>—</strong>
                            <span>Not available</span>
                        </td>
                    ';

                    continue;
                }

                $section_average =
                    array_sum($numeric_scores) /
                    count($numeric_scores);

                $section_percentage =
                    round(
                        ($section_average / 7) * 100
                    );

                $overview_metric_cells .= '
                    <td class="overview-comparison-metric">
                        <strong>' .
                            esc_html(
                                $section_percentage
                            ) .
                        '%</strong>
                        <span>' .
                            esc_html(
                                number_format(
                                    $section_average,
                                    2
                                )
                            ) .
                        ' / 7</span>
                    </td>
                ';

                $has_overview_scores = true;
            }

            if (!$has_overview_scores) {
                continue;
            }

            $series_colour =
                $overview_comparison_palette[
                    $overview_program_index %
                    count(
                        $overview_comparison_palette
                    )
                ];

            $row_class =
                $comparison_program_name === $program
                    ? ' overview-comparison-primary'
                    : '';

            $overview_comparison_rows .= '
                <tr class="' .
                    esc_attr(
                        trim($row_class)
                    ) .
                '">
                    <th>
                        <span
                            class="overview-comparison-swatch"
                            style="background:' .
                                esc_attr(
                                    $series_colour
                                ) .
                            ';"
                        ></span>

                        ' .
                            esc_html(
                                $comparison_program_name
                            ) .
                        '
                    </th>

                    ' . $overview_metric_cells . '
                </tr>
            ';

            $overview_program_index++;
        }

        if ($overview_comparison_rows !== '') {
            $overview_highlights_section_html = '
                <div class="overview-comparison-glance">

                    <h3 class="overview-comparison-heading">
                        Comparison at a glance
                    </h3>

                    <div class="overview-comparison-stage">
                        ' .
                            esc_html(
                                $comparison_evaluation_label
                            ) .
                        '
                    </div>

                    <table class="overview-comparison-table">

                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Insight</th>
                                <th>Influence</th>
                                <th>Impact</th>
                            </tr>
                        </thead>

                        <tbody>
                            ' . $overview_comparison_rows . '
                        </tbody>

                    </table>

                </div>
            ';
        }
    }
    /*
     * Grouped capability comparison chart.
     */
    $build_comparison_chart =
        static function (
            array $measures,
            array $program_series,
            string $section_key,
            string $primary_program,
            string $primary_colour
        ) {
            /*
             * Keep only programs that have real scores in this
             * particular report section.
             */
            $section_series = [];

            foreach (
                $program_series
                as $program_name => $sections
            ) {
                if (
                    empty($sections[$section_key]) ||
                    !is_array(
                        $sections[$section_key]
                    )
                ) {
                    continue;
                }

                $section_series[$program_name] =
                    $sections[$section_key];
            }

            if (
                !isset(
                    $section_series[$primary_program]
                ) ||
                count($section_series) < 2
            ) {
                return '';
            }

            $palette = [
                $primary_colour,
                '#E66B5B',
                '#7A5AA6',
                '#93B83E',
                '#4F7F9B',
                '#D89A42',
            ];

            $series_count =
                count($section_series);

            $legend_columns = 2;
            $legend_rows = (int) ceil(
                $series_count /
                $legend_columns
            );

            $plot_left = 70;
            $plot_right = 550;
            $plot_width =
                $plot_right - $plot_left;

            $plot_top =
                44 + ($legend_rows * 15);

            $plot_bottom = 196;
            $plot_height =
                $plot_bottom - $plot_top;

            $filtered_measures = [];

            foreach ($measures as $measure) {
                $measure_name =
                    isset($measure['measure'])
                        ? (string) $measure['measure']
                        : '';

                if ($measure_name === '') {
                    continue;
                }

                $has_score = false;

                foreach (
                    $section_series
                    as $scores
                ) {
                    if (
                        isset($scores[$measure_name]) &&
                        is_numeric(
                            $scores[$measure_name]
                        )
                    ) {
                        $has_score = true;
                        break;
                    }
                }

                if ($has_score) {
                    $filtered_measures[] =
                        $measure_name;
                }
            }

            if (empty($filtered_measures)) {
                return '';
            }

            $capability_count =
                count($filtered_measures);

            $slot_width =
                $plot_width /
                max(1, $capability_count);

            $group_width =
                min(
                    $slot_width * 0.78,
                    62
                );

            $bar_gap = 2;

            $bar_width =
                min(
                    18,
                    max(
                        4,
                        (
                            $group_width -
                            (
                                $bar_gap *
                                max(
                                    0,
                                    $series_count - 1
                                )
                            )
                        ) /
                        max(1, $series_count)
                    )
                );

            $actual_group_width =
                ($bar_width * $series_count) +
                (
                    $bar_gap *
                    max(
                        0,
                        $series_count - 1
                    )
                );

            $bars = '';
            $legend = '';
            $series_index = 0;

            foreach (
                $section_series
                as $program_name => $scores
            ) {
                $colour =
                    $palette[
                        $series_index %
                        count($palette)
                    ];

                $legend_column =
                    $series_index %
                    $legend_columns;

                $legend_row =
                    intdiv(
                        $series_index,
                        $legend_columns
                    );

                $legend_x =
                    75 +
                    ($legend_column * 250);

                $legend_y =
                    20 +
                    ($legend_row * 15);

                $legend_label =
                    $program_name;

                if (
                    strlen($legend_label) > 30
                ) {
                    $legend_label =
                        substr(
                            $legend_label,
                            0,
                            28
                        ) . '...';
                }

                $legend .= '
                    <rect
                        x="' . $legend_x . '"
                        y="' . ($legend_y - 9) . '"
                        width="10"
                        height="10"
                        fill="' .
                            esc_attr($colour) .
                        '"
                    />

                    <text
                        x="' . ($legend_x + 15) . '"
                        y="' . $legend_y . '"
                        font-family="DejaVu Sans"
                        font-size="8"
                        fill="#333333"
                    >' .
                        esc_html($legend_label) .
                    '</text>
                ';

                foreach (
                    $filtered_measures
                    as $capability_index =>
                        $capability_name
                ) {
                    if (
                        !isset(
                            $scores[
                                $capability_name
                            ]
                        ) ||
                        !is_numeric(
                            $scores[
                                $capability_name
                            ]
                        )
                    ) {
                        continue;
                    }

                    $percentage =
                        (
                            (float) $scores[
                                $capability_name
                            ] /
                            7
                        ) * 100;

                    $height =
                        ($percentage / 100) *
                        $plot_height;

                    $y =
                        $plot_bottom -
                        $height;

                    $centre =
                        $plot_left +
                        (
                            $slot_width *
                            $capability_index
                        ) +
                        ($slot_width / 2);

                    $group_start =
                        $centre -
                        ($actual_group_width / 2);

                    $x =
                        $group_start +
                        (
                            $series_index *
                            (
                                $bar_width +
                                $bar_gap
                            )
                        );

                    $bars .= '
                        <rect
                            x="' . $x . '"
                            y="' . $y . '"
                            width="' .
                                $bar_width .
                            '"
                            height="' .
                                $height .
                            '"
                            fill="' .
                                esc_attr($colour) .
                            '"
                        />
                    ';
                }

                $series_index++;
            }

            $labels = '';

            foreach (
                $filtered_measures
                as $capability_index =>
                    $capability_name
            ) {
                $centre =
                    $plot_left +
                    (
                        $slot_width *
                        $capability_index
                    ) +
                    ($slot_width / 2);

                $label =
                    $capability_name;

                $label_words =
                    preg_split(
                        '/\s+/',
                        trim($label)
                    ) ?: [];

                $label_lines = [$label];

                if (count($label_words) > 1) {
                    $best_split = 1;
                    $best_difference = PHP_INT_MAX;

                    for (
                        $split = 1;
                        $split < count($label_words);
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
                $label_start_y =
                    count($label_lines) > 1
                        ? 216
                        : 220;

                foreach (
                    $label_lines
                    as $line_index =>
                        $label_line
                ) {
                    $label_svg .= '
                        <text
                            x="' . $centre . '"
                            y="' .
                                (
                                    $label_start_y +
                                    ($line_index * 8)
                                ) .
                            '"
                            text-anchor="middle"
                            font-family="DejaVu Sans"
                            font-size="6.5"
                            fill="#333333"
                        >' .
                            esc_html($label_line) .
                        '</text>
                    ';
                }

                $labels .= '
                    ' . $label_svg . '
                ';
            }

            $grid = '';
            $axis_labels = '';

            foreach (
                [100, 80, 60, 40, 20, 0]
                as $percentage
            ) {
                $y =
                    $plot_bottom -
                    (
                        ($percentage / 100) *
                        $plot_height
                    );

                $grid .= '
                    <line
                        x1="' . $plot_left . '"
                        y1="' . $y . '"
                        x2="' . $plot_right . '"
                        y2="' . $y . '"
                    />
                ';

                $axis_labels .= '
                    <text
                        x="43"
                        y="' . ($y + 3) . '"
                    >' .
                        $percentage .
                        '%</text>
                ';
            }

            $svg = '
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="600"
                height="235"
                viewBox="0 0 600 235"
            >
                <rect
                    width="600"
                    height="235"
                    fill="#ffffff"
                />

                ' . $legend . '

                <g
                    stroke="#dddddd"
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

                ' . $bars . '
                ' . $labels . '
            </svg>
            ';

            return
                'data:image/svg+xml;base64,' .
                base64_encode($svg);
        };

    $comparison_section_keys = [
        'Insight'   => 'insight',
        'Influence' => 'influence',
        'Impact'    => 'impact',
    ];

    foreach (
        $comparison_section_keys
        as $section_name => $section_key
    ) {
        if (
            empty($overview_sections[$section_name])
        ) {
            continue;
        }

        $comparison_chart_data =
            $build_comparison_chart(
                $overview_sections[
                    $section_name
                ],
                $comparison_program_series,
                $section_key,
                $program,
                $report_colour
            );

        if ($comparison_chart_data === '') {
            continue;
        }

        $section_secondary_html[
            $section_name
        ] = '
            <h3 class="section-movers-title">
                Comparison - ' .
                    esc_html(
                        $comparison_evaluation_label
                    ) .
                '
            </h3>

            <div class="movers-chart">
                <img
                    src="' .
                        esc_attr(
                            $comparison_chart_data
                        ) .
                    '"
                    alt=""
                >
            </div>
        ';
    }
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
    $manager_question_ids = [
        '11',
        '14',
        '16',
        '17',
        '18',
        '19',
        '20',
        '21',
        '22',
        '23',
        '24',
        '25',
        '26',
        '27',
        '28',
        '30',
    ];

    $manager_program_aliases = [
        'I-LEAD Young Professionals' =>
            'I-LEAD Young Professionals',
        'I-LEAD Women in Community' =>
            'I-LEAD Women in Community Services',
        'I-LEAD Wine Industry' =>
            'I-LEAD Tassie Wine',
        'I-LEAD Pride' =>
            'I-LEAD Pride',
    ];

    $manager_program_value =
        $manager_program_aliases[$program] ??
        $program;

    $manager_form = null;
    $manager_entries = [];
    $manager_matching_entries = [];

    if (
        class_exists('TLE_Gravity_Forms_Provider') &&
        TLE_Gravity_Forms_Provider::is_available()
    ) {
        $manager_form =
            TLE_Gravity_Forms_Provider::get_form(50);

        $manager_entries =
            TLE_Gravity_Forms_Provider::get_all_entries(
                50,
                ['status' => 'active']
            );

        if (is_wp_error($manager_form)) {
            $manager_form = null;
        }

        if (is_wp_error($manager_entries)) {
            $manager_entries = [];
        }
    }

    foreach ($manager_entries as $manager_entry) {
        $entry_program = trim(
            (string) ($manager_entry['45'] ?? '')
        );

        $entry_cohort = trim(
            (string) ($manager_entry['43'] ?? '')
        );

        if (
            $entry_program === $manager_program_value &&
            $entry_cohort === (string) $cohort
        ) {
            $manager_matching_entries[] =
                $manager_entry;
        }
    }

    /*
     * Build question labels and choice maps from Form 50.
     */
    $manager_fields = [];

    if (
        is_array($manager_form) &&
        !empty($manager_form['fields']) &&
        is_array($manager_form['fields'])
    ) {
        foreach ($manager_form['fields'] as $field) {
            $field_id =
                isset($field['id'])
                    ? (string) $field['id']
                    : '';

            if (
                $field_id === '' ||
                !in_array(
                    $field_id,
                    $manager_question_ids,
                    true
                )
            ) {
                continue;
            }

            $choice_map = [];

            if (
                !empty($field['choices']) &&
                is_array($field['choices'])
            ) {
                foreach ($field['choices'] as $choice) {
                    if (!is_array($choice)) {
                        continue;
                    }

                    $choice_text = trim(
                        (string) ($choice['text'] ?? '')
                    );

                    $choice_value = trim(
                        (string) (
                            $choice['value'] ??
                            $choice_text
                        )
                    );

                    if ($choice_text === '') {
                        continue;
                    }

                    $choice_map[$choice_text] =
                        $choice_text;

                    if ($choice_value !== '') {
                        $choice_map[$choice_value] =
                            $choice_text;
                    }
                }
            }

            $manager_fields[$field_id] = [
                'label' =>
                    trim(
                        (string) (
                            $field['label'] ??
                            ('Question ' . $field_id)
                        )
                    ),
                'choices' => $choice_map,
            ];
        }
    }

    /*
     * Fallback labels preserve the approved Form 50 statements if
     * remote field metadata is temporarily unavailable.
     */
    $manager_fallback_labels = [
        '11' =>
            'Overall satisfaction with Tasmanian Leaders.',
        '14' =>
            'Shows greater emotional consistency in how they respond to workplace situations.',
        '16' =>
            'Has clearer reasoning for their decisions.',
        '17' =>
            'Appears to be less defensive and self-critical when making mistakes.',
        '18' =>
            'Shows increased awareness in meetings and work situations.',
        '19' =>
            'Has a greater focus on short-term goals.',
        '20' =>
            'Engages more openly with colleagues who hold different or opposing views.',
        '21' =>
            'More often proposes innovative approaches to work.',
        '22' =>
            'Exerts greater positive influence on others beyond their role.',
        '23' =>
            'More deeply engages in conversations with colleagues and clients.',
        '24' =>
            'Appears more comfortable working in situations where outcomes are not fully defined.',
        '25' =>
            'Is more willing to make decisions with complex information.',
        '26' =>
            'More frequently contributes beyond their core role to support their team.',
        '27' =>
            'More often helps build connections between colleagues who might not otherwise interact.',
        '28' =>
            'Has a clearer personal motivation for work beyond salary or benefits.',
        '30' =>
            'Has a stronger sense of commitment to the community.',
    ];

    $manager_rows = [];

    foreach ($manager_question_ids as $field_id) {
        $field =
            $manager_fields[$field_id] ??
            [
                'label' =>
                    $manager_fallback_labels[$field_id],
                'choices' => [],
            ];

        if ($field_id === '11') {
            $field['label'] =
                'Overall satisfaction with Tasmanian Leaders.';
        }

        $positive_choices =
            $field_id === '11'
                ? [
                    'Somewhat satisfied',
                    'Extremely satisfied',
                ]
                : [
                    'Agree',
                    'Strongly agree',
                ];

        $answered_count = 0;
        $positive_count = 0;

        foreach (
            $manager_matching_entries
            as $manager_entry
        ) {
            $raw_value = trim(
                (string) (
                    $manager_entry[$field_id] ??
                    ''
                )
            );

            if ($raw_value === '') {
                continue;
            }

            $answered_count++;

            $display_value =
                $field['choices'][$raw_value] ??
                $raw_value;

            if (
                in_array(
                    $display_value,
                    $positive_choices,
                    true
                )
            ) {
                $positive_count++;
            }
        }

        $positive_rating = null;

        if ($answered_count > 0) {
            $positive_rating =
                ($positive_count / $answered_count) *
                100;
        }

        $manager_rows[] = [
            'statement' => $field['label'],
            'rating' => $positive_rating,
            'answered' => $answered_count,
        ];
    }

    $manager_response_count =
        count($manager_matching_entries);

    if (trim($manager_summary_text) !== '') {
        $manager_summary_output =
            $manager_summary_text;
    } elseif ($manager_response_count > 0) {
        $manager_summary_output = sprintf(
            'Manager feedback based on %d response%s for %s in %s.',
            $manager_response_count,
            $manager_response_count === 1
                ? ''
                : 's',
            $program,
            $cohort
        );
    } else {
        $manager_summary_output =
            'No manager responses could be matched to the selected program and cohort. The statements below are shown for reference; ratings will appear when matching manager responses are available.';
    }

    $build_manager_rows =
        static function (array $rows) use (
            $report_colour,
            $recommendation_text_colour
        ) {
            $html = '';

            foreach ($rows as $row) {
                $has_rating =
                    $row['rating'] !== null;

                $rating_text =
                    $has_rating
                        ? round(
                            (float) $row['rating']
                        ) . '%'
                        : '—';

                $rating_background =
                    $has_rating
                        ? $report_colour
                        : '#e8ebe9';

                $rating_colour =
                    $has_rating
                        ? $recommendation_text_colour
                        : '#555555';

                $html .= '
                    <tr>
                        <td class="manager-statement-cell">
                            ' .
                                esc_html(
                                    $row['statement']
                                ) .
                            '
                        </td>

                        <td
                            class="manager-rating-cell"
                            style="
                                background: ' .
                                    esc_attr(
                                        $rating_background
                                    ) .
                                ';
                                color: ' .
                                    esc_attr(
                                        $rating_colour
                                    ) .
                                ';
                            "
                        >
                            ' .
                                esc_html(
                                    $rating_text
                                ) .
                            '
                        </td>
                    </tr>
                ';
            }

            return $html;
        };

    $manager_page_1_rows =
        array_slice($manager_rows, 0, 8);

    $manager_page_2_rows =
        array_slice($manager_rows, 8);

    $manager_page_1_table =
        $build_manager_rows(
            $manager_page_1_rows
        );

    $manager_page_2_table =
        $build_manager_rows(
            $manager_page_2_rows
        );

    /*
     * Official Tasmanian Leaders logo.
     * Embedded as base64 so Dompdf does not depend on local file URLs.
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

    $manager_pages_html = '

    <div class="page content-page manager-page">

        ' . $brand_logo_html . '

        <div class="cover-rule"></div>

        <div class="section-label">
            ' .
                esc_html($program) .
                ' &middot; ' .
                esc_html($cohort) .
            '
        </div>

        <h2 class="section-report-title">
            Managers
        </h2>

        <div class="manager-summary-box">
            <div class="box-heading">
                Summary
            </div>

            <div class="manager-summary-text">
                ' .
                    nl2br(
                        esc_html(
                            $manager_summary_output
                        )
                    ) .
                '
            </div>
        </div>

        <h3 class="manager-statements-heading">
            Statements
        </h3>

        <table class="manager-statements-table">
            <thead>
                <tr>
                    <th>Statement</th>
                    <th>Positive rating</th>
                </tr>
            </thead>
            <tbody>
                ' . $manager_page_1_table . '
            </tbody>
        </table>

        <div class="mock-page-footer">
            Page 6 of 7
        </div>

    </div>

    <div class="page content-page manager-page">

        ' . $brand_logo_html . '

        <div class="cover-rule"></div>

        <div class="section-label">
            ' .
                esc_html($program) .
                ' &middot; ' .
                esc_html($cohort) .
            '
        </div>

        <h2 class="section-report-title">
            Managers
        </h2>

        <h3 class="manager-statements-heading manager-statements-heading--continued">
            Statements
        </h3>

        <table class="manager-statements-table">
            <thead>
                <tr>
                    <th>Statement</th>
                    <th>Positive rating</th>
                </tr>
            </thead>
            <tbody>
                ' . $manager_page_2_table . '
            </tbody>
        </table>

        <div class="mock-page-footer">
            Page 7 of 7
        </div>

    </div>
    ';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>

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

    <div class="overview-legend">

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

    ' . $overview_highlights_section_html . '

    <div class="mock-page-footer">
        Page 2 of ' . esc_html($total_pages) . '
    </div>

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

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Insight']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Insight'] . '

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($insight_growth_summary_class) . '">
                <div class="box-heading">
                    ' . esc_html($section_summary_heading) . '
                </div>

                ' . $insight_growth_summary_html . '
            </td>

            ' . $insight_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 3 of ' . esc_html($total_pages) . '
    </div>

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

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Influence']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Influence'] . '

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($influence_growth_summary_class) . '">
                <div class="box-heading">
                    ' . esc_html($section_summary_heading) . '
                </div>

                ' . $influence_growth_summary_html . '
            </td>

            ' . $influence_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 4 of ' . esc_html($total_pages) . '
    </div>

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

    <div class="section-chart">
        <img
            src="' . esc_attr($section_chart_data['Impact']) . '"
            alt=""
        >
    </div>

    ' . $section_secondary_html['Impact'] . '

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($impact_growth_summary_class) . '">
                <div class="box-heading">
                    ' . esc_html($section_summary_heading) . '
                </div>

                ' . $impact_growth_summary_html . '
            </td>

            ' . $impact_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 5 of ' . esc_html($total_pages) . '
    </div>

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