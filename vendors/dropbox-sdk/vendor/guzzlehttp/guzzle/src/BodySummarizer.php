<?php

namespace CodeConfig\IntegrateDropbox\vendor\GuzzleHttp;

use CodeConfig\IntegrateDropbox\vendor\Psr\Http\Message\MessageInterface;

final class BodySummarizer implements BodySummarizerInterface {
    /**
     * @var int|null
     */
    private $truncateAt;

    public function __construct( int $truncateAt = null ) {
        $this->truncateAt = $truncateAt;
    }

    /**
     * Returns a summarized message body.
     */
    public function summarize( MessageInterface $message ): ?string {
        return $this->truncateAt === null
        ? \CodeConfig\IntegrateDropbox\vendor\GuzzleHttp\Psr7\Message::bodySummary( $message )
        : \CodeConfig\IntegrateDropbox\vendor\GuzzleHttp\Psr7\Message::bodySummary( $message, $this->truncateAt );
    }
}
