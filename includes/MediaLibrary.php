<?php

namespace CodeConfig\IntegrateDropbox;

use CodeConfig\IntegrateDropbox\App\App;
use CodeConfig\IntegrateDropbox\App\Database;
use CodeConfig\IntegrateDropbox\App\Processor;

defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class MediaLibrary {

    private static $instance;
    private $settings = null;

    public function __construct() {
        $this->settings = Processor::instance()->get_setting( 'settings', [] );
        add_action( 'wp_ajax_get_current_account', [$this, 'get_current_account'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'wp_enqueue_media', [$this, 'enqueue_scripts'] );
        add_action( 'indbox_insert_attachment', [$this, 'insert_attachment'], 10, 2 );
        add_action( 'pre_get_posts', [$this, 'filter_grid_attachments'] );
        // add_action( 'pre_get_posts', [$this, 'filter_list_attachments'] );
        add_filter( 'wp_prepare_attachment_for_js', [$this, 'filter_attachment_data'], 99, 3 );
        add_filter( 'wp_get_attachment_url', [$this, 'filter_attachment_url'], 999, 2 );
        add_filter( 'wp_get_attachment_image_src', [$this, 'filter_image_src'], 10, 4 );
        add_filter( 'indbox_localize_data', [$this, 'localize_data'], 10, 2 );
        add_filter( 'wp_calculate_image_srcset_meta', [$this, 'calculate_image_srcset_meta'], 10, 4 );
    }

    public function get_current_account() {
        $nonce = $_POST['nonce'] ? sanitize_text_field( $_POST['nonce'] ) : null;

        if ( ! wp_verify_nonce( $nonce, 'indbox-nonce' ) ) {
            wp_send_json_error( ['message' => 'Unauthorized Request!'], 401 );
        }

        $account_info = self::get_current_account_info();

        wp_send_json_success( $account_info );
    }

    public function calculate_image_srcset_meta( $image_meta, $size_array, $image_src, $attachment_id ) {
        $file_id = get_post_meta( $attachment_id, '_indbox_media_file_id', true );

        if ( ! empty( $file_id ) ) {
            $account_id = get_post_meta( $attachment_id, '_indbox_media_account_id', true );
            $nonce = wp_create_nonce( 'indbox-preview-nonce' );

            $url = add_query_arg(
                [
                    'action' => 'indbox_preview_url',
                    'id' => $file_id,
                    'account_id' => $account_id,
                    'indbox' => $nonce
                ]
                ,admin_url( 'admin-ajax.php' ));
                
            $image_meta['sizes']['full']['width'] = isset( $image_meta['width'] ) ? $image_meta['width'] : 60;
            $image_meta['sizes']['full']['height'] = isset( $image_meta['height'] ) ? $image_meta['height'] : 60;
            $image_meta['sizes']['full']['file'] = $url;
        }
        return $image_meta;
    }

    public function enqueue_scripts( $hook ) {
        wp_enqueue_style( 'indbox-media-library', INDBOX_ASSETS . '/css/media-library.css', [], INDBOX_VERSION );

        if ( ! Helpers::user_can_access( 'media_library' ) ) {
            global $indbox_fs;
            if ( ! $indbox_fs->is_paying() ) {
                wp_enqueue_script( 'indbox-media-library', INDBOX_ASSETS . '/js/media-library.js', [
                    'jquery',
                    'integrate-dropbox-sweet-alert2-scripts',
                ], INDBOX_VERSION, true );
            }
            return;
        }

        // Enqueue admin scripts
        Enqueue::instance()->admin_scripts( '' );

        wp_enqueue_style( 'indbox-media-library', INDBOX_ASSETS . '/css/media-library.css', [], INDBOX_VERSION );

        wp_enqueue_media();

        wp_enqueue_script( 'indbox-media-library', INDBOX_ASSETS . '/media-library/index.js', [
            'jquery',
            'wp-element',
            'wp-util',
        ], INDBOX_VERSION, true );
    }

    public function filter_grid_attachments( $query ) {

        if ( ! isset( $query->query_vars['post_type'] ) || 'attachment' !== $query->query_vars['post_type'] ) {
            return $query;
        }

        if ( empty( $_REQUEST['query'] ) ) {
            return $query;
        }

        $folder_id = ! empty( $_REQUEST['query']['folder_id'] ) ? sanitize_text_field( $_REQUEST['query']['folder_id'] ) : '';
        $account_id = ! empty( $_REQUEST['query']['account_id'] ) ? sanitize_text_field( $_REQUEST['query']['account_id'] ) : '';
        $is_refresh = ! empty( $_REQUEST['query']['is_refresh'] ) && filter_var( $_REQUEST['query']['is_refresh'], FILTER_VALIDATE_BOOLEAN );

        $meta_query = $query->get( 'meta_query' ) ?: [];

        if ( ! empty( $folder_id ) ) {
            $meta_query[] = [
                'key'     => '_indbox_media_folder_id',
                'value'   => $folder_id,
                'compare' => '=',
            ];
            $meta_query[] = [
                'key'     => '_indbox_media_account_id',
                'value'   => $account_id,
                'compare' => '=',
            ];
        } else {
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => '_indbox_media_folder_id',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        }

        $query->set( 'meta_query', $meta_query );
        return $query;
    }

    public function filter_list_attachments( $query ) {

        if ( ! isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'attachment' ) {
            return $query;
        }

        global $pagenow, $current_screen;

        if ( $pagenow !== 'upload.php' || ! isset( $current_screen ) || $current_screen->base !== 'upload' ) {
            return $query;
        }

        $folder_id = isset( $_GET['folder_id'] ) ? sanitize_text_field( base64_decode( $_GET['folder_id'] ) ) : null;
        $account_id = isset( $_GET['account_id'] ) ? sanitize_text_field( base64_decode( $_GET['account_id'] ) ) : null;
        $is_refresh = isset( $_GET['is_refresh'] ) ? filter_var( $_GET['is_refresh'], FILTER_VALIDATE_BOOLEAN ) : false;

        if ( ! empty( $folder_id ) && ! empty( $account_id ) && $folder_id !== 'null' ) {
            $meta_query[] = [
                'key'     => '_indbox_media_folder_id',
                'value'   => $folder_id,
                'compare' => '=',
            ];
        } else {
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => '_indbox_media_folder_id',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        }

        $query->set( 'meta_query', $meta_query );

        return $query;
    }

    public function filter_attachment_data( $response, $attachment, $meta ) {

        $id = isset( $meta['id'] ) ? $meta['id'] : null;
        $account_id = isset( $meta['account_id'] ) ? $meta['account_id'] : null;

        if ( ! isset( $meta['indbox_media'] ) || ! $id || ! $account_id ) {
            return $response;
        }

        $response['url'] = isset( $meta['thumbnail'] ) ? $meta['thumbnail'] : null;
        $response['height'] = 1024;
        $response['width'] = 1024;

        if ( isset( $meta['id'] ) && ! empty( $meta['id'] ) ) {
            $response['filename'] = isset( $meta['name'] ) ? $meta['name'] : '';
            $nonce = wp_create_nonce( 'indbox-preview-nonce' );
        
            $url = add_query_arg(
                [
                    'action' => 'indbox_preview_url',
                    'id' => $id,
                    'account_id' => $account_id,
                    'indbox' => $nonce
                ]
                ,admin_url( 'admin-ajax.php' ));

            $response['url'] = $url;
        }

        $thumbnail_nonce = wp_create_nonce( 'indbox-thumbnail-nonce' );
        $thumb_url = add_query_arg(
            [
                'action'     => 'indbox_thumbnail_url',
                'id'         => $id,
                'account_id' => $account_id,
                'indbox'      => $thumbnail_nonce,
            ]
            , admin_url( 'admin-ajax.php' ) );

        if ( isset( $response['mime'] ) && $response['mime'] === 'image/svg+xml' ) {
            $response['url'] = $thumb_url;
        }

        if ( $response['sizes'] ) {
            $response['sizes'] = [
                'thumbnail' => [
                    'url'    => $thumb_url,
                    'file'   => $thumb_url,
                    'height' => 150,
                    'width'  => 150,
                ],
                'medium'    => [
                    'url'    => $thumb_url,
                    'file'   => $thumb_url,
                    'height' => 300,
                    'width'  => 300,
                ],
                'large'     => [
                    'url'    => $response['url'],
                    'file'   => $response['url'],
                    'height' => 1024,
                    'width'  => 1024,
                ],
            ];
        }

        if ( 'video' === $response['type'] ) {
            $response['height'] = 550;

            $response['width'] = 992;
        }

        return $response;
    }

    public function filter_attachment_url( $url, $attachment_id ): string {
        $file_id = get_post_meta( $attachment_id, '_indbox_media_file_id', true );

        if ( ! empty( $file_id ) ) {
            $account_id = get_post_meta( $attachment_id, '_indbox_media_account_id', true );
            $nonce = wp_create_nonce( 'indbox-preview-nonce' );
     
            $url = add_query_arg(
                [
                    'action' => 'indbox_preview_url',
                    'id' => $file_id,
                    'account_id' => $account_id,
                    'indbox' => $nonce
                ]
                ,admin_url( 'admin-ajax.php' ));
        }
        return $url;
    }

    public function filter_image_src( $image, $attachment_id, $size, $icon ) {

        $file_id = get_post_meta( $attachment_id, '_indbox_media_file_id', true );

        if ( is_array( $image ) && ! empty( $file_id ) ) {

            $account_id = get_post_meta( $attachment_id, '_indbox_media_account_id', true );
            $extension = get_post_meta( $attachment_id, '_indbox_media_extension', true );

            $dropbox_full_size = apply_filters( 'indbox_full_size_image', ['large', 'full', 'post-thumbnail', 'woocommerce_single'] );

            $url_arg = [
                'id'         => $file_id,
                'account_id' => $account_id,
            ];

            if ( in_array( $size, $dropbox_full_size ) && $extension !== 'svg' ) {
                $url_arg['action'] = 'indbox_preview_url';
                $url_arg['indbox'] = wp_create_nonce( 'indbox-preview-nonce' );
            } else {
                $url_arg['action'] = 'indbox_thumbnail_url';
                $url_arg['indbox'] = wp_create_nonce( 'indbox-thumbnail-nonce' );
            }
            $query_url = add_query_arg( $url_arg, admin_url( 'admin-ajax.php' ) );

            $image[0] = $query_url;
        }
        return $image;
    }

    public function insert_attachment( $files, $folder_id = false ) {

        $attachment_ids = [];

        if ( empty( $folder_id ) ) {
            $folder_id = $files[0] ? $files[0]->parent_id : null;
        }

        if ( empty( $folder_id ) ) {
            return;
        }

        $a_file_ids = Database::instance()->get_attachments( $folder_id );
        $file_ids = wp_list_pluck( $files, 'file_id' );

        $find_duplicate_att = Helpers::duplicate_items( $a_file_ids );

        $deleted_att = array_diff( $a_file_ids, $file_ids );

        $deleted_att = array_merge( $deleted_att, $find_duplicate_att );

        if ( ! empty( $deleted_att ) ) {
            foreach ( $deleted_att as $file_id ) {

                $post_id = Database::instance()->is_attachment_exists( $file_id, $folder_id );

                if ( get_post( $post_id ) ) {
                    wp_delete_post( $post_id, true );
                }
            }

        }

        if ( is_array( $files ) ) {
            foreach ( $files as $file ) {

                if ( 'folder' === $file->type ) {
                    continue;
                }
                $is_exists = Database::is_attachment_exists( $file->file_id, $file->parent_id );

                if ( ! empty( $is_exists ) ) {
                    continue;
                }

                if ( empty( $folder_id ) ) {
                    $folder_id = $file->parent_id;
                }
                $attached = $file->thumbnail;
                $meta = [
                    'height'       => '',
                    'width'        => '',
                    'indbox_media' => true,
                    'file'         => $attached,
                    'id'           => $file->file_id,
                    'account_id'   => $file->account_id,
                ];

                if ( empty( file_exists( $attached ) ) || empty( $attached ) ) {

                    $attached = add_query_arg(
                        [
                            'action'     => 'indbox_thumbnail_url',
                            'id'         => $file->file_id,
                            'account_id' => $file->account_id,
                            'indbox'      => wp_create_nonce( 'indbox-thumbnail-nonce' ),
                        ]
                        , admin_url( 'admin-ajax.php' ) );

                    $meta['is_ajax_thumbnail'] = true;
                }

                $attachment = [
                    'guid'           => $attached,
                    'post_mime_type' => $file->type,
                    'post_title'     => $file->entry->basename,
                    'post_author'    => get_current_user_id(),
                    'post_type'      => 'attachment',
                    'post_status'    => 'inherit',
                ];

                if ( strlen( $attachment['guid'] > 254 ) ) {
                    $attachment['guid'] = $attached;
                }

                $attachment_id = wp_insert_post( $attachment );
                update_post_meta( $attachment_id, '_indbox_media_extension', $file->extension );
                update_post_meta( $attachment_id, '_indbox_media_folder_id', $folder_id );
                update_post_meta( $attachment_id, '_indbox_media_file_id', $file->file_id );
                update_post_meta( $attachment_id, '_indbox_media_account_id', $file->account_id );
                update_post_meta( $attachment_id, '_wp_attached_file', $file->name );

                if ( isset( $file->thumbnail_size ) ) {

                    $meta['width'] = 1024;
                    $meta['height'] = 1024;
                }

                if ( isset( $attached ) ) {
                    $meta['thumbnail'] = $attached;
                    $meta['name'] = $file->name;
                }

                if ( isset( $file->size ) ) {
                    $meta['filesize'] = $file->size;
                }

                $sizes = [
                    'full' => [
                        'url'    => $attached,
                        'height' => $meta['height'],
                        'width'  => $meta['width'],
                        'file'   => $attached,
                    ],
                ];
                $meta['sizes'] = $sizes;

                update_post_meta( $attachment_id, '_wp_attachment_metadata', $meta );

                $attachment_ids[] = $attachment_id;
            }
        }
        return $attachment_ids;
    }

    public function localize_data( $data, $script ) {
        if ( 'admin' !== $script ) {
            return $data;
        }

        global $pagenow;

        $data['pagenow'] = $pagenow;
        $data['rootFolders'] = isset( $this->settings['mediaLibraryFolders'] ) ? $this->settings['mediaLibraryFolders'] : null;
        $account_info = self::get_current_account_info();
        $data['activeAccount'] = isset( $account_info ) ? $account_info : [];

        if ( $pagenow === 'upload.php' ) {
            $data['mediaUrl'] = admin_url( 'upload.php' );
        }

        return $data;
    }

    public static function instance(): MediaLibrary {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // Private functions
    private static function get_current_account_info() {
        $current_account = App::get_current_account();

        $account_info = [
            'name'  => $current_account->get_name(),
            'email' => $current_account->get_email(),
            'image' => $current_account->get_image(),
            'id'    => $current_account->get_id(),
        ];

        return $account_info;
    }
}
