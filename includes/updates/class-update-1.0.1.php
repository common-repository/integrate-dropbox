<?php

namespace CodeConfig\IntegrateDropbox;

defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class Update_1_0_1 {
    private static $instance = null;

    public function __construct() {
        $this->create_table();
    }

    public function create_table() {
        global $wpdb;

        $wpdb->hide_errors();

        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_dropbox_files( id VARCHAR(60) NOT NULL, `name` TEXT NULL, `size` BIGINT NULL, `parent_id` TEXT, `account_id` TEXT NOT NULL, `type` VARCHAR(255) NOT NULL, `extension` VARCHAR(10) NOT NULL, `thumbnail` VARCHAR(255) NOT NULL, `thumbnail_size` VARCHAR(10) NOT NULL, `preview` VARCHAR(255) NOT NULL, `data` LONGTEXT, is_computers TINYINT(1) DEFAULT 0, is_shared_with_me TINYINT(1) DEFAULT 0, is_starred TINYINT(1) DEFAULT 0, is_shared_drive TINYINT(1) DEFAULT 0, `created` TEXT NULL, `updated` TEXT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            return self::$instance = new self();
        }

        return self::$instance;
    }
}

Update_1_0_1::instance();