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
        'program' => 'Tasmanian Leaders Program',
        'cohort' => '2026 Cohort',
        'evaluation_point' => 'Post-program Evaluation',
        'average_score' => '7.8 / 10',
    ];
}

function tle_render_report_page()
{
    $data = tle_get_test_report_data();
    ?>
    <div class="wrap">
        <h1>Evaluation Report</h1>

        <p><strong>Program:</strong> <?php echo esc_html($data['program']); ?></p>
        <p><strong>Cohort:</strong> <?php echo esc_html($data['cohort']); ?></p>
        <p><strong>Evaluation Point:</strong> <?php echo esc_html($data['evaluation_point']); ?></p>
        <p><strong>Average Score:</strong> <?php echo esc_html($data['average_score']); ?></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="tle_export_pdf">
            <?php wp_nonce_field('tle_export_pdf_action', 'tle_export_pdf_nonce'); ?>

            <?php submit_button('Export PDF'); ?>
        </form>
    </div>
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

    $html = '
        <h1>Evaluation Report</h1>
        <p><strong>Program:</strong> ' . esc_html($data['program']) . '</p>
        <p><strong>Cohort:</strong> ' . esc_html($data['cohort']) . '</p>
        <p><strong>Evaluation Point:</strong> ' . esc_html($data['evaluation_point']) . '</p>
        <p><strong>Average Score:</strong> ' . esc_html($data['average_score']) . '</p>
    ';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('evaluation-report.pdf', ['Attachment' => true]);

    exit;
}
add_action('admin_post_tle_export_pdf', 'tle_export_pdf');