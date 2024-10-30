<?php
namespace CodeConfig\IntegrateDropbox\SDK\Security;

use CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException;
use InvalidArgumentException;

/**
 * Thanks to Facebook
 *
 * @link https://developers.facebook.com/docs/php/RandomStringGeneratorInterface
 */
class RandomStringGeneratorFactory {
    /**
     * Make a Random String Generator
     *
     * @param  null|string|\CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorInterface $generator
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorInterface
     */
    public static function makeRandomStringGenerator( $generator = null ) {
        //No generator provided
        if ( is_null( $generator ) ) {
            //Generate default random string generator
            return static::defaultRandomStringGenerator();
        }

        //RandomStringGeneratorInterface
        if ( $generator instanceof RandomStringGeneratorInterface ) {
            return $generator;
        }

        // Mcrypt
        if ( 'mcrypt' === $generator ) {
            return new McryptRandomStringGenerator();
        }

        //OpenSSL
        if ( 'openssl' === $generator ) {
            return new OpenSslRandomStringGenerator();
        }

        //Invalid Argument
        throw new InvalidArgumentException( 'The random string generator must be set to "mcrypt", "openssl" or be an instance of CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorInterface' );
    }

    /**
     * Get Default Random String Generator
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @return RandomStringGeneratorInterface
     */
    protected static function defaultRandomStringGenerator() {
        //Mcrypt
        if ( function_exists( 'mcrypt_create_iv' ) && version_compare( PHP_VERSION, '7.1', '<' ) ) {
            return new McryptRandomStringGenerator();
        }

        //OpenSSL
        if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
            return new OpenSslRandomStringGenerator();
        }

        //Unable to create a random string generator
        throw new DropboxClientException( 'Unable to detect a cryptographically secure pseudo-random string generator.' );
    }
}
