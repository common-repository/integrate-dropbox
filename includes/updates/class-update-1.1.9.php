<?php

namespace CodeConfig\IntegrateDropbox;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\App;

class Update_1_1_9
{
    private static $instance = null;

    public function __construct()
    {
        $this->update_table_column();
    }

    public function update_table_column()
    {
        $redirectURL = INDBOX_URL . 'authentication.php';
        update_option('indbox-redirect-url', $redirectURL);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            return self::$instance = new self();
        }

        return self::$instance;
    }
}

Update_1_1_9::instance();
