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
                'measure' => 'Clarity of purpose',
                'pre' => 4.7,
                'completion' => 5.4,
                'delay' => 5.6,
                'benchmark' => 5.0,
            ],
        ],

        'influence' => [
            [
                'measure' => 'Collaboration',
                'pre' => 5.1,
                'completion' => 5.6,
                'delay' => 5.8,
                'benchmark' => 5.2,
            ],
            [
                'measure' => 'Networking',
                'pre' => 4.6,
                'completion' => 5.2,
                'delay' => 5.5,
                'benchmark' => 4.9,
            ],
            [
                'measure' => 'Tolerance for ambiguity',
                'pre' => 4.4,
                'completion' => 4.9,
                'delay' => 5.1,
                'benchmark' => 4.8,
            ],
        ],

        'impact' => [
            [
                'measure' => 'Place-attachment',
                'pre' => 5.0,
                'completion' => 5.4,
                'delay' => 5.3,
                'benchmark' => 5.1,
            ],
            [
                'measure' => 'Capacity to foster belonging',
                'pre' => 4.7,
                'completion' => 5.5,
                'delay' => 5.6,
                'benchmark' => 5.0,
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

    $allowed_programs = [
    'I-LEAD Young Professionals',
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
    }

    .brand {
        font-size: 22px;
        font-weight: bold;
        color: #234b3b;
        margin-bottom: 110px;
    }

    .cover-program {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 14px;
    }

    .cover-title {
        font-size: 50px;
        line-height: 1.05;
        font-weight: bold;
        color: #234b3b;
        margin: 0;
        max-width: 560px;
    }

    .cover-year {
        display: inline-block;
        margin-top: 36px;
        padding: 10px 22px;
        background: #234b3b;
        color: #ffffff;
        border-radius: 20px;
        font-size: 18px;
    }

    .cover-line {
        position: absolute;
        right: 110px;
        top: 230px;
        width: 2px;
        height: 480px;
        background: #234b3b;
    }

    .cover-footer {
        position: absolute;
        bottom: 70px;
        left: 70px;
        right: 70px;
        border-top: 1px solid #d9dddb;
        padding-top: 18px;
        font-size: 12px;
        color: #646970;
    }

    .cover-footer strong {
        color: #17211d;
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

</style>
</head>

<body>

<div class="page cover">

    <div class="brand">
        Tasmanian Leaders
    </div>

    <div class="cover-program">
        ' . esc_html($program) . '
    </div>

    <h1 class="cover-title">
        ' . esc_html($report_title) . '
    </h1>

    <div class="cover-year">
        ' . esc_html($cohort) . '
    </div>

    <div class="cover-line"></div>

    <div class="cover-footer">

        <strong>Evaluation and Learning Framework</strong><br>

        Evaluation: ' . esc_html($evaluation_point) . '<br>
        Comparison: ' . esc_html($comparison) . '<br>
        Benchmark: ' . esc_html($data['benchmark']) . '

    </div>

</div>

<div class="page content-page">

    <div class="page-brand">
        Tasmanian Leaders
    </div>

    <div class="section-label">
        Evaluation and Learning Framework
    </div>

    <h2 class="section-title">
        Gaining Context
    </h2>

    <p class="section-intro">
        Social desirability indicators provide additional context when interpreting
        self-reported leadership capability results. They help identify whether
        responses may have been influenced by perceived expectations rather than
        solely reflecting participants\' own perceptions.
    </p>

    <table class="context-table">
        <tr>

            <td class="context-box">

                <div class="context-number">
                    ' . esc_html($data['social_desirability']['impression_management']) . '
                </div>

                <div class="context-name">
                    Impression management
                </div>

                <div class="context-description">
                    Indicates the extent to which responses may be shaped by how
                    participants believe they are expected to present themselves.
                </div>

            </td>

            <td class="context-spacer"></td>

            <td class="context-box">

                <div class="context-number">
                    ' . esc_html($data['social_desirability']['self_deception']) . '
                </div>

                <div class="context-name">
                    Self-deception enhancement
                </div>

                <div class="context-description">
                    Indicates the extent to which participants may unintentionally
                    report an overly positive perception of their own capability.
                </div>

            </td>

        </tr>
    </table>

    <div class="context-note">
        These indicators should be considered alongside the wider ELF results when
        interpreting cohort strengths, development opportunities and changes across
        evaluation points.
    </div>

    <div class="page-number">
        02
    </div>

</div>

<div class="page content-page insight-page">

    <div class="page-brand">
        Tasmanian Leaders
    </div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-title">
        Insight
    </h2>

<p class="insight-intro">
    Insight reflects how participants understand themselves, their environment
    and the factors that influence their leadership decisions.
    ' . esc_html($pdf_results_context) . '
</p>

<table class="metric-table">

    <thead>
        ' . $metric_headers . '
    </thead>

    <tbody>
        ' . $insight_rows . '
    </tbody>

</table>

    <div class="context-note">
        Higher scores indicate stronger perceived leadership capability.
        Scores in this prototype use sample data on a 1–7 scale.
    </div>

    <div class="page-number">
        03
    </div>

</div>

<div class="page content-page insight-page">

    <div class="page-brand">
        Tasmanian Leaders
    </div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-title">
        Influence
    </h2>

    <p class="insight-intro">
        Influence reflects how participants work with others, navigate complexity
        and build relationships that support effective leadership outcomes.
    </p>

<table class="metric-table">

    <thead>
        ' . $metric_headers . '
    </thead>

    <tbody>
        ' . $influence_rows . '
    </tbody>

</table>

    <div class="context-note">
        Higher scores indicate stronger perceived leadership capability.
        Scores in this prototype use sample data on a 1–7 scale.
    </div>

    <div class="page-number">
        04
    </div>

</div>

<div class="page content-page insight-page">

    <div class="page-brand">
        Tasmanian Leaders
    </div>

    <div class="section-label">
        ' . esc_html($program) . ' · ' . esc_html($cohort) . '
    </div>

    <h2 class="section-title">
        Impact
    </h2>

    <p class="insight-intro">
        Impact reflects how participants contribute beyond themselves by strengthening
        belonging, connection and positive outcomes within their organisations and communities.
    </p>

<table class="metric-table">

    <thead>
        ' . $metric_headers . '
    </thead>

    <tbody>
        ' . $impact_rows . '
    </tbody>

</table>

    <div class="context-note">
        Higher scores indicate stronger perceived leadership capability.
        Scores in this prototype use sample data on a 1–7 scale.
    </div>

    <div class="page-number">
        05
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