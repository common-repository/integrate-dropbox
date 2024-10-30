<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\Admin;
use CodeConfig\IntegrateDropbox\Ajax;
use CodeConfig\IntegrateDropbox\App\App;
use CodeConfig\IntegrateDropbox\App\Authorization;
use CodeConfig\IntegrateDropbox\App\Processor;
use CodeConfig\IntegrateDropbox\AutoSync;
use CodeConfig\IntegrateDropbox\Install;
use CodeConfig\IntegrateDropbox\Integration;
use CodeConfig\IntegrateDropbox\Shortcode;
use MailPoetVendor\Doctrine\ORM\Query\Expr\Func;

/**
 * Integrate Dropbox
 */
final class Base
{
    /**
     * The single instance of the class.
     * @var
     * @since 1.0.0
     * @static
     */
    private static $instance = null;

    public function __construct()
    {
        $ajaxAction = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : null;

        if ($ajaxAction === 'indbox_authorization') {
            add_action('wp_ajax_nopriv_indbox_authorization', [$this, 'indbox_authorization']);
            add_action('wp_ajax_indbox_authorization', [$this, 'indbox_authorization']);
        } elseif ($ajaxAction === 'indbox_download_file') {
            Download::instance()->indbox_download_file();
        }

        $this->init_hooks();

        $this->init_includes();

        if (is_admin()) {
            $this->is_doing_oauth();
            new Admin();
        }

        $settings = Processor::instance()->get_setting('settings', false);

        if (isset($settings['enableAutoSynchronization']) && ! empty($settings['enableAutoSynchronization']) && $settings['enableAutoSynchronization'] !== 'false' && indbox_fs()->is_paying()) {
            AutoSync::instance();
        } else {
            $timestamp = wp_next_scheduled('indbox_corn_fire');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'indbox_corn_fire');
            }
        }

    }

    public function indbox_authorization()
    {
        $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : null;
        $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : null;

        if (empty($code) || empty($state)) {
            exit('You are not allowed on this page. Are you a chili human?');
        }

        $getStateData = explode('|', $state);
        $redirectURL = null;

        if (isset($getStateData[1])) {
            $decodedState = base64_decode(strtr($getStateData[1], '-_', '+/'));
            if ($decodedState === false) {
                exit('Invalid state encoding');
            }

            $explodedState = explode('&', $decodedState);

            if (count($explodedState) >= 2) {
                $nonce = isset($explodedState[3]) ? $explodedState[3] : '';

                $redirectURLParams = [
                    $explodedState[0],
                    $explodedState[1],
                    "code=" . urlencode($code),
                    "state=" . urlencode($state),
                    $nonce,
                ];

                $redirectURL = implode('&', $redirectURLParams);
            } else {
                exit('Invalid state format');
            }
        }

        if (esc_url($redirectURL)) {
            header("Content-Security-Policy: default-src 'self'");
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
            wp_safe_redirect($redirectURL);
            exit;
        } else {
            echo "Invalid redirect URL: " . esc_html($redirectURL);
        }
    }

    /**
     * Ensure Singleton instance in this class.
     * @return Base
     * @since 1.0.0
     * @static
     */
    public static function instance(): Base
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Includes required files
     * @return void
     * @since 1.0.0
     */
    private function init_includes(): void
    {
        require_once INDBOX_INC . "/functions.php";
        Shortcode::instance();
        Enqueue::instance();
        Ajax::instance();
        ShortcodeLocations::instance();
        if (Helpers::is_app_login()) {
            Integration::instance();
        }
    }

    /**
     * Add required action hooks
     * @return void
     * @since 1.0.0
     */
    private function init_hooks(): void
    {
        add_action('plugins_loaded', [$this, 'indbox_load_text_domain']);
        add_filter('plugin_row_meta', [$this, 'indbox_plugin_row_meta'], 10, 2);

        register_activation_hook(INDBOX_FILE, [$this, 'activate']);
        register_deactivation_hook(INDBOX_FILE, [$this, 'deactivate']);
    }

    public function activate()
    {
        Install::activate();
    }

    public function deactivate()
    {
        Install::deactivate();
    }

    public function indbox_plugin_row_meta($plugin_meta, $plugin_file)
    {
        if ($plugin_file == plugin_basename(INDBOX_FILE)) {
            $docsLink = '<a target="_blank" href="https://codeconfig.dev/docs-category/integrate-dropbox/">Docs & FAQs</a>';
            $videoLink = '<a target="_blank" href="https://www.youtube.com/playlist?list=PLNYVH9xXmhE2V0LGsUfab4Y_Hc6IXERwa">Video Tutorials</a>';
            array_push($plugin_meta, $docsLink, $videoLink);
        }
        return $plugin_meta;
    }

    /**
     * Load plugin textdomain
     * @return void
     * @since 1.0.0
     */
    public function indbox_load_text_domain(): void
    {
        load_plugin_textdomain('integrate-dropbox', false, dirname(plugin_basename(INDBOX_FILE)) . '/languages');
    }

    public function is_doing_oauth()
    {
        if (! isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] !== 'integrate-dropbox-authorization')) {
            return;
        }

        App::instance()->process_authorization();
    }
}
