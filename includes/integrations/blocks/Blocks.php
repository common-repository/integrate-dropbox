<?php

namespace CodeConfig\IntegrateDropbox\Integrations\Blocks;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\Shortcode;

class Blocks
{
    /**
     * @var null
     */
    protected static $instance = null;

    public function __construct()
    {
        add_action('init', [$this, 'register_gutenberg_blocks']);
        add_filter('block_categories_all', [$this, 'add_custom_block_category'], 10, 2);

        global $indbox_fs;
        if (!$indbox_fs->is_paying()) {
            add_action('admin_print_styles', [$this, 'add_pro_styles']);
        }
    }

    public function add_pro_styles()
    {
        ?>
        <style>
            .editor-block-list-item-integrate-dropbox-file-browser:before,
            .editor-block-list-item-integrate-dropbox-slider-carousel:before {
                content: '\f160';
                font-family: 'dashicons', serif;
                font-size: 20px;
                color: #7badff;
                position: absolute;
                top: 5px;
                right: 5px;
            }
        </style>
        <?php
    }

    public function register_gutenberg_blocks()
    {

        wp_enqueue_script('indbox-script');

        $blocks = [
            'gallery',
            'view-links',
            'embed-documents',
            'download-links',
            'file-browser',
            'slider-carousel',
            'shortcode-module',
        ];
        // Register all blocks
        foreach ($blocks as $block) {

            register_block_type(INDBOX_INTEGRATIONS . '/blocks/' . $block, [
                'render_callback' => [$this, 'render_blocks'],
            ]);
        }
    }

    public function render_blocks($block_att)
    {
        $shortcode_instance = ShortCode::instance();

        if ($block_att['editData']['type'] === 'Shortcode Module') {

            $atts = ['id' => $block_att['editData']['id']];
            $html = $shortcode_instance->render_shortcode($atts, []);
        } else {
            $atts = $block_att['editData'];
            $html = $shortcode_instance->render_shortcode([], $atts);
        }

        return $html;
    }


    /**
     * Add custom block category
     *
     * @param array $categories Array of block categories.
     * @param WP_Block_Editor_Context $block_editor_context Context in which to get block categories.
     * @return array Modified array of block categories.
     */
    public function add_custom_block_category($categories)
    {

        array_unshift($categories, [
            'slug' => 'integrate-dropbox',
            'title' => __('Integrate Dropbox', 'integrate-dropbox-pro'),
        ]);

        return $categories;
    }

    /**
     * @return Blocks|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
