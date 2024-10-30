<?php

namespace CodeConfig\IntegrateDropbox\App;
defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

use CodeConfig\IntegrateDropbox\Helpers;

class CacheRequest {
    /**
     * Set after how much time the cached request should be refreshed.
     * In seconds.
     *
     * @var int
     */
    protected $_max_cached_request_age;

    /**
     * The file name of the requested cache. This will be set in construct.
     *
     * @var string
     */
    private $_cache_name;

    /**
     * Contains the location to the cache file.
     *
     * @var string
     */
    private $_cache_location;


    // Contains the cached response
    private $_requested_response;

    /**
     * Specific identifier for current user.
     * This identifier is used for caching purposes.
     *
     * @var string
     */
    private $_user_identifier;

    public function get_user_identifier() {
        return $this->_user_identifier;
    }

    public function get_cache_name() {
        return $this->_cache_name;
    }

    public function get_cache_location() {
        return $this->_cache_location;
    }

    public function load_cache() {
    }

    public function is_cached() {
        // Check if file exists
        $file = $this->get_cache_location();

        if ( ! file_exists( $file ) ) {
            return false;
        }

        if (  ( filemtime( $this->get_cache_location() ) + $this->_max_cached_request_age ) < time() ) {
            return false;
        }

        if ( empty( $this->_requested_response ) ) {
            return false;
        }

        return true;
    }

    public function get_cached_response() {
        return $this->_requested_response;
    }

    public function add_cached_response( $response ) {
        $this->_requested_response = $response;
    }

    public static function clear_local_cache_for_shortcode( $account_id, $listtoken ) {

    }

    public static function clear_request_cache() {
    }

}
