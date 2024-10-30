<?php

namespace CodeConfig\IntegrateDropbox\Elementor;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\Elementor\Widgets\DownloadLinks;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\EmbedDocuments;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\FileBrowser;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\Gallery;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\Shortcodes;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\ViewLinks;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\SliderCarousel;
use CodeConfig\IntegrateDropbox\Elementor\Widgets\MediaPlayer;
use CodeConfig\IntegrateDropbox\Enqueue;

class Elementor
{
    /**
     * @var Elementor|null
     */
    protected static $instance = null;
    public function __construct()
    {

        add_action('elementor/frontend/before_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'editor_scripts']);
        // Register default widgets
        add_action('elementor/elements/categories_registered', [$this, 'add_categories']);

        if (defined('ELEMENTOR_VERSION')) {
            if (version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
                add_action('elementor/widgets/register', [$this, 'register_widgets']);
            } else {
                add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
            }
        }
        // add_filter( 'elementor/editor/localize_settings', [$this, 'promote_pro_elements'] );
    }

    public function frontend_scripts()
    {
        if (isset($_GET['elementor-preview'])) {
            Enqueue::instance()->admin_scripts('');
        }
        wp_register_script(
            'indbox-elementor',
            INDBOX_ASSETS . '/js/elementor.js',
            ['jquery', 'wp-element', 'wp-components', 'indbox-module-builder-script',],
            INDBOX_VERSION,
            true
        );
        global $indbox_fs;
        $isPro = $indbox_fs->is_paying();
        wp_localize_script('indbox-elementor', 'indbox', [
            'upgradeUrl' => esc_url($indbox_fs->get_upgrade_url()),
            'nonce' => wp_create_nonce('indbox-nonce'),
            'isPro' => $isPro,
        ]);
        wp_enqueue_script('indbox-elementor');
    }

    public function editor_scripts()
    {
        wp_enqueue_style('indbox-elementor-style', INDBOX_ASSETS . '/css/elementor-editor.css', [], INDBOX_VERSION);
    }
    public function register_widgets($widgets_manager)
    {
        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\Shortcodes')) {
            include_once INDBOX_INC . '/elementor/widgets/Shortcodes.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new Shortcodes());
        } else {
            $widgets_manager->register_widget_type(new Shortcodes());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\FileBrowser')) {
            include_once INDBOX_INC . '/elementor/widgets/FileBrowser.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new FileBrowser());
        } else {
            $widgets_manager->register_widget_type(new FileBrowser());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\EmbedDocuments')) {
            include_once INDBOX_INC . '/elementor/widgets/EmbedDocuments.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new EmbedDocuments());
        } else {
            $widgets_manager->register_widget_type(new EmbedDocuments());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\DownloadLinks')) {
            include_once INDBOX_INC . '/elementor/widgets/DownloadLinks.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new DownloadLinks());
        } else {
            $widgets_manager->register_widget_type(new DownloadLinks());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\ViewLinks')) {
            include_once INDBOX_INC . '/elementor/widgets/ViewLinks.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new ViewLinks());
        } else {
            $widgets_manager->register_widget_type(new ViewLinks());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\SliderCarousel')) {
            include_once INDBOX_INC . '/elementor/widgets/SliderCarousel.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new SliderCarousel());
        } else {
            $widgets_manager->register_widget_type(new SliderCarousel());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\MediaPlayer')) {
            include_once INDBOX_INC . '/elementor/widgets/MediaPlayer.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new MediaPlayer());
        } else {
            $widgets_manager->register_widget_type(new MediaPlayer());
        }

        if (!class_exists('CodeConfig\IntegrateDropbox\Elementor\Widgets\Gallery')) {
            include_once INDBOX_INC . '/elementor/widgets/Gallery.php';
        }
        if (method_exists($widgets_manager, 'register')) {
            $widgets_manager->register(new Gallery());
        } else {
            $widgets_manager->register_widget_type(new Gallery());
        }
    }

    public function add_categories($elements_manager)
    {
        $elements_manager->add_category('integrate_dropbox', [
            'title' => __('Integrate Dropbox', 'integrate_dropbox'),
            'icon' => 'fa fa-plug',
        ]);
    }

    /**
     * @return Elementor|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
