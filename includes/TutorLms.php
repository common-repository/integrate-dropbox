<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use TUTOR\Input;

class TutorLms
{
    private static $instance = null;

    public function __construct()
    {
        add_filter('tutor_preferred_video_sources', [$this, 'tutor_preferred_video_sources']);
        add_action('tutor_after_video_source_icon', [$this, 'add_video_source_icon']);
        add_action('tutor_after_video_meta_box_item', [$this, 'tutor_after_video_meta_box_item'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 999);
        add_action('tutor_save_course_after', array( $this, 'save_course_video' ), 10, 2);
        add_action('save_post_' . \tutor()->lesson_post_type, [$this, 'save_lesson_meta']);

        // Attachments
        add_action('tutor_lesson_edit_modal_form_after', array( $this, 'render_attachment_field' ));
        add_action('tutor_global/after/attachments', array( $this, 'display_attachments' ));

    }


    public function tutor_preferred_video_sources($sources)
    {
        $sources['indbox'] = [
            'title' => __('Dropbox', 'integrate-dropbox'),
            'icon'  => 'tutor-icon-brand-dropbox',
        ];

        return $sources;
    }
    public function add_video_source_icon()
    {   ?>

<i class="tutor-icon-brand-dropbox" data-for="indbox"></i>

<?php
    }

    public function enqueue_admin_scripts()
    {

        if (empty(tutor_utils()->get_course_builder_screen())) {
            return;
        }

        if (! wp_script_is('indbox-tutor-lms')) {
            wp_enqueue_script('indbox-tutor-lms');
        }
    }

    public function tutor_after_video_meta_box_item($tutor_video_input_state, $post)
    {
        $videoData = maybe_unserialize(get_post_meta($post->ID, '_video', true));
        $videoSource = tutor_utils()->avalue_dot('source', $videoData);
        $posterIndbox = tutor_utils()->avalue_dot('poster_indbox', $videoData);
        $sourceIndbox = tutor_utils()->avalue_dot('source_indbox', $videoData);
        $nameIndbox = tutor_utils()->avalue_dot('name_indbox', $videoData);
        $sizeIndbox = tutor_utils()->avalue_dot('size_indbox', $videoData);
        $formattedSize = $sizeIndbox ? size_format($sizeIndbox, 2) : '';

        if(false === $videoData) {
            $supported_sources = tutor_utils()->get_option('supported_video_sources', array());

            if (is_string($supported_sources)) {
                $videoSource = $supported_sources;
            }
        }


        ?>

        <div class="tutor-mt-16 video-metabox-source-item video_source_wrap_indbox tutor-dashed-uploader <?php echo $sourceIndbox ? 'tutor-has-video' : ''; ?>"
            style="<?php tutor_video_input_state($videoSource, 'indbox');?>">

            <div class="video-metabox-source-indbox-upload">
                <p class="video-upload-icon"><i class="tutor-icon-upload-icon-line"></i></p>
                <p><strong><?php esc_html_e('Select Your Video', 'integrate-dropbox');?></strong>
                </p>
                <p><strong><?php esc_html_e('File Format: ', 'integrate-dropbox');?>
                    </strong><span class="tutor-color-black">mp4, m4v, ogg, ogv, webmv, mov</span>
                </p>

                <div class="video_source_upload_wrap_indbox">
                    <button class="indbox-tutor-button video_upload_btn tutor-btn tutor-btn-secondary tutor-btn-md">
                        <?php

                        $button_label = $sourceIndbox ? __('Replace Dropbox Files', 'integrate-dropbox') : __('Add Dropbox Files', 'integrate-dropbox');
        echo esc_html($button_label);

        ?>
                    </button>
                    <button class="indbox-tutor-remove-button video_remove_btn tutor-btn tutor-btn-danger tutor-btn-md"
                        style="display: <?php echo $sourceIndbox ? 'inline-flex' : 'none'; ?>">
                        <?php esc_html_e('Remove Video', 'integrate-dropbox'); ?>
                    </button>
                </div>
            </div>

            <div class="indbox-video-data"
                style="display: <?php echo $sourceIndbox ? 'block' : 'none'; ?>">

                <div class="tutor-col-lg-12 tutor-mb-16 tutor-mt-16">
                    <div class="tutor-card">
                        <div class="tutor-card-body">
                            <div class="tutor-row tutor-align-center">
                                <div class="tutor-col tutor-overflow-hidden">
                                    <div class="tutor-video-player">
                                        <video
                                            poster="<?php echo esc_url($posterIndbox); ?>"
                                            class="indbox-tutor-player tutorPlayer" playsinline controls>
                                            <source
                                                src="<?php echo esc_url($sourceIndbox); ?>"
                                                type="<?php echo esc_attr(tutor_utils()->avalue_dot('type', $videoData)); ?>">
                                        </video>
                                        <div
                                            class="video-data-title tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4">
                                            <?php echo esc_html($nameIndbox); ?>
                                        </div>
                                    </div>

                                    <?php if (! empty($formattedSize)): ?>
                                    <div class="tutor-fs-7 tutor-color-muted">
                                        <?php esc_html_e('Size', 'integrate-dropbox');?>:
                                        <span
                                            class="video-data-size"><?php echo esc_html($formattedSize); ?></span>
                                    </div>
                                    <?php endif;?>

                                    <input type="hidden" name="video[source_indbox]"
                                        value="<?php echo esc_attr($sourceIndbox); ?>">
                                    <input type="hidden" name="video[poster_indbox]"
                                        value="<?php echo esc_attr($posterIndbox); ?>">
                                    <input type="hidden" name="video[name_indbox]"
                                        value="<?php echo esc_attr($nameIndbox); ?>">
                                    <input type="hidden" name="video[size_indbox]"
                                        value="<?php echo esc_attr($sizeIndbox); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php

    }

    public function save_lesson_meta($post_ID)
    {
        $video_source = sanitize_text_field(tutor_utils()->array_get('video.source', $_POST));

        if ('-1' === $video_source) {
            delete_post_meta($post_ID, '_video');
        } elseif ($video_source) {

            // Sanitize data through helper method.
            $video = Input::sanitize_array(
                $_POST['video'] ?? array(),
                array(
                    'source_external_url' => 'esc_url',
                    'source_embedded'     => 'wp_kses_post',

                    'source_indbox' => 'esc_url',
                    'name_indbox'   => 'sanitize_text_field',
                    'size_indbox'   => 'sanitize_text_field',
                    'poster_indbox' => 'esc_url'
                ),
                true
            );

            update_post_meta($post_ID, '_video', $video);

        }

        $attachments = tutor_utils()->array_get('indbox_tutor_attachments', $_POST);

        if (empty($_POST['tutor_attachments']) && ! empty($attachments)) {
            $_POST['tutor_attachments'] = [ -1 ];

            update_post_meta($post_ID, '_tutor_attachments', $_POST['tutor_attachments']);

        } elseif (is_array($_POST['tutor_attachments']) && count($_POST['tutor_attachments']) == 1 && reset($_POST['tutor_attachments']) != '-1') {
            $_POST['tutor_attachments'] = array_filter($_POST['tutor_attachments'], function ($value) {
                return $value !== '-1';
            });

            update_post_meta($post_ID, '_tutor_attachments', $_POST['tutor_attachments']);
        }

        if (! empty($attachments)) {
            update_post_meta($post_ID, '_indbox_tutor_attachments', $attachments);
        } else {
            delete_post_meta($post_ID, '_indbox_tutor_attachments');
        }

    }

    public function save_course_video($post_ID, $post)
    {
        $additional_data_edit = Input::post('_tutor_course_additional_data_edit');

        // Additional data like course intro video.
        if ($additional_data_edit) {
            // Sanitize data through helper method.
            $video = Input::sanitize_array(
                $_POST['video'] ?? array(),
                array(
                    'source_embedded'     => 'wp_kses_post',
                    'source_external_url' => 'esc_url',

                    'source_indbox' => 'esc_url',
                    'name_indbox'   => 'sanitize_text_field',
                    'size_indbox'   => 'sanitize_text_field',
                    'poster_indbox'   => 'esc_url',
                ),
                true
            );

            $video_source = tutor_utils()->array_get('source', $video);


            if (- 1 !== $video_source) {

                // Override the tutor lms has_video_in_single condition check
                $is_empty = empty($video['source_video_id']) &&
                            empty($video['source_external_url']) &&
                            empty($video['source_youtube']) &&
                            empty($video['source_vimeo']) &&
                            empty($video['source_embedded']) &&
                            empty($video['source_shortcode']) &&
                            empty($video['source_bunnynet']);

                if ($is_empty) {
                    $video['source_external_url'] = $video['source_indbox'];
                }

                update_post_meta($post_ID, '_video', $video);
            } else {
                delete_post_meta($post_ID, '_video');
            }
        }

    }

    public function display_attachments()
    {

        $open_mode_view = apply_filters('tutor_pro_attachment_open_mode', null) == 'view' ? ' target="_blank" ' : null;

        $attachments = get_post_meta(get_the_ID(), '_indbox_tutor_attachments', true);
        $attachments = is_array($attachments) ? $attachments : array();

        if (! empty($attachments)) {
            ?>
			<div class="tutor-course-attachments tutor-row">
				<?php foreach ($attachments as $attachment) {
				    $download_link = admin_url("admin-ajax.php?action=indbox_download_file&id={$attachment['id']}&account_id={$attachment['accountId']}");

				    $size = size_format($attachment['size'], 2);

				    ?>
					<div class="tutor-col-md-6 tutor-mt-16">
						<div class="tutor-course-attachment tutor-card tutor-card-sm">
							<div class="tutor-card-body">
								<div class="tutor-row">
									<div class="tutor-col tutor-overflow-hidden">
										<div
											class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4"><?php echo esc_html($attachment['name']); ?></div>
										<div
											class="tutor-fs-7 tutor-color-muted"><?php esc_html_e('Size', 'integrate-google-drive'); ?>
											: <?php echo esc_html($size); ?></div>
									</div>

									<div class="tutor-col-auto">
										<a href="<?php echo esc_url_raw($download_link); ?>"
										   class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-stretched-link" <?php echo esc_attr($open_mode_view ? $open_mode_view : "download={$attachment['name']}"); ?>>
											<span class="tutor-icon-download" aria-hidden="true"></span>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>

			<style>
                .tutor-course-attachment:has([download="."]) {
                    display: none;
                }
			</style>

			<?php
        }
    }

    public function render_attachment_field($post)
    {
        $post_id = $post->ID;

        $attachments = get_post_meta($post_id, '_indbox_tutor_attachments', true);

        $attachments = is_array($attachments) ? $attachments : array();

        if (! empty($attachments)) {
            foreach ($attachments as $attachment) {
                printf('<input type="hidden" name="indbox_tutor_attachments[%s][id]" value="%s" />', $attachment['id'], $attachment['id']);
                printf('<input type="hidden" name="indbox_tutor_attachments[%s][accountId]" value="%s" />', $attachment['id'], $attachment['accountId']);
                printf('<input type="hidden" name="indbox_tutor_attachments[%s][name]" value="%s" />', $attachment['id'], $attachment['name']);
                printf('<input type="hidden" name="indbox_tutor_attachments[%s][size]" value="%s" />', $attachment['id'], $attachment['size']);
            }
        }
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
?>