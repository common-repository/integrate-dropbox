<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\API;
use CodeConfig\IntegrateDropbox\App\App;
use CodeConfig\IntegrateDropbox\App\Client;
use CodeConfig\IntegrateDropbox\App\Database;
use CodeConfig\IntegrateDropbox\App\Processor;
use CodeConfig\IntegrateDropbox\App\ShortcodeBuilder;
use CodeConfig\IntegrateDropbox\Helpers;

class Ajax
{
    private static $_instance;
    private $shortcodes;
    private $client;
    private $totalShortcode;

    public function __construct()
    {
        add_action('wp_ajax_indbox_auth_url', [$this, 'indbox_auth_url']);

        add_action('wp_ajax_indbox_get_setting', [$this, 'indbox_get_setting']);
        add_action('wp_ajax_nopriv_indbox_get_setting', [$this, 'indbox_get_setting']);

        add_action('wp_ajax_indbox_set_setting', [$this, 'indbox_set_setting']);

        add_action('wp_ajax_indbox_get_entries', [$this, 'indbox_get_entries']);
        add_action('wp_ajax_nopriv_indbox_get_entries', [$this, 'indbox_get_entries']);

        add_action('wp_ajax_indbox_delete_entries', [$this, 'indbox_delete_entries']);

        add_action('wp_ajax_indbox_get_entries_preview', [$this, 'indbox_get_entries_preview']);
        add_action('wp_ajax_nopriv_indbox_get_entries_preview', [$this, 'indbox_get_entries_preview']);

        add_action('wp_ajax_indbox_get_shortcode', [$this, 'indbox_get_shortcode']);
        add_action('wp_ajax_nopriv_indbox_get_shortcode', [$this, 'indbox_get_shortcode']);

        add_action('wp_ajax_indbox_get_shortcodes', [$this, 'indbox_get_shortcodes']);

        add_action('wp_ajax_indbox_get_all_shortcodes', [$this, 'indbox_get_all_shortcodes']);

        add_action('wp_ajax_indbox_delete_list', [$this, 'indbox_delete_list']);

        add_action('wp_ajax_indbox_save_shortcode', [$this, 'indbox_save_shortcode']);

        add_action('wp_ajax_indbox_duplicate_shortcode', [$this, 'indbox_duplicate_shortcode']);

        add_action('wp_ajax_indbox_revoke_token', [$this, 'indbox_revoke_token']);

        add_action('wp_ajax_indbox_get_all_photos', [$this, 'indbox_get_all_photos']);

        add_action('wp_ajax_indbox_file_exists', [$this, 'indbox_file_exists']);

        add_action('wp_ajax_indbox_get_file', [$this, 'indbox_get_file']);
        add_action('wp_ajax_nopriv_indbox_get_file', [$this, 'indbox_get_file']);

        add_action('wp_ajax_indbox_get_shared_links', [$this, 'indbox_get_shared_links']);

        add_action('wp_ajax_indbox_get_download_links', [$this, 'indbox_get_download_links']);
        add_action('wp_ajax_nopriv_indbox_get_download_links', [$this, 'indbox_get_download_links']);

        add_action('wp_ajax_indbox_get_thumbnail', [$this, 'get_thumbnail']);

        add_action('wp_ajax_indbox_is_user_logged_in', [$this, 'indbox_is_user_logged_in']);
        add_action('wp_ajax_nopriv_indbox_is_user_logged_in', [$this, 'indbox_is_user_logged_in']);

        add_action('wp_ajax_indbox_get_all_user_list', [$this, 'get_all_user_list']);

        add_action('wp_ajax_indbox_set_dropbox_credentials', [$this, 'set_dropbox_credentials']);

        add_action('wp_ajax_indbox_thumbnail_url', [$this, 'get_thumbnail_ajax']);
        add_action('wp_ajax_nopriv_indbox_thumbnail_url', [$this, 'get_thumbnail_ajax']);
        add_action('wp_ajax_indbox_get_url', [$this, 'get_thumbnail_ajax']);
        add_action('wp_ajax_nopriv_indbox_get_url', [$this, 'get_thumbnail_ajax']);

        add_action('wp_ajax_indbox_preview_url', [$this, 'get_entry_preview_ajax']);
        add_action('wp_ajax_nopriv_indbox_preview_url', [$this, 'get_entry_preview_ajax']);
        add_action('wp_ajax_indbox_get_preview_url', [$this, 'get_entry_preview_ajax']);
        add_action('wp_ajax_nopriv_indbox_get_preview_url', [$this, 'get_entry_preview_ajax']);

        add_action('wp_ajax_indbox_upload', [$this, 'indbox_upload']);
        add_action('wp_ajax_indbox_rename', [$this, 'indbox_rename']);
        add_action('wp_ajax_indbox_create_new_folder', [$this, 'indbox_create_new_folder']);
        add_action('wp_ajax_indbox_file_search', [$this, 'indbox_file_search']);

        $this->set_shortcode();

        $this->client = Client::instance();
    }

    public function indbox_file_search()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }
        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '';
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : null;

        if (empty($query)) {
            wp_send_json_error(['message' => __('Search text is required.', 'integrate-dropbox')], 400);
        }

        $result = API::search($query, $path);

        wp_send_json_success($result);
    }

    public function indbox_create_new_folder()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }
        $parent_id = isset($_POST['parent_id']) ? sanitize_text_field($_POST['parent_id']) : 'root';
        $folder_name = isset($_POST['folder_name']) ? sanitize_text_field($_POST['folder_name']) : 'New Folder';

        if (empty($parent_id) || empty($folder_name)) {
            wp_send_json_error(['message' => __('Parent id and folder name is required!', 'integrate-dropbox')]);
        }

        $file = Database::instance()->get_file($parent_id);

        $entry = $file->entry;

        $path = $entry->path;

        $new_folder = API::create_folder($folder_name, $path);

        if (! empty($new_folder)) {
            $new_folder->set_parent($parent_id);
            $result = Database::instance()->set_file($new_folder);
            if (empty($result)) {
                wp_send_json_error(['message' => __('Parent id and folder name is required!', 'integrate-dropbox')]);
            }
            wp_send_json_success($new_folder);
        }
    }

    public function indbox_rename()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;
        $name = isset($_POST['newName']) ? sanitize_text_field($_POST['newName']) : null;

        if (empty($id)) {
            wp_send_json_error(['message' => 'The file is not exists.'], 404);
        }

        $file = Database::instance()->get_file($id);

        $entry = $file->entry;

        $update_entry = API::rename($entry, $name);
        $update_entry->set_parent($entry->get_parent());

        $update_db_entry = Database::instance()->set_file($update_entry, true);

        if (empty($update_db_entry)) {
            wp_send_json_error(['message' => __('Something went wrong!', 'integrate-dropbox')], 400);
        }

        wp_send_json_success($update_entry);
    }

    public function indbox_upload()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $uploaded_file = Helpers::sanitization($_FILES);

        if (! empty($uploaded_file)) {
            $file_data = array_values($uploaded_file);

            $file_object = (object) reset($file_data);

            $folder_id = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : 'root';

            $folder = Database::instance()->get_file($folder_id);

            $folder_path = isset($folder->entry->path) ? $folder->entry->path : '/';

            $path = $folder_path . '/' . $file_object->name;

            $uploaded_entry = API::upload_file($file_object, $path);
            $uploaded_entry->set_parent($folder->entry->id);

            $update_db_entry = Database::instance()->set_file($uploaded_entry);

            if (empty($update_db_entry)) {
                wp_send_json_error(['message' => __('Something went wrong!', 'integrate-dropbox')], 400);
            }

            $response_entry = Database::instance()->get_file($uploaded_entry->get_id());

            if (! empty($response_entry)) {
                wp_send_json_success($response_entry);

            } else {
                wp_send_json_error(['message' => __('No file uploaded.', 'integrate-dropbox')], 400);
            }

        } else {
            wp_send_json_error(['message' => __('No file uploaded.', 'integrate-dropbox')]);
        }
    }

    public function set_dropbox_credentials()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }
        $appKey = isset($_POST['appKey']) ? sanitize_text_field($_POST['appKey']) : null;
        $appSecret = isset($_POST['appSecret']) ? sanitize_text_field($_POST['appSecret']) : null;
        $redirectUrl = isset($_POST['redirectUrl']) ? sanitize_text_field($_POST['redirectUrl']) : null;

        if (empty($appKey) || empty($appSecret)) {
            wp_send_json_error(['message' => 'App key and app secret are required.']);
        }

        update_option('indbox-app-key', $appKey);
        update_option('indbox-app-secret', $appSecret);
        update_option('indbox-redirect-url', $redirectUrl);

        $appKey = get_option('indbox-app-key', '');
        $appSecret = get_option('indbox-app-secret', '');
        $redirectUrl = Helpers::redirect_url();

        wp_send_json_success(['appKey' => $appKey, 'appSecret' => $appSecret, 'redirectUrl' => $redirectUrl]);
    }

    public function get_all_user_list()
    {
        $fetched_users = get_users();

        $users = array_map(function ($user) {
            return [
                'id'         => $user->ID,
                'user_login' => $user->user_login,
            ];
        }, $fetched_users);

        wp_send_json_success($users);
    }

    public function indbox_is_user_logged_in()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        wp_send_json_success(is_user_logged_in());
    }

    public function get_thumbnail_ajax()
    {
        $nonce = isset($_GET['indbox']) ? sanitize_text_field($_GET['indbox']) : null;
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : null;

        if (empty($id) || empty($nonce)) {
            wp_redirect(esc_url(INDBOX_ASSETS . '/images/placeholder-image.webp'));
            exit;
        }

        $image_url = $this->get_thumbnail($id);

        if (! empty($image_url)) {
            wp_redirect(esc_url_raw($image_url));
        } else {
            wp_redirect(esc_url(INDBOX_ASSETS . '/images/placeholder-image.webp'));
        }

        exit;
    }

    public function get_thumbnail($id = false)
    {

        if (empty($_POST['nonce']) && empty($id)) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest') && empty($id)) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $return = false;

        if (empty($id)) {
            $id = empty($_POST['id']) ? null : sanitize_text_field($_POST['id']);
        } else {
            $return = true;
        }

        if (empty($id)) {
            wp_send_json_error(['message' => __('Please provide thumbnail id!', 'integrate-dropbox')], 404);
        }

        $thumbnail_url = Database::instance()->get_thumbnail_url($id);
        $thumbnail_size = Database::instance()->get_thumbnail_size($id);

        if (empty($thumbnail_url) || empty($thumbnail_size)) {

            $entry = Database::instance()->get_entry($id);

            if (empty($entry)) {
                wp_send_json_error(['message' => __("Your provided ID ({$id}) is invalid!", 'integrate-dropbox')], 404);
            }

            $thumbnail = Client::instance()->get_thumbnail($entry, 200, 300);

            $thumbnail_url = isset($thumbnail['url']) ? $thumbnail['url'] : '';
            $thumbnail_height = isset($thumbnail['height']) ? $thumbnail['height'] : 0;
            $thumbnail_width = isset($thumbnail['width']) ? $thumbnail['width'] : 0;

            $thumbnail_size = $thumbnail_width . 'x' . $thumbnail_height;

            Database::instance()->set_thumbnail($id, $thumbnail_url, $thumbnail_size);
        }

        if ($return) {
            return $thumbnail_url;
        }

        $thumbnail_data = [
            'id'   => $id,
            'url'  => $thumbnail_url,
            'size' => $thumbnail_size,
        ];

        wp_send_json_success($thumbnail_data);
    }

    public function get_folder()
    {
        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $id = empty($_POST['id']) ? '' : sanitize_text_field($_POST['id']);
        $folder = $this->client->get_folder($id);

        wp_send_json_success($folder);
    }

    public function indbox_get_download_links()
    {

        if (empty($_POST['nonce'])) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }
        $id = empty($_POST['id']) ? null : sanitize_text_field($_POST['id']);

        $link = $this->manage_download_link($id);

        if (! empty($link)) {

            wp_send_json_success(['link' => $link]);
        }

        wp_send_json_error(['message' => __('Download link not found!', 'integrate-dropbox')], 404);
    }

    public function manage_download_link($id)
    {
        $cacheLink = Database::instance()->get_download_link($id);

        if (! empty($cacheLink)) {

            return $cacheLink;
        }

        $link = Client::instance()->download_entry($id);
        if (! empty($link)) {
            Database::instance()->update_file($id, 'download', $link);
            return $link;
        }

        return false;
    }

    public function indbox_get_shared_links()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $response = Client::instance()->get_shared_links();

        wp_send_json_success(['data' => $response]);
    }

    public function indbox_get_file()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;
        if (empty($id)) {
            wp_send_json_error(['message' => 'file id is required']);
        }

        $res = Database::instance()->get_file($id);
        wp_send_json_success($res);
    }

    public function indbox_file_exists()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;
        if (empty($id)) {
            wp_send_json_error(['message' => __('file id and name is required', 'integrate-dropbox')]);
        }

        $res = Database::instance()->file_exists($id);
        wp_send_json_success($res);
    }

    public function indbox_revoke_token()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $current_account = App::get_current_account();
        App::instance()->revoke_token($current_account);

        if (empty($current_account)) {
            wp_send_json_error(['message' => __('Account not found!', 'integrate-dropbox')]);
        }

        wp_send_json_success(['status' => 'OK', 'message' => 'token remove successfully!']);
    }

    private function unsz_shortcodes($shortcodes)
    {
        if (! empty($shortcodes) && is_array($shortcodes)) {

            foreach ($shortcodes as $shortcode) {
                $shortcode->config = ! is_null($shortcode->config) ? unserialize($shortcode->config) : [];
                $shortcode->locations = ! is_null($shortcode->locations) ? unserialize($shortcode->locations) : null;
                $shortcode->config['id'] = $shortcode->id;
            }
        }
        return $shortcodes;
    }

    private function set_shortcode($args = [])
    {
        $shortcodes = ShortcodeBuilder::instance()->get_shortcodes($args);

        $this->shortcodes = $this->unsz_shortcodes($shortcodes);
        $this->totalShortcode = ShortcodeBuilder::instance()->get_shortcodes_count();
    }

    public function indbox_save_shortcode()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $data = isset($_POST['data']) ? Helpers::sanitization($_POST['data']) : '';
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 5;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

        $args = [
            'limit'    => $per_page,
            'offset'   => ($page - 1) * $per_page,
            'order_by' => 'created_at',
        ];
        if (! empty($data)) {
            $newShortcodeId = ShortcodeBuilder::instance()->update_shortcode($data);

            $this->set_shortcode($args);

            wp_send_json_success(['shortcodes' => $this->shortcodes, 'id' => $newShortcodeId]);
        }
        wp_send_json_error(['message' => __('Account not found!', 'integrate-dropbox')]);
    }

    public function indbox_duplicate_shortcode()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $id = isset($_POST['id']) ? Helpers::sanitization($_POST['id']) : '';
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 5;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

        $args = [
            'limit'    => $per_page,
            'offset'   => ($page - 1) * $per_page,
            'order_by' => 'created_at',
        ];

        if (! empty($id)) {
            $update_shortcode = ShortcodeBuilder::instance()->duplicate_shortcode($id);

            $this->set_shortcode($args);
            wp_send_json_success(['shortcodes' => $this->shortcodes, 'updateShortcode' => $update_shortcode]);
        }
        wp_send_json_error(['message' => __('Something went wrong!', 'integrate-dropbox')]);
    }

    public function indbox_delete_list()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $ids = isset($_POST['id']) ? Helpers::sanitization($_POST['id']) : '';
        if (! empty($ids)) {
            $shortcodes = ShortcodeBuilder::instance()->delete_shortcode($ids);

            wp_send_json_success(['shortcodes' => $this->unsz_shortcodes($shortcodes), 'status' => 'OK', 'ids' => $ids], 200);
        }

        wp_send_json_success(['message' => __('Something went wrong! Try again later.', 'integrate-dropbox'), 'status' => 'ERROR', 'ids' => $ids], 200);
    }

    public function indbox_get_shortcodes()
    {

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 5;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'created_at';
        $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'asc';

        $args = [
            'limit'    => $per_page,
            'offset'   => ($page - 1) * $per_page,
            'order_by' => $sort_by,
            'order'    => $sort_order,
        ];

        $this->set_shortcode($args);

        $shortcodes = $this->shortcodes;
        $totalShortcode = $this->totalShortcode;

        wp_send_json_success([
            'data'         => $shortcodes,
            'current_page' => $page,
            'args'         => $args,
            'total_items'  => $totalShortcode,
        ]);
    }
    public function indbox_get_shortcode()
    {

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : "";

        $response = ShortcodeBuilder::instance()->get_shortcode($id);

        $shortcodeData = $this->unsz_shortcodes($response);
        $shortcode = unserialize($shortcodeData->config);
        $shortcode['status'] = $shortcodeData->status;

        wp_send_json_success([
            'data' => $shortcode,
        ]);
    }

    public function indbox_get_all_shortcodes()
    {

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $shortcodes = ShortcodeBuilder::instance()->get_shortcodes();
        $this->shortcodes = $this->unsz_shortcodes($shortcodes);

        wp_send_json_success([
            'data' => $shortcodes,
        ]);
    }

    /**
     * Get Dropbox Files
     *
     * @param string $id
     * @param bool $force
     * @return array|null
     */
    public function indbox_get_entries($id = '', $force = false)
    {

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest') && empty($id)) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $isJsonSend = false;

        if (empty($id)) {
            $isJsonSend = true;
            $id = isset($_POST['id']) ? Helpers::sanitization($_POST['id']) : '';
        }

        if (empty($force)) {
            $force = isset($_POST['force']) ? rest_sanitize_boolean($_POST['force']) : false;
        }

        if (empty($id) && $isJsonSend) {
            wp_send_json_error(['data' => __('Data not found', 'integrate-dropbox')]);
        }

        if (empty($this->client)) {
            $this->client = Client::instance();
        }

        $is_folder_exists = Database::instance()->file_exists($id);

        if (empty($is_folder_exists) || $force) {
            $self = $this->client->get_folder($id);

            if ($self instanceof \CodeConfig\IntegrateDropbox\App\Entry) {

                if ($self->get_id() === 'Dropbox') {
                    $self->set_id('root');
                }

                $entries_info = ['self' => $self];

                Database::instance()->set_file($self, $force);
            } else {
                wp_send_json_error(['message' => __('Your provide id is invalid', 'integrate-dropbox')]);
            }
        } else {
            $self = Database::instance()->get_file($id);
            $entries_info = ['self' => $self];
        }

        $get_files = Database::instance()->get_files([
            'parent_id' => $id,
        ]);

        if (is_wp_error($get_files)) {
            wp_send_json_error(['message' => __('Something went wrong please try again!', 'integrate-dropbox')]);
        }

        if (! empty($get_files)) {
            $entries_info['children'] = $get_files;
        }

        if (empty($entries_info['children']) || $force) {

            $Entries = $this->client->get_folder($id)->children;

            if (is_array($Entries)) {
                $checkOldData = Database::instance()->get_files([
                    'parent_id'  => $id,
                    'account_id' => App::get_current_account()->get_id(),
                ]);

                if (! empty($checkOldData)) {
                    $deleted_files = $this->compare_cloud_and_server($Entries, $checkOldData);

                    if (! empty($deleted_files)) {
                        foreach ($deleted_files as $deleted_file) {
                            $result = Database::instance()->delete_file($deleted_file);
                            if (is_wp_error($result)) {
                                return false;
                            }
                        }
                    }
                }

                foreach ($Entries as $entry) {
                    $entry->set_parent($id);
                    $response = Database::instance()->set_file($entry, $force);

                    if (is_wp_error($response) || empty($response)) {
                        wp_send_json_error(['data' => __('Something went wrong please try again!', 'integrate-dropbox')]);
                    }

                    $get_files = Database::instance()->get_files([
                        'parent_id' => $id,
                    ]);

                    if (is_wp_error($get_files)) {
                        wp_send_json_error(['data' => __('Something went wrong please try again!', 'integrate-dropbox')]);
                    }

                    if (! empty($get_files)) {
                        $entries_info['children'] = $get_files;
                    }
                }
            }
        }

        if (Integration::instance()->is_active('media-library') && isset($entries_info['children'])) {
            MediaLibrary::instance()->insert_attachment($entries_info['children']);
        }

        if ($isJsonSend) {
            wp_send_json_success(['data' => $entries_info]);
        } else {
            return $entries_info;
        }
    }

    public function indbox_delete_entries()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $entries = isset($_POST['entries']) ? Helpers::sanitization($_POST['entries']) : '';

        if (empty($entries)) {
            wp_send_json_error("[Integrate Dropbox] You cannot delete any file because no file has been selected. Please choose a file to delete.");
        }

        $response = Client::instance()->delete_entries($entries);

        wp_send_json_success(['data' => $response]);
    }

    public function indbox_get_setting()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $key = isset($_POST['key']) ? Helpers::sanitization($_POST['key']) : '';
        $default = isset($_POST['default']) ? Helpers::sanitization($_POST['default']) : '';
        $data = isset($_POST['data']) ? Helpers::sanitization($_POST['data']) : '';
        $entries = isset($_POST['entries']) ? Helpers::sanitization($_POST['entries']) : '';
        $force = isset($_POST['force']) ? Helpers::sanitization($_POST['force']) : '';

        if ($data) {
            $key = 'accounts';
        }

        $res = $default;

        $settings = Processor::instance()->get_setting($key, $default);
        global $indbox_fs;

        if (! empty($settings)) {
            $activeIntegration = $settings['activeIntegration'] ?? null;
            if (! empty($activeIntegration) && ! $indbox_fs->is_paying()) {
                $pro_items = ["master-study-lms", "woocommerce", "tutor-lms", "learnpress"];

                $activeIntegration = array_values(array_diff($activeIntegration, $pro_items));
                $settings['activeIntegration'] = $activeIntegration;
            }
            $res = $settings;
        }

        $accounts = Processor::instance()->get_setting('accounts', false);

        if ($data === 'login') {
            $res = ! empty($accounts);
        } elseif ($data === 'current-account') {
            $current_account = App::get_current_account();

            if (empty($this->client)) {
                $this->client = Client::instance();
            }

            $account_space_info = $this->client->get_account_space_info();

            $used = isset($account_space_info['used']) ? Helpers::bytes_to_size_1024($account_space_info['used']) : 0;
            $allocated = isset($account_space_info['allocation']['allocated']) ? Helpers::bytes_to_size_1024($account_space_info['allocation']['allocated']) : 0;

            $used_percentage = ($account_space_info['used'] / $account_space_info['allocation']['allocated']) * 100;

            $used_percentage_format = number_format($used_percentage, 3);

            $res = [
                'name'             => $current_account->get_name(),
                'email'            => $current_account->get_email(),
                'image'            => $current_account->get_image(),
                'accountSpaceInfo' => [
                    'used'           => $used,
                    'allocated'      => $allocated,
                    'usedPercentage' => $used_percentage_format,
                ],
            ];
        }

        $account_info = [
            'isPro'       => $indbox_fs->is_paying(),
            'adminUrl'    => admin_url(),
            'appKey'      => get_option('indbox-app-key', ''),
            'appSecret'   => get_option('indbox-app-secret', ''),
            'redirectUrl' => Helpers::redirect_url(),
            'redirectUrls' => Helpers::redirect_urls(),
            'nonce'       => wp_create_nonce('indbox-nonce'),
        ];

        $check_scope = Helpers::check_app_permission(null, true);

        if (! empty($accounts) && isset($check_scope['status']) && empty($check_scope['status']) && ! empty($check_scope['missingPermission'])) {
            $account_info['scope'] = $check_scope;
        }

        if (! $indbox_fs->is_paying()) {
            $account_info['upgradeUrl'] = $indbox_fs->get_upgrade_url();
        }

        if ($account_url = $indbox_fs->get_account_url()) {
            $account_info['accountUrl'] = $account_url;
        }

        $response = ['data' => $res, 'accountInfo' => $account_info];

        if (! empty($entries) && ! empty($settings)) {
            $response['entries'] = $this->indbox_get_entries($entries, $force);
        }

        wp_send_json_success($response);
    }

    public function indbox_get_all_photos()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $force = isset($_POST['force']) ? filter_var($_POST['force'], FILTER_VALIDATE_BOOLEAN) : false;

        $photos = Client::instance()->get_all_photos([
            'path'          => '/',
            'offset'        => 0,
            'post_per_page' => 10,
            'force'         => $force,
        ]);

        wp_send_json_success(['data' => $photos]);
    }

    public function indbox_set_setting()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : null;
        $value = isset($_POST['value']) ? Helpers::sanitization($_POST['value']) : null;

        if (empty($key) || empty($value)) {
            return false;
        }

        $res = Processor::instance()->set_setting($key, $value);

        wp_send_json_success($res);
    }

    public function get_entry_preview_ajax()
    {
        $nonce = isset($_GET['indbox']) ? sanitize_text_field($_GET['indbox']) : null;
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : null;

        if (empty($id) || empty($nonce)) {
            header('Content-Type: image/jpeg');

            wp_redirect(esc_url(INDBOX_ASSETS . '/images/placeholder-image.webp'));
            exit;
        }

        $image_url = $this->indbox_get_entries_preview($id);

        if ($image_url) {
            wp_redirect(esc_url_raw($image_url));
        } else {
            header('Content-Type: image/jpeg');
            wp_redirect(esc_url(INDBOX_ASSETS . '/images/placeholder-image.webp'));
        }

        exit;
    }

    public function indbox_get_entries_preview($id)
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest') && empty($id)) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $return = false;

        if (empty($id)) {
            $id = isset($_POST['id']) ? Helpers::sanitization($_POST['id']) : '';
        } else {
            $return = true;
        }

        if (! $id) {
            wp_send_json_success(['data' => 'data not found']);
        }
        if (empty($this->client)) {
            $this->client = Client::instance();
        }

        $Entries = $this->client->preview_entry_info($id);

        if (! empty($return)) {
            return $Entries['url'];
        }

        wp_send_json_success(['data' => $Entries]);
    }

    public function indbox_auth_url()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (! wp_verify_nonce($nonce, 'indbox-nonce') && ! wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error(['message' => __('Unauthorized Request!', 'integrate-dropbox')], 401);
        }

        $data = ['authUrl' => App::instance()->get_auth_url(['prompt' => 'login'])];
        wp_send_json_success($data, 200);
    }

    /**
     * @param array $cloud
     * @param array $server
     **/
    private function compare_cloud_and_server($cloud, $server)
    {
        if (is_array($cloud) && is_array($server)) {
            $cloud_ids = array_keys($cloud);

            $server_ids = array_map(function ($item) {

                if (is_object($item)) {
                    return $item->file_id;
                }
                return '';
            }, array_values($server));

            $dif_ids = array_diff($server_ids, $cloud_ids);

            return $dif_ids;
        }
        return null;
    }

    /**
     * @return Ajax
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
