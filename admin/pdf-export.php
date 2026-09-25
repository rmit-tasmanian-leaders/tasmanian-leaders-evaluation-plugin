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
    $report_title_input = isset($_POST['report_title'])
        ? sanitize_text_field(wp_unslash($_POST['report_title']))
        : '';

    $purpose_text = isset($_POST['purpose_text'])
        ? sanitize_textarea_field(wp_unslash($_POST['purpose_text']))
        : '';

    $impact_text_1 = isset($_POST['impact_text_1'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_text_1']))
        : '';

    $impact_text_2 = isset($_POST['impact_text_2'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_text_2']))
        : '';

    $impact_text_3 = isset($_POST['impact_text_3'])
        ? sanitize_textarea_field(wp_unslash($_POST['impact_text_3']))
        : '';

    $report_colour_key = isset($_POST['report_colour'])
        ? sanitize_key(wp_unslash($_POST['report_colour']))
        : 'teal';

    $report_colours = [
        'teal' => '#4f7f84',
        'coral' => '#ff756b',
        'lime' => '#b8f06a',
        'purple' => '#c249df',
    ];

    $report_colour = isset($report_colours[$report_colour_key])
        ? $report_colours[$report_colour_key]
        : $report_colours['teal'];

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

    <g fill="#666666" font-family="DejaVu Sans" font-size="9">
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
        stroke-width="3"
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
        <circle cx="70" cy="' . $chart_y($overview_averages['Insight']['pre']) . '" r="5"/>
        <circle cx="300" cy="' . $chart_y($overview_averages['Insight']['completion']) . '" r="5"/>
        <circle cx="530" cy="' . $chart_y($overview_averages['Insight']['delay']) . '" r="5"/>
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

$top_impacts = array_slice($growth_items, 0, 3);
$key_growth = array_slice($growth_items, 0, 5);

$impact_descriptions = [
    $impact_text_1,
    $impact_text_2,
    $impact_text_3,
];

$strongest_impact_html = '';

foreach ($top_impacts as $index => $item) {
    $description = $impact_descriptions[$index] ?? '';

    $strongest_impact_html .= '
        <td class="overview-impact-card">
            <div class="overview-impact-percent">
                +' . esc_html(round($item['growth'])) . '%
            </div>

            <div class="overview-impact-name">
                ' . esc_html($item['measure']) . '
            </div>

            <div class="overview-impact-description">
                ' . esc_html($description) . '
            </div>
        </td>
    ';
}

$key_growth_html = '';

foreach ($key_growth as $item) {
    $key_growth_html .= '
        <td class="key-growth-cell">
            <div
                class="key-growth-circle"
                style="background: ' . esc_attr($report_colour) . ';"
            >
                <strong>
                    +' . esc_html(round($item['growth'])) . '%
                </strong>

                <span>
                    ' . esc_html($item['measure']) . '
                </span>
            </div>
        </td>
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

        <g fill="#666666" font-family="DejaVu Sans" font-size="9">
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
            stroke-width="3"
        />

        <g fill="' . esc_attr($report_colour) . '">
            <circle cx="70" cy="' . $section_chart_y($averages['pre']) . '" r="5"/>
            <circle cx="300" cy="' . $section_chart_y($averages['completion']) . '" r="5"/>
            <circle cx="530" cy="' . $section_chart_y($averages['delay']) . '" r="5"/>
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
                fill="#a7e8ef"
            />

            <text
                x="' . ($x + 42) . '"
                y="215"
                text-anchor="middle"
                font-family="DejaVu Sans"
                font-size="8"
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

        <g stroke="#dddddd" stroke-width="1">
            <line x1="70" y1="45" x2="550" y2="45"/>
            <line x1="70" y1="74" x2="550" y2="74"/>
            <line x1="70" y1="103" x2="550" y2="103"/>
            <line x1="70" y1="132" x2="550" y2="132"/>
            <line x1="70" y1="161" x2="550" y2="161"/>
            <line x1="70" y1="190" x2="550" y2="190"/>
        </g>

        <g fill="#666666" font-family="DejaVu Sans" font-size="9">
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
        color: #ffffff;
        font-size: 17px;
        font-weight: bold;
    }

    .cover-image-wrap {
        height: 315px;
        margin: 24px -70px 0;
        overflow: hidden;
    }

    .cover-image {
        width: 100%;
    }

    .cover-image-placeholder {
        height: 270px;
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
        margin: 0 0 10px;
        color: #111111;
        font-size: 34px;
        line-height: 1;
    }

    .overview-legend {
        margin-bottom: 8px;
        color: #444444;
        font-size: 10px;
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
        margin-bottom: 8px;
    }

    .overview-chart img {
        width: 100%;
    }

    .overview-subheading {
        margin: 12px 0 8px;
        color: #111111;
        font-size: 19px;
    }

    .overview-impact-table,
    .key-growth-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px 0;
        margin-left: -8px;
    }

    .overview-impact-card {
        width: 33.33%;
        padding: 12px;
        vertical-align: top;
        background: #a8f5b5;
    }

    .overview-impact-percent {
        margin-bottom: 2px;
        color: #111111;
        font-size: 23px;
        font-weight: bold;
    }

    .overview-impact-name {
        margin-bottom: 4px;
        color: #111111;
        font-size: 11px;
        font-weight: bold;
    }

    .overview-impact-description {
        color: #222222;
        font-size: 9px;
        line-height: 1.3;
    }

    .key-growth-cell {
        width: 20%;
        text-align: center;
        vertical-align: top;
    }

    .key-growth-circle {
        width: 82px;
        height: 82px;
        margin: 0 auto;
        border-radius: 41px;
        color: #ffffff;
        text-align: center;
    }

    .key-growth-circle strong {
        display: block;
        padding-top: 17px;
        font-size: 13px;
    }

    .key-growth-circle span {
        display: block;
        padding: 3px 6px 0;
        font-size: 8px;
        line-height: 1.15;
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
        background: #a8f5b5;
        color: #111111;
    }

    .recommendations-box {
        color: #ffffff;
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
        padding-top: 42px;
        padding-bottom: 42px;
    }

    .insight-page .brand {
        margin-bottom: 32px;
        font-size: 30px;
    }

    .insight-page .cover-rule {
        margin-bottom: 20px;
    }

    .section-report-title {
        margin-bottom: 0;
        font-size: 29px;
    }

    .section-chart {
        height: 145px;
        margin-bottom: 2px;
        overflow: hidden;
    }

    .section-chart img {
        width: 100%;
        height: 145px;
    }

    .section-movers-title {
        margin: 2px 0 0;
        font-size: 18px;
    }

    .movers-chart {
        height: 145px;
        margin-bottom: 0;
        overflow: hidden;
    }

    .movers-chart img {
        width: 100%;
        height: 145px;
    }

    .section-summary-table {
        margin-top: 2px;
        page-break-inside: avoid;
    }

    .growth-box,
    .recommendations-box {
        padding: 10px;
    }

    .box-heading {
        margin-bottom: 7px;
        font-size: 16px;
    }

    .growth-line,
    .recommendation-line {
        margin-bottom: 6px;
        font-size: 8px;
        line-height: 1.25;
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
        Strongest Impact
    </h3>

    <table class="overview-impact-table">
        <tr>
            ' . $strongest_impact_html . '
        </tr>
    </table>

    <h3 class="overview-subheading">
        Key Growth
    </h3>

    <table class="key-growth-table">
        <tr>
            ' . $key_growth_html . '
        </tr>
    </table>

    <div class="mock-page-footer">
        Page 2 of 5
    </div>

</div>
<div class="page content-page insight-page">

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
        Top 3 Positive Movers
    </h3>

    <div class="movers-chart">
        <img
            src="' . esc_attr($section_movers_chart_data['Insight']) . '"
            alt=""
        >
    </div>

    <table class="section-summary-table">
        <tr>

            <td class="growth-box">
                <div class="box-heading">
                    Growth
                </div>

                ' . $section_growth_html['Insight'] . '
            </td>

            <td
                class="recommendations-box"
                style="background: ' . esc_attr($report_colour) . ';"
            >
                <div class="box-heading">
                    Recommendations
                </div>

                <div class="recommendation-line">
                    1. ' . esc_html($section_recommendations['Insight'][0]) . '
                </div>

                <div class="recommendation-line">
                    2. ' . esc_html($section_recommendations['Insight'][1]) . '
                </div>

                <div class="recommendation-line">
                    3. ' . esc_html($section_recommendations['Insight'][2]) . '
                </div>
            </td>

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 3 of 5
    </div>

</div>
<div class="page content-page insight-page">

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
        Top 3 Positive Movers
    </h3>

    <div class="movers-chart">
        <img
            src="' . esc_attr($section_movers_chart_data['Influence']) . '"
            alt=""
        >
    </div>

    <table class="section-summary-table">
        <tr>

            <td class="growth-box">
                <div class="box-heading">
                    Growth
                </div>

                ' . $section_growth_html['Influence'] . '
            </td>

            <td
                class="recommendations-box"
                style="background: ' . esc_attr($report_colour) . ';"
            >
                <div class="box-heading">
                    Recommendations
                </div>

                <div class="recommendation-line">
                    1. ' . esc_html($section_recommendations['Influence'][0]) . '
                </div>

                <div class="recommendation-line">
                    2. ' . esc_html($section_recommendations['Influence'][1]) . '
                </div>

                <div class="recommendation-line">
                    3. ' . esc_html($section_recommendations['Influence'][2]) . '
                </div>
            </td>

        </tr>
    </table>

    <div class="mock-page-footer">
        Page 4 of 5
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