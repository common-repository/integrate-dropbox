<?php
namespace CodeConfig\IntegrateDropbox\SDK\Http\Clients;

/**
 * DropboxHttpClientInterface
 */
interface DropboxHttpClientInterface {
    /**
     * Send request to the server and fetch the raw response
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param  string|resource|\Psr\Http\Message\StreamInterface|null $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Http\DropboxRawResponse Raw response from the server
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function send( $url, $method, $body, $headers = [], $options = [] );
}
