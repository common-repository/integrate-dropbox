<?php

namespace CodeConfig\IntegrateDropbox\Elementor\Widgets;

defined('ABSPATH') or exit('Hey, what are you doing here?');

use CodeConfig\IntegrateDropbox\Shortcode;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

class SliderCarousel extends Widget_Base
{

    public function get_name()
    {
        return 'indbox_slider_carousel';
    }

    public function get_title()
    {
        return __('Slider Carousel', 'integrate-dropbox');
    }

    public function get_icon()
    {
        return 'indbox-slider indbox-pro';
    }

    public function is_editable()
    {
        global $indbox_fs;
        return $indbox_fs->can_use_premium_code();
    }

    public function get_categories()
    {
        return ['integrate_dropbox', 'basic'];
    }

    public function get_keywords()
    {
        return [
            "slider carousel",
            "dropbox",
            "shortcode",
            "module",
            "carousel",
        ];
    }

    public function get_script_depends()
    {
        return [
            'frontend_scripts',
        ];
    }

    public function get_style_depends()
    {
        return [
            'indbox-global-style',
            'indbox-elementor-style',
            'integrate-dropbox-admin-frontend',
        ];
    }

    public function register_controls()
    {

        $defaultData = '{"editData": {
            "status": "off",
            "title": "Shortcode Title",
            "type": "Slider Carousel",
            "allFolders": false,
            "folders": [],
            "privateFolders": false,
            "allowExtensions": null,
            "allowAllExtensions": false,
            "allowExceptExtensions": null,
            "allowNames": null,
            "allowAllNames": false,
            "allowExceptNames": null,
            "nameFilterOptions": [],
            "showFiles": true,
            "showFolders": true,
            "fileNumbers": "",
            "width": "100%",
            "height": "auto",
            "embedIframeWidth": "100%",
            "embedIframeHeight": "480px",
            "showFileName": false,
            "sort": {
              "sortBy": "Name",
              "sortDirection": "asc"
            },
            "view": "list",
            "layout": "Justified",
            "screenSize": "Desktop",
            "lazyLoad": true,
            "lazyloadnumber": 16,
            "maxFileUpload": null,
            "maxFileSize": "",
            "minFileSize": "",
            "allowUserFileDownload": true,
            "enableFolderUpload": false,
            "openNewTab": true,
            "showUploadLabel": true,
            "uploadLabelText": "Upload Files",
            "allowEmbedPopout": true,
            "thumbnailCaption": true,
            "preview": true,
            "download": true,
            "displayFor": "everyone",
            "displayUsers": ["everyone"],
            "displayEveryone": false,
            "displayExcept": [],
            "slideHeight": "360px",
            "sliderPerPage": 4,
            "slideScreenSize": "Large Desktop",
            "slidegap": 15,
            "slideloop": true,
            "SlideAutoplay": false,
            "slideAutoplaySpeed": 3000,
            "slideDotsNavigation": false,
            "slideArrowsNavigation": true,
            "large_desktop_sliderperpage": 5,
            "desktop_sliderperpage": 4,
            "laptop_sliderperpage": 3,
            "tablet_sliderperpage": 2,
            "mobile_sliderperpage": 1,
            "rowheight": 200,
            "imgmargin": 10,
            "mobilecolumn": 2,
            "tabletcolumn": 3,
            "desktopcolumn": 4,
            "aspectRatio": null,
            "allowFileUploadUserRole": [{ "value": "everyone", "label": "Everyone" }],
            "accessDeniedMessage": null,
            "whoCanViewModule": "Everyone",
            "uploadConfirmationMessage": null,
            "isShowUploadLabelText": false,
            "thumbnailView": "Rounded",
            "folderView": "Title",
            "browserHeader": true,
            "breadCrumbsInHeader": true,
            "whoCanViewModule": "Everyone"
        }}';

        $this->start_controls_section(
            '_section_module_builder',
            [
                'label' => __('Slider Carousel Module', 'integrate-dropbox'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        global $indbox_fs;
        if (!$indbox_fs->can_use_premium_code()) {
            $this->add_control('edit_module', [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf('<button class="elementor-button elementor-button-default"><span class="eicon-upgrade-crown" style="margin-right: 5px"></span> %s</button>', __('Upgrade to Pro', 'integrate-dropbox')),
                'description' => __('Use Slider Carousel widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'integrate-dropbox'),
            ]);

        } else {
            $this->add_control('module_data', [
                'label' => __('Module Data', 'integrate-dropbox'),
                'type' => Controls_Manager::HIDDEN,
                'default' => $defaultData,
            ]);

            // Edit button
            $this->add_control('edit_module', [
                'type' => Controls_Manager::BUTTON,
                'label' => '<span class="eicon eicon-settings" style="margin-right: 5px"></span>' . __('Configure Module', 'integrate-dropbox'),
                'text' => __('Configure', 'integrate-dropbox'),
                'event' => 'indbox:editor:edit_module',
                'description' => __('Configure the module first to display the content', 'integrate-dropbox'),
            ]);
        }

        $this->end_controls_section();
    }

    public function render()
    {
        global $indbox_fs;
        if (!$indbox_fs->can_use_premium_code()) {
            if (is_user_logged_in()) {
                wp_enqueue_style('integrate-dropbox-admin-frontend');
                printf('<div class="indbox-pro-module-wrapper"><h2>Slider Carousel Module - Premium Feature</h2><p>You are currently using the free license. To access this feature, you need to upgrade to a Pro license.</p> <a target="_blank" href="%s">Upgrade Now</a></div>', esc_url($indbox_fs->get_upgrade_url()));
            }
            return;
        }
        $settings = $this->get_settings_for_display();

        $settings_data = json_decode($settings['module_data'], true);

        $is_init = isset($settings_data['editData']['status']) && $settings_data['editData']['status'] == 'off';

        if ($is_init && Plugin::$instance->editor->is_edit_mode()) { ?>

                        <div class="indbox-toplavel-wrapper integrate-dropbox-intro-preview-wrapper">
                            <img src="<?php echo INDBOX_ASSETS . '/images/shortcode-builder/types/browser.svg' ?>">
                            <h3><?php _e('Slider Carousel', 'integrate-dropbox'); ?></h3>
                            <p><?php esc_html_e('Please, configure the module first to display the content', 'integrate-dropbox'); ?></p>
                            <button type="button" class="indbox-btn indbox-btn-filled"
                                onclick="setTimeout(() => {window.parent.jQuery(`[data-event='indbox:editor:edit_module']`).trigger('click')}, 100)">
                                <i class="dashicons dashicons-admin-generic"></i>
                                <span><?php esc_html_e('Configure Module', 'integrate-dropbox'); ?></span>
                            </button>
                        </div>
                <?php } else {
            echo Shortcode::instance()->render_shortcode([], $settings_data['editData']);
        }
    }

}
