<?php

namespace CodeConfig\IntegrateDropbox\App;

defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

use CodeConfig\IntegrateDropbox\App\App;
use CodeConfig\IntegrateDropbox\App\Processor;
use Error;

class Accounts {

    /**
     * @var Accounts
     */
    private static $_instance;

    /**
     * @var \CodeConfig\IntegrateDropbox\App\Account
     */
    private $_accounts;

    public function __construct() {
        $this->_init_accounts();
    }

    /**
     * @return Accounts
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @param \CodeConfig\IntegrateDropbox\App\Account
     *
     * @return Accounts
     */
    public function add_account( Account $account ) {
        $this->_accounts[$account->get_id()] = $account;

        // Assuming $account is an object with a get_id() method
        if ( isset( $account ) && is_object( $account ) && method_exists( $account, 'get_id' ) ) {
            $user_id = get_current_user_id();

            if (
                empty( $user_id )
            ) {
                if ( ! function_exists( 'wp_get_current_user' ) ) {
                    $path = ABSPATH . '/wp-includes/pluggable.php';
                    if ( file_exists( $path ) ) {
                        require_once ABSPATH . '/wp-includes/pluggable.php';
                    } else {
                        throw new Error( 'Something went wrong!' );
                    }
                }
                $user_id = get_current_user_id();

                if ( empty( $user_id ) ) {
                    return;
                }
            }

            $current_user = 'user_' . $user_id;

            $account_id = [$current_user => $account->get_id()];
            Processor::instance()->set_setting( 'current-accounts', $account_id );
        } else {
            // Handle the case where $account is not properly defined or does not have a get_id() method
            // You may want to log an error or take appropriate action
            throw new Error( 'Session not authorize' );
        }

        $this->save();

        return $this;
    }

    /**
     * @param string $account_id
     *
     * @return Accounts
     */
    public function remove_account( $account_id ) {
        $account = $this->get_account_by_id( $account_id );

        if ( null === $account ) {
            return;
        }

        $current_accounts = Processor::instance()->get_setting( 'current-accounts', null );

        $user_id = App::get_current_user_id();

        unset( $current_accounts[$user_id] );
        Processor::instance()->set_setting( 'current-accounts', $current_accounts );

        $account->get_authorization()->remove_token();

        unset( $this->_accounts[$account_id] );

        $this->save();

        return $this;
    }

    public function save() {
        return Processor::instance()->set_setting( 'accounts', $this->_accounts );
    }

    /**
     *  @return \CodeConfig\IntegrateDropbox\App\Account
     */
    public function get_account_by_id( $id ) {
        if ( empty( $this->_accounts ) ) {
            $this->_accounts =
            Processor::instance()->get_setting( 'accounts', [] );
        }

        $account = isset($this->_accounts[$id]) ? $this->_accounts[$id] : null;
        return $account;
    }

    /**
     * @return array
     */
    public function get_accounts() {
        return $this->_accounts;
    }

    /**
     * @return \CodeConfig\IntegrateDropbox\App\Account
     */
    public function get_primary_account() {
        if ( empty( $this->_accounts ) ) {
            $this->_accounts =
            Processor::instance()->get_setting( 'accounts', [] );
        }

        $account = is_array( $this->_accounts ) ? reset( $this->_accounts ) : null;
        return $account;
    }

    private function _init_accounts() {
        $this->_accounts = Processor::instance()->get_setting( 'accounts', [] );
    }
}
