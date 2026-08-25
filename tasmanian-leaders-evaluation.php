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
