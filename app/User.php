<?php
namespace CodeConfig\IntegrateDropbox\App;
defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class User {
    /**
     * The single instance of the class.
     *
     * @var User
     */
    protected static $_instance;

    private static $_can_view = true;
    private static $_can_preview = true;
    private static $_can_download = true;
    private static $_can_download_zip = true;
    private static $_can_delete_files = true;
    private static $_can_delete_folders = true;
    private static $_can_rename_files = true;
    private static $_can_rename_folders = true;
    private static $_can_add_folders = true;
    private static $_can_create_document = true;
    private static $_can_upload = true;
    private static $_can_move_files = true;
    private static $_can_move_folders = true;
    private static $_can_copy_files = true;
    private static $_can_copy_folders = true;
    private static $_can_share = true;
    private static $_can_edit_description = true;
    private static $_can_deeplink = true;
    private static $_can_search = true;
    private static $_locale;

    public function __construct() {
        self::init();
    }

    public static function reset() {
        self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * User Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return User - User instance
     *
     * @static
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function can_view() {
        self::instance();

        return self::$_can_view;
    }

    public static function can_preview() {
        self::instance();

        return self::$_can_preview;
    }

    public static function can_download() {
        self::instance();

        return self::$_can_download;
    }

    public static function can_download_zip() {
        self::instance();

        return self::$_can_download_zip;
    }

    public static function can_delete_files() {
        self::instance();

        return self::$_can_delete_files;
    }

    public static function can_delete_folders() {
        self::instance();

        return self::$_can_delete_folders;
    }

    public static function can_rename_files() {
        self::instance();

        return self::$_can_rename_files;
    }

    public static function can_rename_folders() {
        self::instance();

        return self::$_can_rename_folders;
    }

    public static function can_add_folders() {
        self::instance();

        return self::$_can_add_folders;
    }

    public static function can_create_document() {
        self::instance();

        return self::$_can_create_document;
    }

    public static function can_upload() {
        self::instance();

        return self::$_can_upload;
    }

    public static function can_move_files() {
        self::instance();

        return self::$_can_move_files;
    }

    public static function can_move_folders() {
        self::instance();

        return self::$_can_move_folders;
    }

    public static function can_copy_files() {
        self::instance();

        return self::$_can_copy_files;
    }

    public static function can_copy_folders() {
        self::instance();

        return self::$_can_copy_folders;
    }

    public static function can_share() {
        self::instance();

        return self::$_can_share;
    }

    public static function can_deeplink() {
        self::instance();

        return self::$_can_deeplink;
    }

    public static function can_edit_description() {
        self::instance();

        return self::$_can_edit_description;
    }

    public static function can_search() {
        self::instance();

        return self::$_can_search;
    }

    public static function get_permissions_hash() {
        self::instance();

        $class = new \ReflectionClass( '\\CodeConfig\\IntegrateDropbox\\App\\User' );
        $data = $class->getStaticProperties();

        unset( $data['_instance'] );
        $data = json_encode( $data );

        return md5( $data );
    }

    private static function init() {
        // TODO ....
    }
}
