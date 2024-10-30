<?php
namespace CodeConfig\IntegrateDropbox\SDK\Http;

use CodeConfig\IntegrateDropbox\SDK\DropboxFile;

/**
 * RequestBodyStream
 */
class RequestBodyStream implements RequestBodyInterface {

    /**
     * File to be sent with the Request
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\DropboxFile
     */
    protected $file;

    /**
     * Create a new RequestBodyStream instance
     *
     * @param \CodeConfig\IntegrateDropbox\SDK\DropboxFile $file
     */
    public function __construct( DropboxFile $file ) {
        $this->file = $file;
    }

    /**
     * Get the Body of the Request
     *
     * @return string
     */
    public function getBody() {
        return $this->file->getContents();
    }
}
