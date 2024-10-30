<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

/**
 * The Plugin Admin Class
 * @since 1.0.0
 */
class Admin
{
    /**
     * The single instance of the class.
     * @since 1.0.0
     * @static
     * @var
     */
    private static $instance = null;

    /**
     * The Admin Pages
     * @since 1.0.0
     * @static
     * @var
     */
    public static $admin_pages = [];

    /**
     * The class construct function
     * @return void
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_update']);
    }

    public function add_dropbox_submenu($page_name, $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null)
    {
        return self::$admin_pages[$page_name] = add_submenu_page(
            $parent_slug,
            $page_title,
            $menu_title,
            $capability,
            $menu_slug,
            $callback,
            $position
        );
    }

    /**1115,1145,
     * Add admin menu
     * @return void
     * @since 1.0.0
     */
    public function add_admin_menu(): void
    {

        add_menu_page(
            'Integrate Dropbox',
            'Dropbox',
            'manage_options',
            'integrate-dropbox',
            [
                $this,
                'file_browser',
            ],
            INDBOX_ASSETS . '/admin/images/dropbox_logo_small.png',
            30
        );

        $this->add_dropbox_submenu(
            'file-browser',
            'integrate-dropbox',
            __("File Browser - Integrate Dropbox", "integrate-dropbox"),
            __("File Browser", "integrate-dropbox"),
            'manage_options',
            'integrate-dropbox',
            [$this, 'file_browser']
        );

        $this->add_dropbox_submenu(
            'settings',
            'integrate-dropbox',
            __('Settings - Integrate Dropbox', 'integrate-dropbox'),
            __('Settings', 'integrate-dropbox'),
            'manage_options',
            'integrate-dropbox-settings',
            [
                $this,
                'settings',
            ]
        );

        $this->add_dropbox_submenu(
            'shortcode-builder',
            'integrate-dropbox',
            __('Shortcode Builder - Integrate Dropbox', 'integrate-dropbox'),
            __('Shortcode Builder', 'integrate-dropbox'),
            'manage_options',
            'integrate-dropbox-shortcode-builder',
            [
                $this,
                'shortcode_builder',
            ]
        );

        $this->add_dropbox_submenu(
            'getting-started',
            'integrate-dropbox',
            __('Getting Started - Integrate Dropbox', 'integrate-dropbox'),
            __('Getting Started', 'integrate-dropbox'),
            'manage_options',
            'integrate-dropbox-getting-started',
            [
                $this,
                'getting_started',
            ]
        );

        do_action('indbox_add_submenu_page', $this);
    }

    public function getting_started()
    {
        $path = INDBOX_INC . '/views/getting-started.php';

        if (file_exists($path)) {
            echo '<div class="indbox-toplavel-wrapper getting-started">';
            include $path;
            echo '</div>';
        } else {
            echo '<p>File not found: ' . esc_url($path) . '</p>';
        }
    }

    public function file_browser()
    {
        echo '<div class="indbox-toplavel-wrapper" id="integrate-dropbox-file-browser"></div>';

        wp_enqueue_script('integrate-dropbox-file-browser');
    }

    /**
     * The Dropbox admin menu page.
     * @return void
     * @since 1.0.0
     */
    public function settings(): void
    {
        printf('<div class="indbox-toplavel-wrapper" id="integrate-dropbox-settings"></div>');
        wp_enqueue_script('integrate-dropbox-settings');
    }

    /**
     * The Dropbox admin menu page.
     * @return void
     * @since 1.0.0
     */
    public function shortcode_builder(): void
    {
        printf('<div class="indbox-toplavel-wrapper" id="integrate-dropbox-shortcode-builder"></div>');
        wp_enqueue_script('integrate-dropbox-shortcode-builder');
        wp_enqueue_editor();
    }

    /**
     * Get admin menu pages.
     * @return array
     * @since 1.0.0
     * @static
     */
    public static function get_admin_pages(): array
    {
        return self::$admin_pages;
    }

    /**
     * If needs update to perform update
     * @return void
     * @since 1.0.1
     */
    public function init_update()
    {

        if (current_user_can('manage_options')) {
            if (!class_exists('CodeConfig\\IntegrateDropbox\\Update')) {
                include_once INDBOX_INC . '/Update.php';
            }

            $updater = Update::instance();
            if ($updater->is_update_needed()) {
                $updater->perform_update();
            }
        }
    }

    /**
     * The instantiate singleton class.
     * @return Admin
     * @since 1.0.0
     * @static
     */
    public static function instance(): Admin
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
