<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class ShortcodeBuilder
{
    /**
     * @var null
     */
    protected static $instance = null;

    public function __construct()
    {
    }

    /**
     * Get Shortcode
     *
     * @param int $id
     *
     * @return string
     */
    public function get_shortcode($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));
    }

    /**
     * Get all shortcode
     *
     * @param array $args
     *
     * @return array|null
     */
    public function get_shortcodes($args = [])
    {
        $offset = !empty($args['offset']) ? intval($args['offset']) : 0;
        $limit = !empty($args['limit']) ? intval($args['limit']) : 999;
        $order_by = !empty($args['order_by']) ? sanitize_key($args['order_by']) : 'created_at';
        $order = !empty($args['order']) ? strtoupper(sanitize_key($args['order'])) : 'DESC';

        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}integrate_dropbox_shortcodes ORDER BY $order_by $order LIMIT %d, %d", $offset, $limit));
    }

    public function get_all_shortcodes()
    {


        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}integrate_dropbox_shortcodes ORDER BY `created_at`", ));
    }


    public function get_shortcodes_count()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    public function update_shortcode($posted, $force_insert = false)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';
        $id = !empty($posted['id']) ? intval($posted['id']) : '';
        $status = !empty($posted['status']) ? sanitize_key($posted['status']) : 'on';
        $title = !empty($posted['title']) ? sanitize_text_field($posted['title']) : '';

        $data = [
            'title' => $title,
            'status' => $status,
            'config' => !empty($posted['config']) ? $posted['config'] : serialize($posted),
        ];

        $data_format = ['%s', '%s', '%s'];

        if (!empty($posted['created_at'])) {
            $data['created_at'] = $posted['created_at'];
            $data_format[] = '%s';
        } else {
            $data['created_at'] = current_time('mysql');
        }

        if (!empty($posted['updated_at'])) {
            $data['updated_at'] = $posted['updated_at'];
            $data_format[] = '%s';
        } else {
            $data['updated_at'] = current_time('mysql');
        }

        if (!$id || $force_insert) {
            $wpdb->insert($table, $data, $data_format);

            return $wpdb->insert_id;
        } else {
            $wpdb->update($table, $data, ['id' => $id], $data_format, ['%d']);

            return $id;
        }
    }

    private function process_duplicate_shortcode($id)
    {
        $shortcode = $this->get_shortcode($id);
        if ($shortcode) {
            $shortcode = (array) $shortcode;
            $shortcode['title'] = 'Copy of ' . $shortcode['title'];
            $shortcode['created_at'] = current_time('mysql');
            $shortcode['updated_at'] = current_time('mysql');
            $shortcode['locations'] = serialize([]);

            if (isset($shortcode['config'])) {
                $config = unserialize($shortcode['config']);
                $config['title'] = $shortcode['title'];
                $config['id'] = '';
                $shortcode['config'] = serialize($config);
            }
            return $this->update_shortcode($shortcode, true);
        }
        return false;
    }

    public function duplicate_shortcode($id)
    {
        if (empty($id)) {
            return false;
        }

        $return = [];

        if (is_array($id)) {
            foreach ($id as $item) {
                $return[] = $this->process_duplicate_shortcode($item);
            }
        } else {
            $return[] = $this->process_duplicate_shortcode($id);
        }

        return $return;
    }

    public function delete_shortcode($ids = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $id) {
                if (intval($id)) {
                    $wpdb->delete($table, ['id' => $id], ['%d']);
                }
            }
        }
        return $this->get_shortcodes();
    }

    public static function view()
    { ?>
        <div id="indbox-dropbox-shortcode-builder"></div>
    <?php }

    /**
     * @return Shortcode_Builder|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
