<?php
namespace CodeConfig\IntegrateDropbox\SDK\Security;

use CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException;

/**
 * @inheritdoc
 */
class McryptRandomStringGenerator implements RandomStringGeneratorInterface {
    use RandomStringGeneratorTrait;

    /**
     * The error message when generating the string fails.
     *
     * @const string
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from mcrypt_create_iv(). ';

    /**
     * Create a new McryptRandomStringGenerator instance
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function __construct() {
        if ( ! function_exists( 'mcrypt_create_iv' ) ) {
            throw new DropboxClientException(
                static::ERROR_MESSAGE .
                'The function mcrypt_create_iv() does not exist.'
            );
        }
    }

    /**
     * Get a randomly generated secure token
     *
     * @param  int $length Length of the string to return
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @return string
     */
    public function generateString( $length ) {
        //Create Binary String
        $binaryString = mcrypt_create_iv( $length, MCRYPT_DEV_URANDOM );

        //Unable to create binary string
        if ( $binaryString === false ) {
            throw new DropboxClientException(
                static::ERROR_MESSAGE .
                'mcrypt_create_iv() returned an error.'
            );
        }

        //Convert binary to hex
        return $this->binToHex( $binaryString, $length );
    }
}
