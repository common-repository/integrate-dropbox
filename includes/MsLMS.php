<?php

namespace CodeConfig\IntegrateDropbox;

use CodeConfig\IntegrateDropbox\App\Database;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class MsLMS
{
    private static $instance = null;

    public function __construct()
    {
        if (! indbox_fs()->is_paying()) {
            return;
        }
        add_filter('ms_lms_course_builder_additional_scripts', [$this, 'ms_lms_course_builder_additional_scripts']);
        add_filter('ms_lms_course_builder_additional_styles', [$this, 'ms_lms_course_builder_additional_styles']);
        add_action('wp_ajax_indbox_set_master_lms_course_materials', [$this, 'indbox_set_master_lms_course_materials']);
    }

    public function indbox_set_master_lms_course_materials()
    {
        if (! isset($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = sanitize_text_field($_POST['nonce']);

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $postId = sanitize_text_field($_POST['id'] ?? null);
        $poster = Helpers::sanitization($_POST['poster'] ?? []);
        $video = Helpers::sanitization($_POST['video'] ?? []);
        $materials = Helpers::sanitization($_POST['materials'] ?? []);
        $video_id = $video['file_id'] ?? null;
        $account_id = $video['account_id'] ?? null;
        $poster_id = $poster['file_id'] ?? null;
        $folder_id = $poster['parent_id'] ?? null;

        if (empty($video_id) || empty($account_id)) {
            wp_send_json_error(['message' => __('Invalid video or account ID!', 'integrate-dropbox')], 400);
        }

        if (is_array($materials) && !empty($materials)) {
            $maid = [];
            foreach ($materials as $material) {
                $material_id = $material['file_id'] ?? null;
                $material_parent_id = $material['parent_id'] ?? null;

                if (!empty($material_id) && !empty($material_parent_id)) {
                    $mat_attachment_id = Database::is_attachment_exists($material_id, $material_parent_id);
                    if (!empty($mat_attachment_id)) {
                        $maid[] = $mat_attachment_id;
                    }
                }

            }

            $string_ids = "[" . implode(',', $maid) . "]";

            ob_start();
            echo '<pre>';
            var_dump( $string_ids );
            echo '</pre>';
            error_log( ob_get_clean() );

            if (!empty($maid)) {
                update_post_meta($postId, 'lesson_files', $string_ids);
            }
        }

        if (! empty($poster_id) && ! empty($folder_id)) {
            $attachment_id = Database::is_attachment_exists($poster['file_id'], $poster['parent_id']);

            if (! empty($attachment_id)) {
                update_post_meta($postId, 'lesson_video_poster', $attachment_id);
            }
        }

        $preview_nonce = wp_create_nonce('indbox-preview-url');
        $url_arg = [
            'action'     => 'indbox_preview_url',
            'id'         => $video_id,
            'account_id' => $account_id,
            'indbox'     => $preview_nonce,
        ];

        $preview_url = add_query_arg(
            $url_arg,
            admin_url('admin-ajax.php')
        );

        update_post_meta($postId, 'video_type', 'ext_link');
        update_post_meta($postId, 'lesson_ext_link_url', $preview_url);

        wp_send_json_success(['status' => 'ok']);

    }

    public function ms_lms_course_builder_additional_styles($urls)
    {
        $urls[] = INDBOX_ASSETS . '/vendor/sweetalert2/sweetalert2.min.css';
        $urls[] = INDBOX_ASSETS . '/admin/css/indbox-global.css';

        return $urls;
    }

    public function ms_lms_course_builder_additional_scripts($urls)
    {
        $urls[] = includes_url() . '/js/wp-util.min.js';
        $urls[] = INDBOX_ASSETS . '/vendor/sweetalert2/sweetalert2.min.js';
        $urls[] = INDBOX_ASSETS . '/admin/file-selector/file-selector.js';
        $urls[] = INDBOX_ASSETS . '/js/master-study-lms.js';
        return $urls;
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
