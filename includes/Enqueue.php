<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

/**
 * The enqueue class;
 */
class Enqueue
{
    /**
     * The class instance variable
     * @var null
     * @since 1.0.0
     * @static
     */
    protected static $instance = null;

    private $localize_data = [];

    /**
     * The construct function
     * @return void
     * @since 1.0.0
     */

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

    }

    /**
     * Enqueue frontend scripts
     * @return void
     * @since 1.0.0
     */
    public function frontend_scripts(): void
    {
        $this->common_enqueue_scripts(false, 'frontend');
        wp_enqueue_script(
            'integrate-dropbox-frontend',
            INDBOX_ASSETS . '/admin/frontend/frontend.js',
            ['indbox-script'],
            INDBOX_VERSION,
            true
        );
        wp_register_style(
            'integrate-dropbox-admin-frontend',
            INDBOX_ASSETS . '/css/admin-frontend.css',
            [],
            INDBOX_VERSION
        );
        wp_enqueue_style('integrate-dropbox-admin-frontend');
    }

    /**
     * Enqueue admin scripts
     * @return void
     * @since 1.0.0
     */
    public function admin_scripts($hook): void
    {
        $this->common_enqueue_scripts($hook);

        wp_register_script(
            'integrate-dropbox-file-browser',
            INDBOX_ASSETS . '/admin/file-browser/file-browser.js',
            ['indbox-script'],
            INDBOX_VERSION,
            true
        );

        wp_register_script(
            'integrate-dropbox-settings',
            INDBOX_ASSETS . '/admin/settings/settings.js',
            ['indbox-script'],
            INDBOX_VERSION,
            true
        );

        wp_register_script(
            'integrate-dropbox-shortcode-builder',
            INDBOX_ASSETS . '/admin/shortcode-builder/shortcode-builder.js',
            ['indbox-script'],
            INDBOX_VERSION,
            true
        );

        wp_register_style(
            'indbox-global-style',
            INDBOX_ASSETS . '/admin/css/indbox-global.css',
            [],
            INDBOX_VERSION,
            false
        );

        wp_register_style(
            'indbox-getting-started-css',
            INDBOX_ASSETS . '/admin/getting-started/getting-started.css',
            [],
            INDBOX_VERSION,
            false
        );

        wp_register_script(
            'indbox-getting-started-script',
            INDBOX_ASSETS . '/admin/getting-started/getting-started.js',
            [],
            INDBOX_VERSION,
            false
        );

        wp_register_script(
            'indbox-module-builder-script',
            INDBOX_ASSETS . '/admin/module-builder/module-builder.js',
            [],
            INDBOX_VERSION,
            false
        );
        wp_register_script(
            'indbox-file-selector-script',
            INDBOX_ASSETS . '/admin/file-selector/file-selector.js',
            [],
            INDBOX_VERSION,
            false
        );

        wp_register_style(
            'integrate-dropbox-admin-frontend',
            INDBOX_ASSETS . '/css/admin-frontend.css',
            [],
            INDBOX_VERSION
        );

        wp_register_script(
            'indbox-woocommerce',
            INDBOX_ASSETS . '/js/woocommerce.js',
            ['jquery', "indbox-file-selector-script"],
            INDBOX_VERSION,
            false
        );

        wp_register_script(
            'indbox-tutor-lms',
            INDBOX_ASSETS . '/js/tutor.js',
            ['jquery', "indbox-file-selector-script"],
            INDBOX_VERSION,
            false
        );

        if ($hook === 'post.php' && $isWC = Integration::instance()->is_active('woocommerce')) {
            $post_id = isset($_GET['post']) ? sanitize_text_field($_GET['post']) : null;


            if (!empty($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type === 'product') {
                    $this->update_localize_data('isWooCommerceDownloadEnabled', $isWC);
                    $woocommerce_file_download_method = get_option('woocommerce_file_download_method');
                    $this->update_localize_data('woocommerce_file_download_method', $woocommerce_file_download_method);
                    wp_enqueue_script('indbox-woocommerce');
                }
            }
        }

        wp_enqueue_style('indbox-global-style');
    }

    private function common_enqueue_scripts($hook = false, $script = 'admin')
    {
        wp_enqueue_script(
            'indbox-script',
            INDBOX_ASSETS . '/common/js/common.js',
            ['wp-util'],
            INDBOX_VERSION,
            true
        );

        // SweetAlert2 js
        wp_register_script(
            'integrate-dropbox-sweet-alert2-scripts',
            INDBOX_ASSETS
                . '/vendor/sweetalert2/sweetalert2.min.js',
            [],
            '11.4.8'
        );

        wp_enqueue_script('integrate-dropbox-sweet-alert2-scripts');

        // SweetAlert2 CSS
        wp_register_style(
            'integrate-dropbox-sweet-alert2-style',
            INDBOX_ASSETS
                . '/vendor/sweetalert2/sweetalert2.min.css',
            [],
            '11.4.8'
        );

        wp_enqueue_style('integrate-dropbox-sweet-alert2-style');

        wp_localize_script('indbox-script', 'indbox', $this->get_localize_data($hook, $script));
    }

    private function update_localize_data($key, $value = null)
    {
        if (is_string($key)) {
            $this->localize_data[$key] = $value;
        } elseif (is_array($key) && empty($value)) {
            $this->localize_data = array_merge($this->localize_data, $key);
        }

        return $this->localize_data;
    }

    public function get_localize_data($hook = false, $script = 'admin', $additional_data = [])
    {
        global $indbox_fs;
        $isPro = $indbox_fs->is_paying();
        $localize_data = [
            'nonce' => wp_create_nonce('indbox-nonce'),
            'isPro' => $isPro,
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ];

        $this->update_localize_data($localize_data);

        // $localize_data = wp_parse_args( $additional_data, $this->localize_data );
        // $localize_data = array_merge( $localize_data, $additional_data );

        if (isset($this->localize_data['isPro']) && empty($this->localize_data['isPro'])) {
            // $this->localize_data['upgradeUrl'] =  );
            $this->update_localize_data('upgradeUrl', esc_url($indbox_fs->get_upgrade_url()));
        }

        return apply_filters('indbox_localize_data', $this->localize_data, $script, $hook);
    }

    /**
     *  The class singleton instance.
     * @return Enqueue|null
     * @since 1.0.0
     * @static
     */
    public static function instance(): ?Enqueue
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
