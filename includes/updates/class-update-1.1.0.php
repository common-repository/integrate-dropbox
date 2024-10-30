<?php

namespace CodeConfig\IntegrateDropbox;
defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

use CodeConfig\IntegrateDropbox\App\App;

class Update_1_1_0 {
    private static $instance = null;

    public function __construct() {
        $this->update_table_column();
        $this->force_logout();
    }

    public function force_logout() {
        $current_account = App::get_current_account();
        App::instance()->revoke_token( $current_account );
    }

    public function update_table_column() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'integrate_dropbox_files';

        $sql = "ALTER TABLE {$wpdb->prefix}integrate_dropbox_files ";

        $thumbnail = $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'thumbnail'" );
        if ( ! empty( $thumbnail ) ) {
            $sql .= "CHANGE COLUMN `thumbnail` `thumbnail` VARCHAR(255) COLLATE 'utf8mb4_0900_ai_ci' NULL AFTER `extension`,";
        }

        $thumbnail_size = $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'thumbnail_size'" );
        if ( ! empty( $thumbnail_size ) ) {
            $sql .= "CHANGE COLUMN `thumbnail_size` `thumbnail_size` VARCHAR(20) COLLATE 'utf8mb4_bin' NULL AFTER `thumbnail`,";
        }

        $preview = $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'preview'" );
        if ( ! empty( $preview ) ) {
            $sql .= "CHANGE COLUMN `preview` `preview` longtext COLLATE 'utf8mb4_bin' NULL AFTER `thumbnail_size`,";
        }

        $file_id_col_exists = $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'file_id'" );

        if ( ! empty( $file_id_col_exists ) ) {
            $sql .= "CHANGE COLUMN `file_id` `file_id` VARCHAR(60) COLLATE 'utf8mb4_bin' NOT NULL AFTER `id`,";
        }

        if ( ! empty( $sql ) ) {
            $sql = substr( $sql, 0, -1 );
            $sql .= " ;";
            $wpdb->query( $sql );
        }
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            return self::$instance = new self();
        }

        return self::$instance;
    }
}

Update_1_1_0::instance();