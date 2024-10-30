<?php

namespace CodeConfig\IntegrateDropbox;

defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class Install {

    public static function activate() {
        if ( ! class_exists( 'CodeConfig\\IntegrateDropbox\\Update' ) ) {
            require_once INDBOX_INC . '/Update.php';
        }

        $updater = new Update();
        if ( $updater->is_update_needed() ) {
            $updater->perform_update();

        } else {
            self::create_tables();
            self::create_default_data();
            self::add_settings();
            self::create_cache_folder();
        }
    }

    public static function deactivate() {
        $timestamp = wp_next_scheduled( 'indbox_corn_fire' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'indbox_corn_fire' );
        }
    }

    private static function create_cache_folder() {
        global $wp_filesystem;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();

        if ( ! is_object( $wp_filesystem ) ) {
            return;
        }

        $cache_dir = INDBOX_CACHE_DIR;

        if ( ! $wp_filesystem->is_dir( $cache_dir ) ) {
            if ( ! $wp_filesystem->mkdir( $cache_dir, 0755 ) ) {
                error_log( "Failed to create cache directory: " . $cache_dir );
            } else {
                exec( "fsutil file setCaseSensitiveInfo " . escapeshellarg( $cache_dir ) . " enable", $output, $return_var );
                if ( $return_var !== 0 ) {
                    error_log( "Failed to set case sensitivity on cache directory: " . $cache_dir );
                }
            }
        }

        if ( ! $wp_filesystem->is_writable( $cache_dir ) ) {
            if ( ! $wp_filesystem->chmod( $cache_dir, 0755 ) ) {
                error_log( "Failed to set permissions on cache directory: " . $cache_dir );
            }
        }

    }

    private static function create_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tables = [
            // Shortcode List
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_dropbox_shortcodes( id BIGINT(20) NOT NULL AUTO_INCREMENT, title VARCHAR(255) NULL, status VARCHAR(6) NULL DEFAULT 'on', config LONGTEXT NULL, locations LONGTEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            // Dropbox files
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}integrate_dropbox_files( id INT AUTO_INCREMENT, `file_id` VARCHAR(60) COLLATE utf8mb4_bin NOT NULL, `name` TEXT NULL, `size` BIGINT NULL, `parent_id` TEXT, `account_id` TEXT NOT NULL, `type` VARCHAR(255) NOT NULL, `extension` VARCHAR(10) NOT NULL, `thumbnail` VARCHAR(255) NULL, `thumbnail_size` VARCHAR(10) NULL, `preview` LONGTEXT NULL, `download` LONGTEXT NULL, `data` LONGTEXT, is_computers TINYINT(1) DEFAULT 0, is_shared_with_me TINYINT(1) DEFAULT 0, is_starred TINYINT(1) DEFAULT 0, is_shared_drive TINYINT(1) DEFAULT 0, `created` TEXT NULL, `updated` TEXT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        ];

        foreach ( $tables as $table ) {
            dbDelta( $table );
        }
    }

    private static function add_settings() {
        $integrate_dropbox_settings = get_option( 'integrate_dropbox_settings' );

        if ( ! $integrate_dropbox_settings ) {

            $default_settings = [
                'accounts' => [],
                'settings' => [
                    'activeIntegration' => ["gutenberg-editor", "elementor", "media-library"],
                ],
            ];
            update_option( 'integrate_dropbox_settings', $default_settings );
        }
    }

    private static function create_default_data() {

        $integrate_dropbox_install_time = get_option( 'integrate_dropbox_install_time' );

        if ( ! $integrate_dropbox_install_time ) {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            update_option( 'integrate_dropbox_install_time', gmdate( $date_format . ' ' . $time_format ) );
        }

        $version = get_option( 'integrate_dropbox_version', '0' );
        if ( version_compare( $version, INDBOX_VERSION, '<' ) ) {
            update_option( 'integrate_dropbox_version', INDBOX_VERSION );
        }
    }
}