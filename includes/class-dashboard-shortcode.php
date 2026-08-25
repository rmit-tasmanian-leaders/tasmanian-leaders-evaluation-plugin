<?php

/**
 * Tasmanian Leaders Evaluation Dashboard Shortcode
 *
 * Registers and renders the front-end evaluation dashboard shortcode.
 *
 * Shortcode:
 * [tasmanian_leaders_evaluation_dashboard]
 *
 * @package TasmanianLeadersEvaluation
 */

// Prevent this PHP file from being accessed directly outside WordPress.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the front-end evaluation dashboard shortcode.
 *
 * WordPress will replace the shortcode with the output returned by
 * tle_render_dashboard_shortcode().
 *
 * Example:
 * [tasmanian_leaders_evaluation_dashboard]
 *
 * @return void
 */
function tle_register_dashboard_shortcode()
{
    // Register the shortcode name and connect it to its rendering function.
    add_shortcode(
        'tasmanian_leaders_evaluation_dashboard',
        'tle_render_dashboard_shortcode'
    );
}

// Register the shortcode after WordPress has initialised.
add_action('init', 'tle_register_dashboard_shortcode');

/**
 * Render the Tasmanian Leaders evaluation dashboard.
 *
 * This function:
 * 1. Checks whether the visitor is logged in.
 * 2. Loads the dashboard stylesheet.
 * 3. Loads the front-end dashboard template.
 * 4. Returns the generated HTML to WordPress.
 *
 * This is currently a proof-of-concept access model.
 * Final WordPress roles and capabilities will be confirmed with the client.
 *
 * @return string Dashboard HTML or an access message.
 */
function tle_render_dashboard_shortcode()
{
    /*
     * Evaluation information should not be displayed publicly.
     *
     * For the current prototype, any authenticated WordPress user can
     * access the dashboard. Role/capability restrictions can be added
     * when the client's final access requirements are confirmed.
     */
    if (!is_user_logged_in()) {
        return '<p class="tle-access-message">Please log in to access the evaluation dashboard.</p>';
    }

    /*
     * Load the dashboard stylesheet only when the shortcode is rendered.
     *
     * plugin_dir_url(dirname(__FILE__)) resolves the URL of the main
     * plugin directory from this file inside the includes directory.
     */
    wp_enqueue_style(
        'tle-dashboard',
        plugin_dir_url(dirname(__FILE__)) . 'assets/css/dashboard.css',
        [],
        '0.1.0'
    );

    /*
     * Start output buffering.
     *
     * WordPress shortcodes must return their generated HTML rather than
     * directly printing the entire interface.
     */
    ob_start();

    // Build the absolute path to the front-end dashboard template.
    $template_path = plugin_dir_path(dirname(__FILE__))
        . 'templates/evaluation-dashboard.php';

    /*
     * Confirm the template exists before loading it.
     *
     * This prevents a PHP warning or broken page if the template is
     * accidentally removed or renamed.
     */
    if (!file_exists($template_path)) {
        ob_end_clean();

        return '<p class="tle-access-message">The evaluation dashboard is currently unavailable.</p>';
    }

    // Load the dashboard template into the output buffer.
    include $template_path;

    // Return the generated template HTML to WordPress.
    return ob_get_clean();
}