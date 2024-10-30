<?php

namespace CodeConfig\IntegrateDropbox\Elementor\Widgets;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

class Shortcodes extends Widget_Base
{

    public function get_name()
    {
        return 'indbox_shortcodes';
    }

    public function get_title()
    {
        return __('Module Shortcodes', 'integrate-dropbox');
    }

    public function get_icon()
    {
        return 'indbox-shortcodes';
    }

    public function get_categories()
    {
        return ['integrate_dropbox'];
    }

    public function get_keywords()
    {
        return [
            "integrate dropbox",
            "dropbox",
            "shortcode",
            "module",
            "cloud",
        ];
    }

    public function register_controls()
    {

        $this->start_controls_section(
            '_section_module_shortcodes',
            [
                'label' => __('Module Shortcode', 'integrate-dropbox'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'shortcode_id',
            [
                'label' => __('Select Shortcode Module', 'integrate-dropbox'),
                'type' => Controls_Manager::SELECT,
                'label_block' => true,
                'options' => indbox_get_shortcodes_array(),
            ]
        );

        $this->end_controls_section();
    }

    public function render()
    {
        // $render_preview = Shortcode::instance()->render_shortcode();
        $settings = $this->get_settings_for_display();
        $shortcode_id = isset($settings['shortcode_id']) ? $settings['shortcode_id'] : null;

        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $shortcode_content = do_shortcode('[integrate_dropbox id="' . $shortcode_id . '"]');
        // echo $shortcode_content;
        if (!empty($shortcode_id)) {
            if ($is_editor) {
                echo $shortcode_content;
            } else {
                echo $shortcode_content;
            }
        } else {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) { ?>
                <div class="indbox-toplavel-wrapper integrate-dropbox-intro-preview-wrapper">
                    <img src="<?php echo INDBOX_ASSETS . '/images/shortcode-builder/types/shortcodes.svg' ?>" class="module-shortcodes"
                        alt="Module Shortcodes">

                    <h3><?php esc_html_e('Dropbox Module Shortcodes', 'integrate-dropbox'); ?></h3>
                    <p><?php esc_html_e('Please select a shortcode from the widget settings.', 'integrate-dropbox'); ?></p>
                </div>
            <?php }
        }
    }
}
