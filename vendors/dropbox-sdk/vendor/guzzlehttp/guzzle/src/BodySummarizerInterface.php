<?php

namespace CodeConfig\IntegrateDropbox\vendor\GuzzleHttp;

use CodeConfig\IntegrateDropbox\vendor\Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface {
    /**
     * Returns a summarized message body.
     */
    public function summarize( MessageInterface $message ): ?string;
}
