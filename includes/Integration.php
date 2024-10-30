<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\Processor;
use CodeConfig\IntegrateDropbox\Elementor\Elementor;
use CodeConfig\IntegrateDropbox\Integrations\Blocks\Blocks;

class Integration
{
    private static $instance = null;
    private $settings;

    public function __construct()
    {
        $this->settings = Processor::instance()->get_setting('settings', []);

        if ($this->is_active('gutenberg-editor')) {
            if (! class_exists('CodeConfig\\IntegrateDropbox\\Integrations\\Gutenberg\\Blocks\\Blocks')) {
                require_once INDBOX_INC . '/integrations/blocks/Blocks.php';
            }
            Blocks::instance();
        }

        if ($this->is_active('media-library')) {
            if (! class_exists('CodeConfig\\IntegrateDropbox\\MediaLibrary')) {
                require_once INDBOX_INC . '/MediaLibrary.php';
            }
            MediaLibrary::instance();
        } else {
            add_action('pre_get_posts', [$this, 'filter_grid_attachments']);
        }

        if ($this->is_active('elementor')) {
            if (! class_exists('CodeConfig\\IntegrateDropbox\\Elementor\\Elementor')) {
                require_once INDBOX_INC . '/elementor/Elementor.php';
            }
            if (class_exists('Elementor\\Plugin')) {
                Elementor::instance();
            }
        }

        if (indbox_fs()->is_paying()) {
            if ($this->is_active('woocommerce')) {
                $this->initializeIntegration('woocommerce_loaded', WooCommerce::class);
            }

            if ($this->is_active('master-study-lms')) {
                $this->initializeIntegration('masterstudy_lms_plugin_loaded', MsLMS::class);
            }

            if ($this->is_active('tutor-lms')) {
                $this->initializeIntegration('tutor_loaded', TutorLms::class);
            }
        }

    }

    public function is_active($key)
    {
        $active_integrations = isset($this->settings['activeIntegration']) ? $this->settings['activeIntegration'] : [];

        return in_array($key, $active_integrations, true);
    }

    public static function instance(): Integration
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function filter_grid_attachments($query)
    {

        if (! isset($query->query_vars['post_type']) || 'attachment' !== $query->query_vars['post_type']) {
            return $query;
        }

        if (empty($_REQUEST['query'])) {
            return $query;
        }

        $meta_query = $query->get('meta_query') ?: [];

        $meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => '_indbox_media_folder_id',
                'compare' => 'NOT EXISTS',
            ],
        ];

        $query->set('meta_query', $meta_query);

        return $query;
    }

    private function initializeIntegration($hook, $class)
    {
        add_action($hook, function () use ($class) {
            $class::instance();
        });
    }
}
