<?php
namespace CodeConfig\IntegrateDropbox\SDK\Models;

use CodeConfig\IntegrateDropbox\SDK\DropboxFile;

class File extends BaseModel {

    /**
     * The file contents
     *
     * @var string|DropboxFile
     */
    protected $contents;

    /**
     * File Metadata
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     */
    protected $metadata;

    /**
     * Create a new File instance
     *
     * @param array  $data
     * @param string|DropboxFile $contents
     */
    public function __construct( array $data, $contents ) {
        parent::__construct( $data );
        $this->contents = $contents;
        $this->metadata = new FileMetadata( $data );
    }

    /**
     * The metadata for the file
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     */
    public function getMetadata() {
        return $this->metadata;
    }

    /**
     * Get the file contents
     *
     * @return string
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getContents() {
        if ( $this->contents instanceof DropboxFile ) {
            return $this->contents->getContents();
        }
        return $this->contents;
    }
}
