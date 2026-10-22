<?php

/**
 * Evaluation Database
 *
 * Creates and manages the initial evaluation database table.
 *
 * @package TasmanianLeadersEvaluation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TLE_Evaluation_Database
{
    /**
     * Get the evaluation table name.
     *
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . 'tle_evaluations';
    }

    /**
     * Create the evaluation database table.
     *
     * @return void
     */
    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            participant_id VARCHAR(191) NOT NULL,
            program VARCHAR(255) NOT NULL,
            cohort VARCHAR(100) NOT NULL,
            evaluation_point VARCHAR(100) NOT NULL,
            category VARCHAR(100) NOT NULL,
            measure VARCHAR(255) NOT NULL,
            pre_program DECIMAL(4,2) NULL,
            completion DECIMAL(4,2) NULL,
            delay DECIMAL(4,2) NULL,
            benchmark DECIMAL(4,2) NULL,
            change_value DECIMAL(4,2) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY participant_id (participant_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Retrieve evaluation records.
     *
     * @param int $limit Maximum number of records to retrieve.
     * @return array
     */
    public static function get_evaluations($limit = 50)
    {
        global $wpdb;

        $table_name = self::table_name();

        $limit = absint($limit);

        if ($limit < 1) {
            $limit = 50;
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }
}