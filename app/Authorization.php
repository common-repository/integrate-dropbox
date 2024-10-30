<?php

namespace CodeConfig\IntegrateDropbox\App;
defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class Authorization {
    private $_isValid;
    private $_accountId;
    private $_accessTokens;
    private $_accessTokensKey = 'integrate_dropbox_access_tokens';

    public function __construct( Account $account ) {
        $this->_accessTokens = get_option( $this->_accessTokensKey, null );
        $this->_accountId = $account->get_id();
    }

    public function set_access_token( $accessToken ) {
        $this->_accessTokens[$accessToken->getAccountId()] = $accessToken;

        $success = update_option( $this->_accessTokensKey, $this->_accessTokens );

        return $success;
    }

    public function remove_token() {
        $this->_accessTokens = [];
        $success = update_option( $this->_accessTokensKey, $this->_accessTokens );

        return $success;
    }

    public function get_access_token( $id ) {
        return isset( $this->_accessTokens[$id] ) ? $this->_accessTokens[$id] : null;
    }

    public function has_access_token() {
        $account = App::get_current_account();
        if ( $this->_accessTokens[$account->get_id()] ) {
            return true;
        }
        return false;
    }

    public function get_account_id() {
        return $this->_accountId;
    }

    public function set_account_id( $accountId ) {
        $this->_accountId = $accountId;
        return $this;
    }

    public function get_is_valid() {
        return $this->_isValid;
    }

    public function set_is_valid( $isValid ) {
        $this->_isValid = $isValid;
        return $this;
    }
}