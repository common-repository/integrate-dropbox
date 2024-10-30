<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class Update_1_0_4
{
    private static $instance = null;

    public function __construct()
    {
        $this->add_table_column();
    }

    public function add_table_column()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'integrate_dropbox_files';

        $download_col_exists = $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'download'");
        $file_id_col_exists = $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'file_id'");

        if (empty($download_col_exists) && empty($file_id_col_exists)) {
            global $wpdb;

            $wpdb->query("ALTER TABLE {$wpdb->prefix}integrate_dropbox_files DROP INDEX `PRIMARY`;");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}integrate_dropbox_files CHANGE COLUMN `id` `file_id` VARCHAR(60);");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}integrate_dropbox_files ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}integrate_dropbox_files ADD COLUMN `download` LONGTEXT NULL AFTER `preview` ;");

        }
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            return self::$instance = new self();
        }

        return self::$instance;
    }
}

Update_1_0_4::instance();