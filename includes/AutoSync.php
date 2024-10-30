<?php

namespace CodeConfig\IntegrateDropbox;

use CodeConfig\IntegrateDropbox\App\Processor;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

class AutoSync
{
    private static $instance;

    private $settings;
    private $autoSyncTimerUnit;
    private $autoSyncTimer;
    private $autoSyncFolders;
    private $enableAutoSynchronization;

    public function __construct()
    {
        $this->settings = Processor::instance()->get_setting('settings', []);
        $this->enableAutoSynchronization = isset($this->settings['enableAutoSynchronization']) ? $this->settings['enableAutoSynchronization'] !== 'false' : false;
        $this->autoSyncFolders = $this->settings['autoSyncFolders'] ?? false;
        $this->autoSyncTimer = $this->settings['autoSyncTimer'] ?? false;
        $this->autoSyncTimerUnit = $this->settings['autoSyncTimerUnit'] ?? false;

        if (empty($this->settings) || empty($this->enableAutoSynchronization) || empty($this->autoSyncFolders) || empty($this->autoSyncTimerUnit) || empty($this->autoSyncTimer)) {
            return;
        }

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            add_action('admin_notices', [$this, 'indbox_check_wp_cron_status']);
            return;
        } else {
            add_filter('cron_schedules', [$this, 'indbox_cron_schedule']);
            add_action('wp', [$this, 'schedule_indbox_cron_event']);
            add_action('indbox_corn_fire', [$this, 'indbox_corn_task']);
        }
    }

    public function indbox_cron_schedule($schedules)
    {
        $interval = HOUR_IN_SECONDS; // Default interval 1 hours.
        if (isset($this->autoSyncTimerUnit['value'])) {
            if ('custom' === $this->autoSyncTimerUnit['value']) {
                $interval = $this->autoSyncTimer;
            } else {
                switch ($this->autoSyncTimerUnit['value']) {
                    case '1h':{
                        $interval = HOUR_IN_SECONDS;
                        break;
                    }
                    case '6h':{
                        $interval = 6 * HOUR_IN_SECONDS;
                        break;
                    }
                    case '12h':{
                        $interval = 12 * HOUR_IN_SECONDS;
                        break;
                    }
                    case '24h':{
                        $interval = DAY_IN_SECONDS;
                        break;
                    }
                    case '2d':{
                        $interval = 2 * DAY_IN_SECONDS;
                        break;
                    }
                    case '3d':{
                        $interval = 3 * DAY_IN_SECONDS;
                        break;
                    }
                    case '7d':{
                        $interval = WEEK_IN_SECONDS;
                        break;
                    }
                }

            }
        }

        $schedules['indbox_auto_sync'] = [
            'interval' => $interval,
            'display'  => 'Dropbox Auto Sync',
        ];

        return $schedules;
    }

    public function schedule_indbox_cron_event()
    {
        if (! wp_next_scheduled('indbox_corn_fire')) {
            wp_schedule_event(time(), 'indbox_auto_sync', 'indbox_corn_fire');
        }
    }

    public function indbox_corn_task()
    {

        if (! empty($this->autoSyncFolders)) {
            foreach ($this->autoSyncFolders as $folder) {
                $folder_id = $folder['file_id'] ?? null;

                if (! empty($folder_id)) {
                    Ajax::instance()->indbox_get_entries($folder_id, true);
                }
            }
        }
    }

    public function indbox_check_wp_cron_status()
    {

        ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>WP-Cron is disabled.</strong> If you want the AutoSync function, you need to enable cron. To do this, remove or set <code>DISABLE_WP_CRON</code> to <code>false</code> in the <code>wp-config.php</code> file.</p>
            </div>
        <?php

    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
