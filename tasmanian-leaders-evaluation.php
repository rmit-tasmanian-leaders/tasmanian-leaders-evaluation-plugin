<?php
/**
 * Plugin Name: Tasmanian Leaders Evaluation Plugin
 * Description: Evaluation reporting plugin for Tasmanian Leaders.
 * Version: 0.1.0
 * Author: Team 55
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the existing WordPress Admin report and PDF export functionality.
require_once plugin_dir_path(__FILE__) . 'admin/pdf-export.php';

// Load the front-end dashboard shortcode integration.
require_once plugin_dir_path(__FILE__) . 'includes/class-dashboard-shortcode.php';

// Load the evaluation database functionality.
require_once plugin_dir_path(__FILE__) . 'includes/class-evaluation-database.php';

// Load the REST API integration.
require_once plugin_dir_path(__FILE__) . 'includes/class-rest-api.php';

// Load the evaluation data service and Gravity Forms provider.
require_once plugin_dir_path(__FILE__) . 'includes/class-evaluation-data-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gravity-forms-provider.php';

// Load the reporting service.
require_once plugin_dir_path(__FILE__) . 'includes/class-reporting-service.php';

// Create the evaluation database table when the plugin is activated.
register_activation_hook(
    __FILE__,
    ['TLE_Evaluation_Database', 'create_table']
);