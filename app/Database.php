<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class Database
{
    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var Database|null
     */
    private static $instance;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->prefix = $this->wpdb->prefix;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function file_exists($id)
    {

        return !!$this->get_file($id);
    }

    /**
     * @param string $id
     * @return object|array|null
     */
    public function get_file($id)
    {
        $valid_ids = ['Dropbox', '/', ''];
        $id = in_array($id, $valid_ids) ? 'root' : $id;

        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT * FROM $this->table WHERE `file_id` = %s", $id);
        $response = $this->wpdb->get_row($sql);

        if ($response && isset($response->data)) {
            $data = unserialize($response->data);
            $response->entry = $data;
            $response->data = "";
            $response->preview = explode('|', $response->preview ?? '')[0];
            $response->download = explode('|', $response->download ?? '')[0];
        }

        return $response;
    }

    /**
     * @param string
     */
    public function delete_file($id)
    {
        $valid_ids = ['Dropbox', '/', ''];
        $id = in_array($id, $valid_ids) ? 'root' : $id;

        $this->table = $this->prefix . 'integrate_dropbox_files';

        $thumbnail = $this->get_thumbnail_url($id);

        if (!empty($thumbnail)) {

            $thumbnail_path = str_replace(INDBOX_CACHE_URL, INDBOX_CACHE_DIR, $thumbnail);

            if (file_exists($thumbnail_path)) {
                wp_delete_file($thumbnail_path);
            }
        }

        $sql = $this->wpdb->prepare("DELETE FROM $this->table WHERE file_id = %s", $id);
        $response = $this->wpdb->query($sql);

        return $response;
    }

    /**
     * @param Entry $entry
     * @param bool $force
     * @return int|bool
     */
    public function set_file($entry, $force = false)
    {

        $this->table = $this->prefix . 'integrate_dropbox_files';

        $id = $entry->get_id();
        $name = $entry->get_name();
        $is_exists = $this->file_exists($id);

        if ($is_exists && !$force) {
            error_log('[Integrated Dropbox]: File already exists! file id: ' . $id . ' Name: ' . $name);
            return true;
        }

        $size = $entry->get_size();
        $parent_id = $entry->get_parent() ? $entry->get_parent() : 'root';

        if ($parent_id === '/' || $parent_id === '') {
            $parent_id = 'root';
        }

        if ($id === 'root') {
            $parent_id = '';
        }
        $account_id = App::instance()->get_current_account()->get_id();
        $type = $entry->is_dir() ? 'folder' : $entry->get_mimetype();
        $thumbnail_url = $entry->get__thumbnail();
        $thumbnail_size = $entry->get_thumbnail_size();
        $extension = $entry->get_extension();
        $preview = $entry->get_preview_link();
        $data = serialize($entry);
        $now = new \DateTime();
        $created = $now->format('Y-m-d H:i:s');
        // $result = $entry;

        if ($is_exists && $force) {
            $sql = $this->wpdb->prepare("UPDATE `$this->table` SET `name` = %s, `size` = %d, `parent_id` = %s, `account_id` = %s, `type` = %s, `extension` = %s, `preview` = %s, `data` = %s,`updated` = %s WHERE `file_id` = %s", $name, $size, $parent_id, $account_id, $type, $extension, $preview, $data, $created, $id);
        } else {
            $sql = $this->wpdb->prepare("INSERT INTO `$this->table` (`file_id`, `name`, `size`, `parent_id`, `account_id`, `type`, `extension`, `thumbnail`, `thumbnail_size`, `preview`, `data`, `created`, `updated`) values (%s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", $id, $name, $size, $parent_id, $account_id, $type, $extension, $thumbnail_url, $thumbnail_size, $preview, $data, $created, $created);
        }
        $result = $this->wpdb->query($sql);

        if (!empty($result)) {
            $cache_key = 'integrate-dropbox-files_' . $account_id . '_' . $parent_id;
            wp_cache_delete($cache_key);
        }

        return $result;
    }

    public function update_file($id, $key, $value)
    {

        if ($key === 'download' || $key === 'preview') {
            $expires_time = (time() + (4 * 60 * 60) - (10 * 60));

            $value .= '|' . $expires_time;
        }

        $this->table = $this->prefix . 'integrate_dropbox_files';

        $this->wpdb->update($this->table, [$key => $value], ['file_id' => $id], ['%s'], ['%s']);
    }

    public function count_entries($parent_id = null, $account_id = null)
    {
        if ($parent_id === '/' || empty($parent_id)) {
            $parent_id = 'root';
        }

        if (empty($account_id)) {
            $account_id = App::get_current_account()->get_id();
        }

        $this->table = $this->prefix . 'integrate_dropbox_files';

        return $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM $this->table WHERE parent_id = %s AND account_id = %s", $parent_id, $account_id));
    }

    /**
     * @return array|null
     */
    public function get_files($args = [])
    {

        $default = [
            'parent_id' => 'root',
            'account_id' => null,
            'sort' => [],
            'extensions' => [],
        ];

        $args = wp_parse_args($args, $default);

        $parent_id = $args['parent_id'];

        if ($parent_id === '/') {
            $parent_id = 'root';
        }
        $account_id = $args['account_id'];

        if (empty($account_id)) {
            $account_id = App::get_current_account()->get_id();
        }

        $cache_key = 'integrate-dropbox-files_' . $account_id . '_' . $parent_id;
        $result = wp_cache_get($cache_key);

        if (empty($result)) {

            $sort = $args['sort'];
            $file_extensions = $args['extensions'];

            $this->table = $this->prefix . 'integrate_dropbox_files';

            $is_exists = $this->count_entries();

            if (empty($is_exists)) {
                __return_false();
            }

            if ($parent_id !== 'all_photos') {
                $sql = "SELECT * FROM {$this->table} WHERE parent_id = %s";
                $sql = $this->wpdb->prepare($sql, $parent_id);
            }

            if ($parent_id === 'all_photos') {
                $sql = "SELECT * FROM {$this->table} WHERE account_id = %s";
                $sql = $this->wpdb->prepare($sql, $account_id);
            } elseif (!is_null($account_id)) {
                $sql .= " AND account_id = %s";
                $sql = $this->wpdb->prepare($sql, $account_id);
            }

            if (!empty($file_extensions)) {
                $sql .= " AND (";
                $placeholders = rtrim(str_repeat("extension = %s OR ", count($file_extensions)), " OR ");
                $sql .= $placeholders;
                $sql .= ")";
                $sql = $this->wpdb->prepare($sql, ...$file_extensions);
            }

            if (!empty($sort)) {
                $sql .= " ORDER BY ";
                foreach ($sort as $column => $direction) {
                    $sql .= "$column $direction, ";
                }
                $sql = rtrim($sql, ', ');
            }

            $response = $this->wpdb->get_results($sql);

            if (is_wp_error($response) || empty($response)) {
                __return_false();
            }

            $result = $this->process_files($response);

            if (!empty($result)) {
                wp_cache_set($cache_key, $result);
            }

        }

        return $result;
    }

    public function set_preview_entry($id, $preview)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $expires_time = (time() + (4 * 60 * 60) - (10 * 60));

        $preview .= '|' . $expires_time;

        $sql = $this->wpdb->prepare("UPDATE $this->table SET preview = %s WHERE file_id = %s", $preview, $id);
        $result = $this->wpdb->query($sql);

        return $result !== false;
    }

    public function get_preview_entry($id)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT preview FROM $this->table WHERE file_id = %s", $id);
        $result = $this->wpdb->get_var($sql);

        if (!is_wp_error($result) && !empty($result)) {
            $preview = explode('|', $result);

            if (isset($preview[0]) && isset($preview[1]) && intval($preview[1]) > time() || strpos($preview[0], 'jpg') || strpos($preview[0], 'png')) {
                return $preview[0];
            }

            return false;
        }

        return false;
    }

    public function get_download_link($id)
    {
        $file = $this->get_file($id);

        if (isset($file) && isset($file->download) && $link = $file->download) {

            $decodedLink = urldecode($link);

            $explodeLink = explode('|', $decodedLink);

            if (isset($explodeLink[0]) && isset($explodeLink[1]) && intval($explodeLink[1]) > time()) {
                return $link;
            }

        }

        return false;

    }

    public function get_entry($id)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT data FROM $this->table WHERE file_id = %s", $id);
        $result = $this->wpdb->get_var($sql);

        if (!is_wp_error($result) && !empty($result)) {

            $entry = unserialize($result);
            if (!empty($entry)) {

                return $entry;
            }

            return false;
        }

        return false;
    }

    public function get_thumbnail_url($id)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT thumbnail FROM $this->table WHERE file_id = %s", $id);

        $result = $this->wpdb->get_var($sql);

        if (empty($result) || is_wp_error($result)) {
            return false;
        }

        $file_path = str_replace(INDBOX_CACHE_URL, INDBOX_CACHE_DIR, $result);

        if (!empty($file_path) && !file_exists($file_path)) {
            return false;
        }

        return $result;
    }
    public function get_thumbnail_size($id)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT thumbnail_size FROM $this->table WHERE file_id = %s", $id);

        $result = $this->wpdb->get_var($sql);

        return $result;
    }

    public function set_thumbnail($id, $thumbnail_url, $thumbnail_size)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("UPDATE $this->table SET thumbnail = %s, thumbnail_size = %s WHERE file_id = %s", $thumbnail_url, $thumbnail_size, $id);
        $result = $this->wpdb->query($sql);

        return $result;
    }

    public function get_entry_extension($id)
    {
        $this->table = $this->prefix . 'integrate_dropbox_files';

        $sql = $this->wpdb->prepare("SELECT extension FROM $this->table WHERE file_id = %s", $id);
        $result = $this->wpdb->get_var($sql);

        return $result;
    }

    private function process_files($files)
    {
        $unserialize_file = null;

        if (is_array($files)) {
            foreach ($files as $key => $file) {
                $unserialize_file[$key] = $this->process_file($file);
            }
        }

        return $unserialize_file;
    }

    private function process_file($file)
    {

        $file->entry = unserialize($file->data);

        if (!empty($file->preview)) {
            $preview = explode('|', $file->preview);

            if (isset($preview[0]) && isset($preview[1]) && intval($preview[1]) > time()) {
                $file->preview = $preview[0];
            } else {
                unset($file->preview);
            }
        }

        if (!empty($file->download)) {
            $download = explode('|', $file->download);

            if (isset($download[0]) && isset($download[1]) && intval($download[1]) > time()) {
                $file->download = $download[0];
            } else {
                unset($file->download);
            }
        }

        unset($file->data);
        $thumbnail_path = str_replace(INDBOX_CACHE_URL, INDBOX_CACHE_DIR, $file->thumbnail);

        if (!empty($file->thumbnail) && !file_exists($thumbnail_path)) {
            $file->thumbnail = null;
        }

        return $file;
    }

    public function get_attachments($folder_id)
    {
        if (!empty($folder_id)) {
            global $wpdb;

            // $meta_key = '_indbox_media_folder_id';
            // $meta_value = $folder_id;
            // $get_attachments_ids = $wpdb->get_results( $wpdb->prepare(
            //     "SELECT _indbox_media_file_id
            //     FROM {$wpdb->postmeta}
            //     WHERE meta_key = %s AND meta_value = %s",
            //     $meta_key, $meta_value
            // ), ARRAY_A );

            // $post_ids = wp_list_pluck( $get_attachments_ids, 'post_id' );

            $folder_meta_key = '_indbox_media_folder_id';
            $folder_meta_value = $folder_id; // The folder ID you're searching for
            $file_meta_key = '_indbox_media_file_id';

            $get_file_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT pm2.meta_value AS file_id
                    FROM {$wpdb->postmeta} pm1
                    INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
                    WHERE pm1.meta_key = %s AND pm1.meta_value = %s
                    AND pm2.meta_key = %s",
                    $folder_meta_key,
                    $folder_meta_value,
                    $file_meta_key
                ),
                ARRAY_A
            );

            $file_ids = wp_list_pluck($get_file_ids, 'file_id');

            return $file_ids;
        }
        return [];
    }

    public static function is_attachment_exists($file_id, $folder_id)
    {
        if (!empty($file_id)) {
            global $wpdb;

            $meta_key = '_indbox_media_file_id';
            $meta_value = $file_id;
            $meta_key2 = '_indbox_media_folder_id';
            $meta_value2 = $folder_id;

            $post_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT pm1.post_id
                FROM $wpdb->postmeta pm1
                 JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
                 WHERE pm1.meta_key = %s AND pm1.meta_value = %s
                 AND pm2.meta_key = %s AND pm2.meta_value = %s",
                $meta_key,
                $meta_value,
                $meta_key2,
                $meta_value2
            ));

            if (!empty($post_exists)) {
                return $post_exists;
            } else {
                return false;
            }

        }
    }

    /**
     * @return Database|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
