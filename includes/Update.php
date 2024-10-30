<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class Update
{
    private static $instance = null;

    private static $update_list = [
        '1.0.1',
        '1.0.4',
        '1.1.0',
        '1.1.9',
    ];

    /**
     * Get the installed version
     *
     * @return string|bool
     */
    public function installed_version()
    {
        return get_option('integrate_dropbox_version');
    }

    public function is_update_needed($version = INDBOX_VERSION)
    {

        if (empty($this->installed_version())) {
            return false;
        }

        if (version_compare($this->installed_version(), $version, '<')) {
            return true;
        }

        return false;
    }

    public function perform_update()
    {
        foreach (self::$update_list as $version) {
            if ($this->is_update_needed($version)) {
                $file_path = INDBOX_INC . "/updates/class-update-$version.php";

                if (file_exists($file_path)) {
                    include_once $file_path;
                }

                update_option('integrate_dropbox_version', $version);
            }
        }

        delete_option('integrate_dropbox_version');
        update_option('integrate_dropbox_version', INDBOX_VERSION);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            return self::$instance = new self();
        }
    }
}
