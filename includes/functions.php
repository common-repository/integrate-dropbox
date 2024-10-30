<?php
use CodeConfig\IntegrateDropbox\App\ShortcodeBuilder;

function indbox_get_shortcodes_array()
{
    $shortcodes = ShortcodeBuilder::instance()->get_shortcodes();

    $formatted = [0 => __('Select Shortcode', 'integrate-dropbox')];

    if (! empty($shortcodes)) {
        foreach ($shortcodes as $shortcode) {
            if ($shortcode->status === 'on') {
                $formatted[$shortcode->id] = $shortcode->title;
            } else {
                $formatted[$shortcode->id] = $shortcode->title . ' (Disabled)';
            }
        }
    }

    return $formatted;
}

if (! function_exists('tutor_get_template')) {
    /**
     * Load template with override file system
     *
     * @param null $template template.
     * @param bool $tutor_pro is tutor pro.
     *
     * @return bool|string
     */
    function tutor_get_template($template = null, $tutor_pro = false)
    {
        if (! $template) {
            return false;
        }
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);

        /**
         * Get template first from child-theme if exists
         * If child theme not exists, then get template from parent theme
         **/
        $template_location = trailingslashit(get_stylesheet_directory()) . "tutor/{$template}.php";
        if (! file_exists($template_location)) {
            $template_location = trailingslashit(get_template_directory()) . "tutor/{$template}.php";
        }
        $file_in_theme = $template_location;
        if (! file_exists($template_location)) {
            $template_location = trailingslashit(tutor()->path) . "templates/{$template}.php";

            if ($tutor_pro && function_exists('tutor_pro')) {
                $pro_template_location = trailingslashit(tutor_pro()->path) . "templates/{$template}.php";
                if (file_exists($pro_template_location)) {
                    $template_location = trailingslashit(tutor_pro()->path) . "templates/{$template}.php";
                }
            }

            if (! file_exists($template_location) && strpos($template, 'indbox') !== false) {
                $template_location = trailingslashit(INDBOX_INC) . "templates/tutor/{$template}.php";
            }

            if (! file_exists($template_location)) {
                $warning_msg = __('The file you are trying to load does not exist in your theme or Tutor LMS plugin location. If you are extending the Tutor LMS plugin, please create a php file here: ', 'integrate-dropbox');
                $warning_msg = $warning_msg . "<code>$file_in_theme</code>";
                $warning_msg = apply_filters('tutor_not_found_template_warning_msg', $warning_msg);
                echo wp_kses($warning_msg, ['code' => true]);
                ?>
<?php

            }
        }

        return apply_filters('tutor_get_template_path', $template_location, $template);
    }
}
?>