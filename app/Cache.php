<?php

namespace CodeConfig\IntegrateDropbox\App;
defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

use CodeConfig\IntegrateDropbox\App\App;
use CodeConfig\IntegrateDropbox\App\CacheNode;
use CodeConfig\IntegrateDropbox\App\Entry;
use CodeConfig\IntegrateDropbox\Helpers;

class Cache {
    /**
     * The single instance of the class.
     *
     * @var Cache
     */
    protected static $_instance;

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

    /**
     * Contains the file handle in case the plugin has to work
     * with a file for unlocking/locking.
     *
     * @var type
     */
    private $_cache_file_handle;
    private $_cache_type;

    /**
     * $_nodes contains all the cached files that are present
     * in the Cache File or Database.
     *
     * @var CacheNode[]
     */
    private $_nodes = [];

    /**
     * Is set to true when a change has been made in the cache.
     * Forcing the plugin to save the cache when needed.
     *
     * @var bool
     */
    private $_updated = false;

    public function __construct() {

    }

    public function __destruct() {
        $this->update_cache();
    }

    /**
     * Cache Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Cache - Cache instance
     *
     * @static
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function instance_unload() {
        if ( is_null( self::$_instance ) ) {
            return;
        }

        self::instance()->update_cache();
        self::$_instance = null;
    }

    public function reset_cache() {
        $this->_nodes = [];
        $this->update_cache();
    }

    public function update_cache() {

    }

    /**
     * @param string $value
     * @param string $findby
     *
     * @return CacheNode|false
     */
    public function is_cached( $value, $findby = 'id' ) {
        // Find the node by ID/NAME
        $node = null;
        if ( 'id' === $findby ) {
            $node = $this->get_node_by_id( $value );
        }

        // Return if nothing can be found in the cache
        if ( empty( $node ) ) {
            return false;
        }

        return $node;
    }

    /**
     * @param \CodeConfig\IntegrateDropbox\App\Entry $entry
     *
     * @return CacheNode
     */
    public function add_to_cache( Entry $entry ) {
        // Check if entry is present in cache
        $cached_node = $this->get_node_by_id( $entry->get_id() );

        /* If entry is not yet present in the cache,
         * create a new CacheNode
         */
        if ( false === $cached_node ) {
            $cached_node = $this->add_node( $entry );
            $this->set_updated();
        }

        // Check if the added file has another rev
        if ( $cached_node->get_rev() !== $entry->get_rev() ) {
            $cached_node->set_rev( $entry->get_rev() );

            // Remove the thumbnails if there is a new version available
            // $cached_node->remove_thumbnails();
        }

        // Return the cached CacheNode
        return $cached_node;
    }

    public function remove_from_cache( $entry_id ) {
        $node = $this->get_node_by_id( $entry_id );
        $this->set_updated();

        return true;
    }

    public function get_node_by_id( $id ) {
        if ( ! isset( $this->_nodes[$id] ) ) {
            return false;
        }

        return $this->_nodes[$id];
    }

    public function has_nodes() {
        return count( $this->_nodes ) > 0;
    }

    /**
     * @return CacheNode[]
     */
    public function get_nodes() {
        return $this->_nodes;
    }

    public function add_node( Entry $entry ) {
        $cached_node = new CacheNode(
            [
                '_id'         => $entry->get_id(),
                '_account_id' => App::get_current_account()->get_id(),
                '_path'       => $entry->get_path(),
                '_rev'        => $entry->get_rev(),
            ]
        );

        return $this->set_node( $cached_node );
    }

    public function set_node( CacheNode $node ) {
        $id = $node->get_id();
        $this->_nodes[$id] = $node;

        return $this->_nodes[$id];
    }

    public function is_updated() {
        return $this->_updated;
    }

    public function set_updated( $value = true ) {
        $this->_updated = (bool) $value;

        return $this->_updated;
    }

    public function get_cache_name() {
        return $this->_cache_name;
    }

    public function get_cache_type() {
        return $this->_cache_type;
    }

    public function get_cache_location() {
        return $this->_cache_location;
    }
}
