<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Encryption;

interface StringEncrypter {
    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Encryption\EncryptException
     */
    public function encryptString( $value );

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Encryption\DecryptException
     */
    public function decryptString( $payload );
}
