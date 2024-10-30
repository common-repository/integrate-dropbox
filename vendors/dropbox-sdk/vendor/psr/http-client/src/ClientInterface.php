<?php

namespace CodeConfig\IntegrateDropbox\vendor\Psr\Http\Client;

use CodeConfig\IntegrateDropbox\vendor\Psr\Http\Message\RequestInterface;
use CodeConfig\IntegrateDropbox\vendor\Psr\Http\Message\ResponseInterface;

interface ClientInterface {
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \CodeConfig\IntegrateDropbox\vendor\Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest( RequestInterface $request ): ResponseInterface;
}
