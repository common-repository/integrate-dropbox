<?php

namespace CodeConfig\IntegrateDropbox\Elementor\Widgets;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;
use CodeConfig\IntegrateDropbox\Shortcode;

class Gallery extends Widget_Base
{

    public function get_name()
    {
        return 'indbox_gallery';
    }

    public function get_title()
    {
        return __('Gallery', 'integrate-dropbox');
    }

    public function get_icon()
    {
        return 'indbox-gallery';
    }

    public function get_categories()
    {
        return ['integrate_dropbox', 'basic'];
    }

    public function get_keywords()
    {
        return [
            "Gallery",
            "dropbox",
            "shortcode",
            "module",
            "gallery",
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
        ];
    }

    public function register_controls()
    {

        $defaultData = '{"editData": {
  "status": "off",
  "type": "Gallery",
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
  "layout": "Grid",
  "screenSize": "Desktop",
  "lazyLoad": true,
  "lazyloadnumber": 100,
  "maxFileUpload": null,
  "maxFileSize": "",
  "minFileSize": "",
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
  "sliderPerPage": 4,
  "slideScreenSize": "",
  "slideGap": "",
  "rowheight": 200,
  "imgmargin": 10,
  "mobilecolumn": 2,
  "tabletcolumn": 3,
  "desktopcolumn": 4,
  "allowFileUploadUserRole": [
    {
      "value": "everyone",
      "label": "Everyone"
    }
  ],
  "accessDeniedMessage": null,
  "uploadConfirmationMessage": null,
  "isShowUploadLabelText": false,
  "thumbnailView": "Rounded",
  "folderView": "Title",
  "whoCanViewModule": "Everyone"
}}';

        $this->start_controls_section(
            '_section_module_builder',
            [
                'label' => __('Gallery Module', 'integrate-dropbox'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control('module_data', [
            'label' => __('Module Data', 'integrate-dropbox'),
            'type' => Controls_Manager::HIDDEN,
            'default' => $defaultData,
        ]);

        //Edit button
        $this->add_control('edit_module', [
            'type' => Controls_Manager::BUTTON,
            'label' => '<span class="eicon eicon-settings" style="margin-right: 5px"></span>' . __('Configure  Module', 'integrate-dropbox'),
            'text' => __('Configure', 'integrate-dropbox'),
            'event' => 'indbox:editor:edit_module',
            'description' => __('Configure the module first to display the content', 'integrate-dropbox'),
        ]);

        $this->end_controls_section();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();

        $settings_data = json_decode($settings['module_data'], true);

        $is_init = isset($settings_data['editData']['status']) && $settings_data['editData']['status'] == 'off';

        if ($is_init && Plugin::$instance->editor->is_edit_mode()) { ?>

            <div class="indbox-toplavel-wrapper integrate-dropbox-intro-preview-wrapper">

                <img src="<?php echo INDBOX_ASSETS . '/images/shortcode-builder/types/gallery.svg' ?>">
                <h3><?php _e('Photo Gallery', 'integrate-dropbox'); ?></h3>
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
