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

    $allowed_programs = [
    'I-LEAD Young Professionals',
    'Emerging Leaders Program',
    'I-LEAD Women in Industry',
    'I-LEAD Tassie Wine',
];

$allowed_cohorts = [
    '2025',
    '2026',
];

$allowed_report_types = [
    'Initial Leadership Capability Survey',
    'Final ELF Evaluation Report',
];

$allowed_comparisons = [
    'Pre-program / Completion / 3-Month Delay',
    'Pre-program / Completion',
    'Pre-program / Tasmanian Benchmark',
];

$program = isset($_POST['program'])
    ? sanitize_text_field(wp_unslash($_POST['program']))
    : $data['program'];

$cohort = isset($_POST['cohort'])
    ? sanitize_text_field(wp_unslash($_POST['cohort']))
    : $data['cohort'];

$report_type = isset($_POST['report_type'])
    ? sanitize_text_field(wp_unslash($_POST['report_type']))
    : $data['report_type'];

$comparison = isset($_POST['comparison'])
    ? sanitize_text_field(wp_unslash($_POST['comparison']))
    : $data['comparison'];

if (!in_array($program, $allowed_programs, true)) {
    $program = $data['program'];
}

if (!in_array($cohort, $allowed_cohorts, true)) {
    $cohort = $data['cohort'];
}

if (!in_array($report_type, $allowed_report_types, true)) {
    $report_type = $data['report_type'];
}

if (!in_array($comparison, $allowed_comparisons, true)) {
    $comparison = $data['comparison'];
}

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

$section_recommendations = [
    'Insight' => [
        'Add a reflective check-in between post-program and delayed evaluation.',
        'Introduce goal-setting earlier in the program to reinforce clarity of purpose.',
        'Review social-awareness content and build on existing participant strengths.',
    ],
    'Influence' => [
        'Extend structured peer contact beyond the formal program end date.',
        'Introduce networking opportunities earlier so participants have longer to apply them.',
        'Continue providing stretch opportunities that strengthen collaboration.',
    ],
    'Impact' => [
        'Document what contributed to the strongest belonging result.',
        'Review how place-attachment is measured and interpreted across evaluation points.',
        'Include manager evaluation where available to support behavioural evidence.',
    ],
];

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
    floor((min($insight_percentages) - 5) / 10) * 10
);

$insight_axis_max = min(
    100,
    ceil((max($insight_percentages) + 5) / 10) * 10
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
        $custom_text = sprintf(
            '%s increased by %s percentage points.',
            $item['measure'],
            round($item['increase'])
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
    floor((min($influence_percentages) - 5) / 10) * 10
);

$influence_axis_max = min(
    100,
    ceil((max($influence_percentages) + 5) / 10) * 10
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
        $custom_text = sprintf(
            '%s increased by %s percentage points.',
            $item['measure'],
            round($item['increase'])
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
    floor((min($impact_percentages) - 5) / 10) * 10
);

$impact_axis_max = min(
    100,
    ceil((max($impact_percentages) + 5) / 10) * 10
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
        $custom_text = sprintf(
            '%s increased by %s percentage points.',
            $item['measure'],
            round($item['increase'])
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
        color: #111111;
        font-size: 38px;
        font-weight: normal;
        line-height: 0.92;
    }

    .brand-second-line {
        display: block;
        margin-left: 70px;
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

    .growth-box,
    .recommendations-box {
        width: 50%;
        padding: 15px;
        vertical-align: top;
    }

    .growth-box {
        background: ' . esc_attr($report_colour_light) . ';
        color: #111111;
    }

    .recommendations-box {
        color: ' . esc_attr($recommendation_text_colour) . ';
    }

    .box-heading {
        margin-bottom: 12px;
        font-size: 19px;
        font-weight: bold;
    }

    .growth-line,
    .recommendation-line {
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

    .growth-box,
    .recommendations-box {
        height: 130px;
        padding: 14px;
        vertical-align: top;
    }

    .box-heading {
        margin-bottom: 9px;
        font-size: 18px;
    }

    .growth-line,
    .recommendation-line {
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
</style>
</head>

<body>

<div class="page cover">

    <div class="brand">
        Tasmanian
        <span class="brand-second-line">Leaders</span>
    </div>

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
        Page 1 of 5
    </div>

</div>

<div class="page content-page">

    <div class="brand">
        Tasmanian
        <span class="brand-second-line">Leaders</span>
    </div>

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

    <h3 class="overview-subheading">
        Highlights
    </h3>

    ' . $highlights_html . '

    <div class="mock-page-footer">
        Page 2 of 5
    </div>

</div>
<div class="page content-page insight-page final-insight-page">

    <div class="brand">
        Tasmanian
        <span class="brand-second-line">Leaders</span>
    </div>

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

    <h3 class="section-movers-title">
        Key Growth
    </h3>

    <div class="movers-chart">
        <img
            src="' . esc_attr($section_movers_chart_data['Insight']) . '"
            alt=""
        >
    </div>

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($insight_growth_summary_class) . '">
                <div class="box-heading">
                    Growth Summary
                </div>

                ' . $insight_growth_summary_html . '
            </td>

            ' . $insight_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 3 of 5
    </div>

</div>
<div class="page content-page insight-page final-influence-page">

    <div class="brand">
        Tasmanian
        <span class="brand-second-line">Leaders</span>
    </div>

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

    <h3 class="section-movers-title">
        Key Growth
    </h3>

    <div class="movers-chart">
        <img
            src="' . esc_attr($section_movers_chart_data['Influence']) . '"
            alt=""
        >
    </div>

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($influence_growth_summary_class) . '">
                <div class="box-heading">
                    Growth Summary
                </div>

                ' . $influence_growth_summary_html . '
            </td>

            ' . $influence_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 4 of 5
    </div>

</div>
<div class="page content-page insight-page final-impact-page">

    <div class="brand">
        Tasmanian
        <span class="brand-second-line">Leaders</span>
    </div>

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

    <h3 class="section-movers-title">
        Key Growth
    </h3>

    <div class="movers-chart">
        <img
            src="' . esc_attr($section_movers_chart_data['Impact']) . '"
            alt=""
        >
    </div>

    <table class="section-summary-table">
        <tr>

            <td class="' . esc_attr($impact_growth_summary_class) . '">
                <div class="box-heading">
                    Growth Summary
                </div>

                ' . $impact_growth_summary_html . '
            </td>

            ' . $impact_quote_html . '

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 5 of 5
    </div>

</div>
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