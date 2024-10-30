<?php

namespace CodeConfig\IntegrateDropbox;

defined( 'ABSPATH' ) or exit( 'Hey, what are you doing here? You silly human!' );

class WooCommerce {
    private static $instance = null;

    public function __construct() {

        add_filter( 'woocommerce_file_download_method', [$this, 'woocommerce_file_download_method'], 10, 3 );
        add_filter( 'woocommerce_download_product_filepath', [$this, 'woocommerce_download_product_filepath'] );

    }

    public function woocommerce_download_product_filepath( $file_path ) {
        if ( strpos( $file_path, 'integrate-dropbox' ) !== false && strpos( $file_path, 'action=indbox_download_file' ) !== false ) {
            $queryString = parse_url( $file_path, PHP_URL_QUERY );
            parse_str( $queryString, $params );

            $file_path = add_query_arg( $params, admin_url( 'admin-ajax.php' ) );
        }
        
        return $file_path;
    }

    public function woocommerce_file_download_method( $method, $product_id, $file_path ) {
        if ( 'redirect' !== $method && strpos( $file_path, 'action=indbox_download_file' ) !== false ) {
            $method = 'redirect';
        }
        return $method;
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}