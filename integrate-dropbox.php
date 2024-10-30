<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

/*
 * Plugin Name:       Integrate Dropbox
 * Plugin URI:        https://codeconfig.dev/integrate-dropbox/
 * Description:       Integrate Dropbox: user-friendly WordPress plugin beautifully displays Dropbox files on posts, pages, & products.
 * Version:           1.1.10
 * Requires at least: 5.2
 * Requires PHP:      7.4.0
 * Author:            CodeConfig
 * Author URI:        https://codeconfig.dev/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       integrate-dropbox
 * Domain Path:       /languages
 */

if (function_exists('\CodeConfig\IntegrateDropbox\indbox_fs')) {
    indbox_fs()->set_basename(true, __FILE__);
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if (!function_exists('\CodeConfig\IntegrateDropbox\indbox_fs')) {
        // Create a helper function for easy SDK access.
        function indbox_fs()
        {
            global $indbox_fs;

            if (!isset($indbox_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';

                $indbox_fs = fs_dynamic_init([
                    'id' => '15531',
                    'slug' => 'integrate-dropbox',
                    'premium_slug' => 'integrate-dropbox-pro',
                    'type' => 'plugin',
                    'public_key' => 'pk_7b9c0e876c395a764dda52ddb28cd',
                    'is_premium' => true,
                    'premium_suffix' => 'PRO',
                    'has_premium_version' => false,
                    'has_addons' => false,
                    'has_paid_plans' => true,
                    'trial' => [
                        'days' => 7,
                        'is_require_payment' => false,
                    ],
                    'menu' => [
                        'slug' => 'integrate-dropbox',
                        'first-path' => 'admin.php?page=integrate-dropbox',
                    ],
                ]);
            }

            return $indbox_fs;
        }

        // Init Freemius.
        indbox_fs();
        // Signal that SDK was initiated.
        do_action('indbox_fs_loaded');
    }

    //  Define constant
    define('INDBOX_FILE', __FILE__);
    define('INDBOX_VERSION', '1.1.10');
    define('INDBOX_PATH', dirname(__FILE__));
    define('INDBOX_APP', INDBOX_PATH . '/app');
    define('INDBOX_URL', plugins_url('/', __FILE__));
    define('INDBOX_ASSETS', INDBOX_URL . 'assets');
    define('INDBOX_INC', INDBOX_PATH . '/includes');
    define('INDBOX_VENDOR', INDBOX_PATH . '/vendors');
    define('INDBOX_SETTINGS', 'integrate_dropbox_settings');
    define('INDBOX_CACHE_DIR', WP_CONTENT_DIR . '/integrate-dropbox-cache/');
    define('INDBOX_CACHE_URL', content_url() . '/integrate-dropbox-cache/');
    define('INDBOX_ICON_SET', INDBOX_ASSETS . '/admin/icons/');
    define('INDBOX_ADMIN_URL', admin_url('admin-ajax.php'));
    define('INDBOX_INTEGRATIONS', INDBOX_INC . '/integrations');

    require_once INDBOX_PATH . '/vendors/autoload.php';

    if (!class_exists('CodeConfig\\IntegrateDropbox\\Base')) {
        require_once INDBOX_INC . '/Base.php';
    }

    Base::instance();
}
