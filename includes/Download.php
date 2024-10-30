<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class Download
{
    private static $instance;
    private $id;
    private $account_id;
    private $action;

    public function __construct()
    {
        $this->action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : null;

        if ($this->action !== 'indbox_download_file') {
            return;
        }

        $this->id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
        $this->account_id = isset($_GET['account_id']) ? sanitize_text_field($_GET['account_id']) : null;

        add_action('init', 'indbox_download_file');
    }

    public function indbox_download_file()
    {
        if ((empty($this->id) || empty($this->account_id)) && $this->action === 'indbox_download_file') {
            if (! function_exists('wp_redirect')) {
                require_once ABSPATH . 'wp-includes/pluggable.php';
            }
            \wp_redirect(home_url());
            exit();
        }

        $redirectURL = Ajax::instance()->manage_download_link($this->id);

        wp_redirect($redirectURL);
        exit;

    }

    /**
     * @return Download
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
