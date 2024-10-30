<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\Account;
use CodeConfig\IntegrateDropbox\App\Accounts;
use CodeConfig\IntegrateDropbox\Helpers;
use CodeConfig\IntegrateDropbox\SDK\Dropbox;
use CodeConfig\IntegrateDropbox\SDK\DropboxApp;
use CodeConfig\IntegrateDropbox\SDK\Store\DatabasePersistentDataStore;

class App
{
    /**
     * @var string
     */
    private $_app_key;

    /**
     * @var string
     */
    private $_app_secret;

    /**
     * @var \CodeConfig\IntegrateDropbox\SDK\Dropbox
     */
    private static $_sdk_client;

    /**
     * @var \CodeConfig\IntegrateDropbox\SDK\DropboxApp
     */
    private static $_sdk_client_app;

    /**
     * @var \CodeConfig\IntegrateDropbox\App\Account
     */
    private static $_current_account;

    private $callbackUrl;

    /**
     *  TODO: Shortcode
     * @var string
     */
    public $client;

    /**
     * @var App
     */
    private static $instance;

    /**
     * @var string
     */
    private $parentPage = 'integrate-dropbox';

    public function __construct()
    {
        add_action('integrate-dropbox-refresh-token', [$this, 'refresh_token'], 10, 1);
        $this->_app_key = get_option('indbox-app-key');
        $this->_app_secret = get_option('indbox-app-secret');
        $this->callbackUrl = Helpers::redirect_url();
    }

    public static function instance()
    {

        if (is_null(self::$instance)) {
            $app = new self();
        } else {
            $app = self::$instance;
        }

        if (empty($app::$_sdk_client)) {
            try {
                $app->start_sdk_client(App::get_current_account());
            } catch (\Exception $ex) {
                self::$instance = $app;

                return self::$instance;
            }
        }

        self::$instance = $app;

        if (null !== App::get_current_account()) {
            $app->get_sdk_client(App::get_current_account());
        }

        return self::$instance;
    }

    public function start_sdk_client(Account $account = null)
    {
        self::$_sdk_client = new Dropbox($this->get_sdk_client_app($account), ['persistent_data_store' => new DatabasePersistentDataStore()]);
    }

    /**
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxApp
     */
    public function get_sdk_client_app(Account $account = null)
    {
        if (empty(self::$_sdk_client_app)) {
            if (! empty($account)) {
                self::$_sdk_client_app = new DropboxApp($this->get_app_key(), $this->get_app_secret(), $account->get_authorization()->get_access_token($account->get_id()));
            } else {
                self::$_sdk_client_app = new DropboxApp($this->get_app_key(), $this->get_app_secret());
            }
        }

        return self::$_sdk_client_app;
    }

    public function get_auth_url($prompt = [])
    {

        $authHelper = self::get_sdk_client()->getAuthHelper();

        $urlState = admin_url('admin.php?page=integrate-dropbox&action=integrate-dropbox-authorization');
        $urlState .= sprintf('&site_url=%s', site_url());

        $encodeState = strtr(base64_encode($urlState), '+/=', '-_~');

        // Token Access Type
        $tokenAccessType = "offline";

        return $authHelper->getAuthUrl($this->callbackUrl, $prompt, $encodeState, $tokenAccessType);
    }

    public function process_authorization()
    {

        if (! isset($_GET['state']) || empty($_GET['state']) || ! isset($_GET['code']) || empty($_GET['code'])) {

            $this->closeAndExit();
        }

        $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';

        $explodeState = explode('|', $state);

        $urlState = isset($explodeState[1]) ? $explodeState[1] : '';

        $decodeUrlState = base64_decode(strtr($urlState, '-_~', '+/='));

        if (false === strpos($decodeUrlState, 'integrate-dropbox-authorization')) {

            $this->closeAndExit();
        }

        $this->create_access_token();

        $this->closeAndExit();
    }

    public function create_access_token()
    {
        try {
            $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
            $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';

            $accessToken = self::get_sdk_client()->getAuthHelper()->getAccessToken($code, $state, $this->callbackUrl);

            if (is_object($accessToken)) {
                Helpers::check_app_permission($accessToken->scope);
            }

            self::get_sdk_client()->setAccessToken($accessToken);

            $account_data = self::get_sdk_client()->getCurrentAccount();

            $account = new Account($account_data->getAccountId(), $account_data->getDisplayName(), $account_data->getEmail(), $account_data->getAccountType(), $account_data->getProfilePhotoUrl());

            $account->get_authorization()->set_access_token($accessToken);

            if ($account_data->emailIsVerified()) {
                $account->set_is_verified(true);
            }

            Accounts::instance()->add_account($account);
        } catch (\Exception $ex) {
            error_log('[Integrate Dropbox]: ' . sprintf('Cannot generate Access Token: %s', $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Error communicating with API:', 'integrate-dropbox') . $ex->getMessage());
        }
    }

    public function refresh_token(Account $account)
    {
        $authorization = $account->get_authorization();
        $access_token = $authorization->get_access_token($account->get_id());

        $refresh_token = $access_token->getRefreshToken();

        if (empty($refresh_token)) {
            error_log('[Integrate Dropbox message]: ' . sprintf('No Refresh Token found during the renewing of the current token. We will stop the authorization completely.'));
            $authorization->set_is_valid(false);

            $this->revoke_token($account);

            return false;
        }

        try {
            $new_access_token = self::$_sdk_client->getAuthHelper()->refreshToken();

            $authorization->set_access_token($new_access_token);
            self::get_sdk_client()->setAccessToken($new_access_token);
        } catch (\Exception $ex) {
            $authorization->set_is_valid(false);
            error_log('[Integrate Dropbox message]: ' . sprintf('Cannot refresh Authorization Token'));

            throw $ex;
        }

        return self::$_sdk_client;
    }

    public function revoke_token(Account $account)
    {
        error_log('[Integrate Dropbox message]: Lost authorization');

        try {
            $this->get_sdk_client($account)->getAuthHelper()->revokeAccessToken();
            Accounts::instance()->remove_account($account->get_id());
        } catch (\Exception $ex) {
            error_log('[Integrate Dropbox  message]: ' . $ex->getMessage());
        }
    }

    /**
     * Get SDK Client
     *
     * @param \CodeConfig\IntegrateDropbox\App\Account
     * @return \CodeConfig\IntegrateDropbox\SDK\Dropbox
     */
    public static function get_sdk_client($account = null)
    {
        if (! empty($account)) {
            self::set_current_account($account);
        }

        return self::$_sdk_client;
    }

    public static function get_current_account()
    {
        if (empty(self::$_current_account)) {

            $accountID = isset($_COOKIE['integrate-dropbox-current_id']) ? sanitize_text_field($_COOKIE['integrate-dropbox-current_id']) : '';

            if (! empty($accountID)) {
                $account = Accounts::instance()->get_account_by_id($accountID);
                if (! empty($account)) {
                    self::set_current_account($account);
                }
            } else {

                $accounts_id = Processor::instance()->get_setting('current-accounts', null);
                $user_id = self::get_current_user_id();
                if (! empty($accounts_id) && ! empty($user_id) && isset($accounts_id[$user_id])) {
                    $account_id = $accounts_id[$user_id];
                    $account = Accounts::instance()->get_account_by_id($account_id);
                    if (! empty($account)) {
                        self::set_current_account($account);
                    }
                } else {
                    $accounts = Accounts::instance()->get_accounts();
                    $account = Accounts::instance()->get_account_by_id(array_key_first($accounts));
                    if (! empty($account)) {
                        self::set_current_account($account);
                    }
                }
            }
        }

        return self::$_current_account;
    }

    public static function set_current_account(Account $account)
    {

        if (self::$_current_account !== $account) {
            self::$_current_account = $account;

            if ($account->get_authorization()->has_access_token()) {
                if (empty(self::$_sdk_client)) {
                    self::instance();
                }
            }
        }

        $current_accounts = Processor::instance()->get_setting('current-accounts');

        $current_user = self::get_current_user_id();

        if (isset($current_accounts[$current_user])) {
            if ($current_accounts[$current_user] !== $account->get_id()) {
                $current_accounts[$current_user] = $account->get_id();
            } else {
                return self::$_current_account;
            }
        } else {
            $current_accounts[$current_user] = $account->get_id();
        }

        Processor::instance()->set_setting('current-accounts', $current_accounts);

        return self::$_current_account;
    }

    public function get_app_key()
    {
        return $this->_app_key;
    }

    public function set_app_key($key)
    {
        return $this->_app_key = $key;
    }

    public function get_app_secret()
    {
        return $this->_app_secret;
    }

    public function set_app_secret($secret)
    {
        return $this->_app_secret = $secret;
    }

    public function get_files()
    {
        // TODO: Shortcode.
    }
    public function getService()
    {
        // TODO: Shortcode.
    }

    private function closeAndExit()
    {
        $redirect_url = esc_url(admin_url("admin.php?page=integrate-dropbox"));
        add_action('admin_print_scripts', function () use ($redirect_url) {
            echo "<script type='text/javascript'>\n";
            echo "window.opener.parent.location.href = '" . esc_url($redirect_url) . "';";
            echo "window.close()";
            echo "</script>";
        });
    }

    public static function get_current_user_id()
    {
        $user_id = get_current_user_id();

        if (empty($user_id)) {
            if (! function_exists('wp_get_current_user')) {
                require_once ABSPATH . '/wp-includes/pluggable.php';
            }
            $user_id = get_current_user_id();

            if (empty($user_id)) {
                return;
            }
        }

        return 'user_' . $user_id;
    }
}
