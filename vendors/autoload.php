<?php

function integrate_dropbox_php_client_autoload( $className ) {
    if ( false === strpos( $className, 'CodeConfig\\IntegrateDropbox' ) ) {
        return;
    }

    $allFiles = get_autoload_path();
    if ( is_array( $allFiles ) ) {
        foreach ( $allFiles as $key => $values ) {
            if ( false !== strpos( $className, $key ) ) {
                foreach ( $values as $value ) {
                    $filePath = $value . '/' . str_replace( '\\', DIRECTORY_SEPARATOR, str_replace( $key, '', $className ) ) . '.php';
                    if ( file_exists( $filePath ) ) {
                        require_once $filePath;
                    }
                }
            }
        }
    }
}

function get_autoload_path() {
    $sdkVendorDir = dirname( __FILE__ ) . '/dropbox-sdk/vendor';

    return [
        'CodeConfig\\IntegrateDropbox\\'                                => [INDBOX_INC, INDBOX_PATH . '/download'],
        'CodeConfig\\IntegrateDropbox\\App\\'                           => [INDBOX_APP],
        'CodeConfig\\IntegrateDropbox\\Vendors\\'                       => [dirname( __FILE__ ) . '/phpThumb'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Psr\\Container\\'        => [$sdkVendorDir . '/psr/container/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Psr\\Http\\Client\\'     => [$sdkVendorDir . '/psr/http-client/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\GuzzleHttp\\Psr7\\'      => [$sdkVendorDir . '/guzzlehttp/psr7/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Psr\\SimpleCache\\'      => [$sdkVendorDir . '/psr/simple-cache/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Illuminate\\Contracts\\' => [$sdkVendorDir . '/illuminate/contracts'],
        'CodeConfig\\IntegrateDropbox\\vendor\\GuzzleHttp\\'            => [$sdkVendorDir . '/guzzlehttp/guzzle/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\GuzzleHttp\\Promise\\'   => [$sdkVendorDir . '/guzzlehttp/promises/src'],
        'CodeConfig\\IntegrateDropbox\\SDK\\'                           => [dirname( __FILE__ ) . '/dropbox-sdk/src/IntegrateDropbox'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Psr\\Http\\Message\\'    => [$sdkVendorDir . '/psr/http-message/src', $sdkVendorDir . '/psr/http-factory/src'],
        'CodeConfig\\IntegrateDropbox\\vendor\\Illuminate\\Support\\'   => [$sdkVendorDir . '/illuminate/conditionable', $sdkVendorDir . '/illuminate/macroable', $sdkVendorDir . '/illuminate/collections'],
    ];
}

spl_autoload_register( 'integrate_dropbox_php_client_autoload' );
