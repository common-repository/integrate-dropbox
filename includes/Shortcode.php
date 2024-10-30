<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\ShortcodeBuilder;

class Shortcode
{
    /**
     * @var Shortcode|null
     */
    private static $instance = null;
    private $type = null;
    private $data = null;

    public function __construct()
    {
        add_shortcode('integrate_dropbox', [$this, 'render_shortcode']);
    }

    public function get_shortcode($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));
    }

    public function get_shortcodes($args = [])
    {
        $offset = !empty($args['offset']) ? intval($args['offset']) : 0;
        $limit = !empty($args['limit']) ? intval($args['limit']) : 999;
        $order_by = !empty($args['order_by']) ? sanitize_key($args['order_by']) : 'created_at';
        $order = !empty($args['order']) ? sanitize_key($args['order']) : 'DESC';

        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        return $wpdb->get_results("SELECT * FROM $table ORDER BY $order_by $order LIMIT $offset, $limit");
    }

    public function get_shortcodes_count()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'integrate_dropbox_shortcodes';

        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    public function render_shortcode($atts = [], $data = null)
    {

        $this->fetch_data($atts, $data);
        if (!isset($this->data['type'])) {
            return;
        }
        if (empty($this->data) || !$this->check_status()) {
            if (!empty($this->data['id']) && is_user_logged_in() && !$this->check_status() && !empty($this->data)) {
                return sprintf('<div class="indbox-toplavel-wrapper"><div class="indbox-warning-message">Currently, this shortcode (%s) is disabled. Please enable it and try again.</div></div>', $this->data['id']);
            }
            return;
        }

        if (!$this->check_permission()) {
            ob_start();
            ?>

                        <div class="indbox-toplavel-wrapper">
                            <div class="indbox-access-denied-message">
                                <svg width="104" height="107" view-box="0 0 104 107" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M14.7464 34.2977H45.0512C46.0606 34.2977 47.0156 34.6943 47.6488 35.3764L51.927 39.9852C52.349 40.4399 52.9861 40.7043 53.6589 40.7043H100.966C102.642 40.7043 104 41.8828 104 43.3372V100.765C104 103.902 101.07 106.445 97.4547 106.445H16.2412C13.7098 106.445 11.6575 104.664 11.6575 102.468V36.9783C11.6579 35.498 13.0404 34.2977 14.7464 34.2977Z"
                                        fill="#003FA6" />
                                    <path d="M36.9642 0L24.2144 12.7498V61.607H86.7139V0H36.9642Z" fill="#CBDFFF" />
                                    <path d="M36.9642 12.7498V0L24.2144 12.7498H36.9642Z" fill="#70A6FF" />
                                    <path
                                        d="M4.11463 48.6923H88.0864C90.0657 48.6923 91.764 49.9151 92.1295 51.6028L103.085 102.217C103.561 104.413 101.618 106.445 99.0419 106.445H15.0705C13.0912 106.445 11.3929 105.222 11.0274 103.534L0.0719557 52.9207C-0.403628 50.7234 1.53866 48.6923 4.11463 48.6923Z"
                                        fill="#0061FE" />
                                    <path
                                        d="M58.0146 26.1643C59.6122 24.5451 61.2068 22.9224 62.8023 21.2997C63.742 20.3443 63.8266 18.6213 62.8023 17.6812C61.7882 16.75 60.1864 16.6616 59.1838 17.6812C57.5654 19.2724 55.9473 20.8632 54.3319 22.4566C52.733 20.8573 51.1282 19.2643 49.5042 17.6897C48.5603 16.716 46.7799 16.6705 45.8445 17.6897C44.884 18.7361 44.8372 20.3107 45.8445 21.3494C47.4149 22.9691 49.004 24.5697 50.599 26.1643C49.0048 27.7581 47.417 29.3578 45.847 30.9771C44.8733 31.9211 44.8279 33.7014 45.847 34.6369C46.8934 35.5974 48.4681 35.6441 49.5068 34.6369C51.1299 33.0631 52.7343 31.4706 54.3319 29.8721C55.9465 31.4646 57.5637 33.0546 59.1812 34.6454C60.1367 35.5851 61.8596 35.6696 62.7997 34.6454C63.7309 33.6313 63.8193 32.0294 62.7997 31.0269C61.2055 29.405 59.6118 27.7832 58.0146 26.1643Z"
                                        fill="#0061FE" />
                                </svg>
                                <h3>Access Denied</h3>
                                <p>
                                    <?php
                                    if (isset($this->data['accessDeniedMessage']) && !empty($this->data['accessDeniedMessage'])) {
                                        echo $this->data['accessDeniedMessage'];
                                    } else {
                                        echo "We're sorry, but your account does not currently have access to this content. To gain access, please contact the site administrator who can assist in linking your account to the appropriate content. Thank you.";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>

                        <?php
                        return ob_get_clean();
        }

        global $indbox_fs;


        if (($this->type === 'File Browser' || $this->type === 'Slider Carousel' || $this->type === 'Media Player') && !$indbox_fs->is_paying() && is_user_logged_in()) {
            $html = '';
            if (is_user_logged_in()) {
                wp_enqueue_style('integrate-dropbox-admin-frontend');
                $html = sprintf('<div class="indbox-toplavel-wrapper"><div class="indbox-pro-module-wrapper"><h2>%s Module - Premium Feature</h2><p>You are currently using the free license. To access this feature, you need to upgrade to a Pro license.</p> <a target="_blank" href="%s">Upgrade Now</a></div></div>', $this->type, esc_url($indbox_fs->get_upgrade_url()));
                return $html;
            }
        }
        $entries = isset($this->data['folders']) ? $this->data['folders'] : null;

        if (empty($entries)) {
            __return_false();
        }

        wp_enqueue_style('indbox-frontend-style');

        $html = '';

        do_action('integrate-dropbox-before-wrapper');
        ob_start();
        echo '<div class="indbox-toplavel-wrapper"><div class="integrate-dropbox-preview-wrapper" data-content="' . base64_encode(json_encode($this->data)) . '"></div></div>';
        $html = ob_get_clean();
        do_action('integrate-dropbox-after-wrapper');

        return $html;
    }

    private function fetch_data($atts, $data)
    {
        if (empty($data)) {
            if (!empty($atts['data'])) {
                $data = json_decode(base64_decode($atts['data']), true);
            } elseif (!empty($atts['id'])) {
                $id = intval($atts['id']);
                if ($id) {
                    $shortcode = ShortcodeBuilder::instance()->get_shortcode($id);

                    if (!empty($shortcode)) {
                        $data = unserialize($shortcode->config);
                    }
                }
            }
        }

        $this->type = isset($data['type']) ? $data['type'] : '';
        $this->data = $data;

    }

    private function check_status()
    {

        // $shortcode = ShortcodeBuilder::instance()->get_shortcode($this->data['id']);

        // if (!empty($shortcode) && !empty($this->data)) {
        //     $this->data['status'] = $shortcode->status;
        // }

        if (empty($this->data) || empty($this->data['status']) || $this->data['status'] === 'off') {
            return false;
        }

        return true;
    }

    private function check_permission()
    {
        $data = $this->data;

        if (isset($data['whoCanViewModule'])) {
            $permission = $data['whoCanViewModule'];

            if ('Everyone' === $permission) {
                return true;
            }

            if ('Logged' === $permission && !is_user_logged_in()) {
                return false;
            }
        }

        return true;
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
