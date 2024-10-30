<?php
namespace CodeConfig\IntegrateDropbox\SDK\Models;

class TemporaryLink extends BaseModel {

    /**
     * The temporary link
     *
     * @var string
     */
    protected $link;

    /**
     * File Metadata
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     */
    protected $metadata;

    /**
     * Create a new TemporaryLink instance
     *
     * @param array $data
     */
    public function __construct( array $data ) {
        parent::__construct( $data );
        $this->link = $this->getDataProperty( 'link' );
        $this->setMetadata();
    }

    /**
     * Set Metadata
     */
    protected function setMetadata() {
        $metadata = $this->getDataProperty( 'metadata' );
        if ( is_array( $metadata ) ) {
            $this->metadata = new FileMetadata( $metadata );
        }
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
     * Get the temporary link
     *
     * @return string
     */
    public function getLink() {
        return $this->link;
    }
}
