<?php

namespace CodeConfig\IntegrateDropbox\SDK;

use CodeConfig\IntegrateDropbox\SDK\Authentication\DropboxAuthHelper;
use CodeConfig\IntegrateDropbox\SDK\Authentication\OAuth2Client;
use CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException;
use CodeConfig\IntegrateDropbox\SDK\Http\Clients\DropboxHttpClientFactory;
use CodeConfig\IntegrateDropbox\SDK\Models\Account;
use CodeConfig\IntegrateDropbox\SDK\Models\AccountList;
use CodeConfig\IntegrateDropbox\SDK\Models\AsyncJob;
use CodeConfig\IntegrateDropbox\SDK\Models\CopyReference;
use CodeConfig\IntegrateDropbox\SDK\Models\DeletedMetadata;
use CodeConfig\IntegrateDropbox\SDK\Models\File;
use CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata;
use CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata;
use CodeConfig\IntegrateDropbox\SDK\Models\ModelCollection;
use CodeConfig\IntegrateDropbox\SDK\Models\ModelFactory;
use CodeConfig\IntegrateDropbox\SDK\Models\Tag;
use CodeConfig\IntegrateDropbox\SDK\Models\TemporaryLink;
use CodeConfig\IntegrateDropbox\SDK\Models\Thumbnail;
use CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorFactory;
use CodeConfig\IntegrateDropbox\SDK\Store\PersistentDataStoreFactory;

/**
 * Dropbox
 */
class Dropbox {
    /**
     * Uploading a file with the 'uploadFile' method, with the file's
     * size less than this value (~8 MB), the simple `upload` method will be
     * used, if the file size exceed this value (~8 MB), the `startUploadSession`,
     * `appendUploadSession` & `finishUploadSession` methods will be used
     * to upload the file in chunks.
     *
     * @const int
     */
    const AUTO_CHUNKED_UPLOAD_THRESHOLD = 8000000;

    /**
     * The Chunk Size the file will be
     * split into and uploaded (~4 MB)
     *
     * @const int
     */
    const DEFAULT_CHUNK_SIZE = 4000000;

    /**
     * Response header containing file metadata
     *
     * @const string
     */
    const METADATA_HEADER = 'Dropbox-Api-Result';

    /**
     * The Dropbox App
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\DropboxApp
     */
    protected $app;

    /**
     * OAuth2 Access Token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Dropbox Client
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\DropboxClient
     */
    protected $client;

    /**
     * OAuth2 Client
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\Authentication\OAuth2Client
     */
    protected $oAuth2Client;

    /**
     * Random String Generator
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorInterface
     */
    protected $randomStringGenerator;

    /**
     * Persistent Data Store
     *
     * @var \CodeConfig\IntegrateDropbox\SDK\Store\PersistentDataStoreInterface
     */
    protected $persistentDataStore;

    /**
     * Create a new Dropbox instance
     *
     * @param \CodeConfig\IntegrateDropbox\SDK\DropboxApp
     * @param array $config Configuration Array
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function __construct( DropboxApp $app, array $config = [] ) {
        //Configuration
        $config = array_merge( [
            'http_client_handler'     => null,
            'random_string_generator' => null,
            'persistent_data_store'   => null,
        ], $config );

        //Set the app
        $this->app = $app;

        //Set the access token
        $this->setAccessToken( $app->getAccessToken() );

        //Make the HTTP Client
        $httpClient = DropboxHttpClientFactory::make( $config['http_client_handler'] );

        //Make and Set the DropboxClient
        $this->client = new DropboxClient( $httpClient );

        //Make and Set the Random String Generator
        $this->randomStringGenerator = RandomStringGeneratorFactory::makeRandomStringGenerator( $config['random_string_generator'] );

        //Make and Set the Persistent Data Store
        $this->persistentDataStore = PersistentDataStoreFactory::makePersistentDataStore( $config['persistent_data_store'] );
    }

    /**
     * Get Dropbox Auth Helper
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Authentication\DropboxAuthHelper
     */
    public function getAuthHelper() {
        return new DropboxAuthHelper(
            $this->getOAuth2Client(),
            $this->getRandomStringGenerator(),
            $this->getPersistentDataStore()
        );
    }

    /**
     * Get OAuth2Client
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Authentication\OAuth2Client
     */
    public function getOAuth2Client() {
        if ( ! $this->oAuth2Client instanceof OAuth2Client ) {
            return new OAuth2Client(
                $this->getApp(),
                $this->getClient(),
                $this->getRandomStringGenerator()
            );
        }

        return $this->oAuth2Client;
    }

    /**
     * Get the Dropbox App.
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxApp Dropbox App
     */
    public function getApp() {
        return $this->app;
    }

    /**
     * Get the Client
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxClient
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Get the Random String Generator
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Security\RandomStringGeneratorInterface
     */
    public function getRandomStringGenerator() {
        return $this->randomStringGenerator;
    }

    /**
     * Get Persistent Data Store
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Store\PersistentDataStoreInterface
     */
    public function getPersistentDataStore() {
        return $this->persistentDataStore;
    }

    /**
     * Get the Metadata for a file or folder
     *
     * @param  string $path   Path of the file or folder
     * @param  array  $params Additional Params
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata | \CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_metadata
     *
     */
    public function getMetadata( $path, array $params = [] ) {
        //Root folder is unsupported
        if ( $path === '/' ) {
            throw new DropboxClientException( "Metadata for the root folder is unsupported." );
        }

        //Set the path
        $params['path'] = $path;

        //Get File Metadata
        $response = $this->postToAPI( '/files/get_metadata', $params );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Make a HTTP POST Request to the API endpoint type
     *
     * @param  string $endpoint API Endpoint to send Request to
     * @param  array $params Request Query Params
     * @param  string $accessToken Access Token to send with the Request
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxResponse
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function postToAPI( $endpoint, array $params = [], $accessToken = null ) {
        return $this->sendRequest( "POST", $endpoint, 'api', $params, $accessToken );
    }

    /**
     * Make Request to the API
     *
     * @param  string      $method       HTTP Request Method
     * @param  string      $endpoint     API Endpoint to send Request to
     * @param  string      $endpointType Endpoint type ['api'|'content']
     * @param  array       $params       Request Query Params
     * @param  string      $accessToken  Access Token to send with the Request
     * @param  DropboxFile $responseFile Save response to the file
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxResponse
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function sendRequest( $method, $endpoint, $endpointType = 'api', array $params = [], $accessToken = null, DropboxFile $responseFile = null ) {
        //Access Token
        $accessToken = $this->getAccessToken() ? $this->getAccessToken() : $accessToken;

        if ( $this->getOAuth2Client()->isAccessTokenExpired() ) {
            do_action( 'integrate-dropbox-refresh-token', \CodeConfig\IntegrateDropbox\App\App::get_current_account() );
            $accessToken = $this->getAccessToken();
        }

        //Make a DropboxRequest object
        $request = new DropboxRequest( $method, $endpoint, $accessToken, $endpointType, $params );

        //Make a DropboxResponse object if a response should be saved to the file
        $response = $responseFile ? new DropboxResponseToFile( $request, $responseFile ) : null;

        //Send Request through the DropboxClient
        //Fetch and return the Response
        return $this->getClient()->sendRequest( $request, $response );
    }

    /**
     * Get the Access Token.
     *
     * @return CodeConfig\IntegrateDropbox\SDK\Models\AccessToken
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Set the Access Token.
     *
     * @param CodeConfig\IntegrateDropbox\SDK\Models\AccessToken
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Dropbox Dropbox Client
     */
    public function setAccessToken( $accessToken ) {
        $this->accessToken = $accessToken;
        $this->getApp()->setAccessToken( $this->accessToken );

        return $this;
    }

    /**
     * Make Model from DropboxResponse
     *
     * @param  DropboxResponse $response
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\ModelInterface
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function makeModelFromResponse( DropboxResponse $response ) {
        //Get the Decoded Body
        $body = $response->getDecodedBody();

        if ( is_null( $body ) ) {
            $body = [];
        }

        //Make and Return the Model
        return ModelFactory::make( $body );
    }

    /**
     * Get the contents of a Folder
     *
     * @param  string $path Path to the folder. Defaults to root.
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\MetadataCollection
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function listFolder( $path = null, array $params = [] ) {
        //Specify the root folder as an
        //empty string rather than as "/"
        if ( $path === '/' ) {
            $path = "";
        }

        //Set the path
        $params['path'] = $path;

        //Get File Metadata
        $response = $this->postToAPI( '/files/list_folder', $params );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Paginate through all files and retrieve updates to the folder,
     * using the cursor retrieved from listFolder or listFolderContinue
     *
     * @param  string $cursor The cursor returned by your
     *                        last call to listFolder or listFolderContinue
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-continue
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\MetadataCollection
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function listFolderContinue( $cursor ) {
        $response = $this->postToAPI( '/files/list_folder/continue', ['cursor' => $cursor] );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Get a cursor for the folder's state.
     *
     * @param  string $path   Path to the folder. Defaults to root.
     * @param  array  $params Additional Params
     *
     * @return string The Cursor for the folder's state
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-get_latest_cursor
     *
     */
    public function listFolderLatestCursor( $path, array $params = [] ) {
        //Specify the root folder as an
        //empty string rather than as "/"
        if ( $path === '/' ) {
            $path = "";
        }

        //Set the path
        $params['path'] = $path;

        //Fetch the cursor
        $response = $this->postToAPI( '/files/list_folder/get_latest_cursor', $params );

        //Retrieve the cursor
        $body = $response->getDecodedBody();
        $cursor = isset( $body['cursor'] ) ? $body['cursor'] : false;

        //No cursor returned
        if ( ! $cursor ) {
            throw new DropboxClientException( "Could not retrieve cursor. Something went wrong." );
        }

        //Return the cursor
        return $cursor;
    }

    /**
     * Get Revisions of a File
     *
     * @param  string $path Path to the file
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_revisions
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\ModelCollection
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function listRevisions( $path, array $params = [] ) {
        //Set the Path
        $params['path'] = $path;

        //Fetch the Revisions
        $response = $this->postToAPI( '/files/list_revisions', $params );

        //The file metadata of the entries, returned by this
        //endpoint doesn't include a '.tag' attribute, which
        //is used by the ModelFactory to resolve the correct
        //model. But since we know that revisions returned
        //are file metadata objects, we can explicitly cast
        //them as \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata manually.
        $body = $response->getDecodedBody();
        $entries = isset( $body['entries'] ) ? $body['entries'] : [];
        $processedEntries = [];

        foreach ( $entries as $entry ) {
            $processedEntries[] = new FileMetadata( $entry );
        }

        return new ModelCollection( $processedEntries );
    }

    /**
     * Search a folder for files/folders
     *
     * @param  string $path Path to search
     * @param  string $query Search Query
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-search
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\SearchResults
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function search( $path, $query, array $params = [] ) {
        //Specify the root folder as an
        //empty string rather than as "/"
        if ( $path === '/' ) {
            $path = "";
        }

        //Set the path and query
        $params['path'] = $path;
        $params['query'] = $query;

        //Fetch Search Results
        $response = $this->postToAPI( '/files/search', $params );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Create a folder at the given path
     *
     * @param  string  $path       Path to create
     * @param  boolean $autorename Auto Rename File
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-create_folder
     *
     */
    public function createFolder( $path, $autorename = false ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Create Folder
        $response = $this->postToAPI( '/files/create_folder', ['path' => $path, 'autorename' => $autorename] );

        //Fetch the Metadata
        $body = $response->getDecodedBody();

        //Make and Return the Model
        return new FolderMetadata( $body );
    }

    /**
     * Delete a file or folder at the given path
     *
     * @param  string $path Path to file/folder to delete
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\DeletedMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-delete
     *
     */
    public function delete( $path ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Delete
        $response = $this->postToAPI( '/files/delete_v2', ['path' => $path] );
        $body = $response->getDecodedBody();

        //Response doesn't have Metadata
        if ( ! isset( $body['metadata'] ) || ! is_array( $body['metadata'] ) ) {
            throw new DropboxClientException( "Invalid Response." );
        }

        return new DeletedMetadata( $body['metadata'] );
    }

    /**
     * Move a file or folder to a different location
     *
     * @param  string $fromPath Path to be moved
     * @param  string $toPath   Path to be moved to
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\DeletedMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-move
     *
     */
    public function move( $fromPath, $toPath ) {
        //From and To paths cannot be null
        if ( is_null( $fromPath ) || is_null( $toPath ) ) {
            throw new DropboxClientException( "From and To paths cannot be null." );
        }

        //Response
        $response = $this->postToAPI( '/files/move', ['from_path' => $fromPath, 'to_path' => $toPath] );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Copy a file or folder to a different location
     *
     * @param  string $fromPath Path to be copied
     * @param  string $toPath   Path to be copied to
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\DeletedMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy
     *
     */
    public function copy( $fromPath, $toPath ) {
        //From and To paths cannot be null
        if ( is_null( $fromPath ) || is_null( $toPath ) ) {
            throw new DropboxClientException( "From and To paths cannot be null." );
        }

        //Response
        $response = $this->postToAPI( '/files/copy', ['from_path' => $fromPath, 'to_path' => $toPath] );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Restore a file to the specific version
     *
     * @param  string $path Path to the file to restore
     * @param  string $rev  Revision to store for the file
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\DeletedMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-restore
     *
     */
    public function restore( $path, $rev ) {
        //Path and Revision cannot be null
        if ( is_null( $path ) || is_null( $rev ) ) {
            throw new DropboxClientException( "Path and Revision cannot be null." );
        }

        //Response
        $response = $this->postToAPI( '/files/restore', ['path' => $path, 'rev' => $rev] );

        //Fetch the Metadata
        $body = $response->getDecodedBody();

        //Make and Return the Model
        return new FileMetadata( $body );
    }

    /**
     * Get Copy Reference
     *
     * @param  string $path Path to the file or folder to get a copy reference to
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\CopyReference
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_reference-get
     *
     */
    public function getCopyReference( $path ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Get Copy Reference
        $response = $this->postToAPI( '/files/copy_reference/get', ['path' => $path] );
        $body = $response->getDecodedBody();

        //Make and Return the Model
        return new CopyReference( $body );
    }

    /**
     * Save Copy Reference
     *
     * @param  string $path          Path to the file or folder to get a copy reference to
     * @param  string $copyReference Copy reference returned by getCopyReference
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_reference-save
     *
     */
    public function saveCopyReference( $path, $copyReference ) {
        //Path and Copy Reference cannot be null
        if ( is_null( $path ) || is_null( $copyReference ) ) {
            throw new DropboxClientException( "Path and Copy Reference cannot be null." );
        }

        //Save Copy Reference
        $response = $this->postToAPI( '/files/copy_reference/save', ['path' => $path, 'copy_reference' => $copyReference] );
        $body = $response->getDecodedBody();

        //Response doesn't have Metadata
        if ( ! isset( $body['metadata'] ) || ! is_array( $body['metadata'] ) ) {
            throw new DropboxClientException( "Invalid Response." );
        }

        //Make and return the Model
        return ModelFactory::make( $body['metadata'] );
    }

    /**
     * Get a temporary link to stream contents of a file
     *
     * @param  string $path Path to the file you want a temporary link to
     *
     * https://www.dropbox.com/developers/documentation/http/documentation#files-get_temporary_link
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\TemporaryLink
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getTemporaryLink( $path ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Get Temporary Link
        $response = $this->postToAPI( '/files/get_temporary_link', ['path' => $path] );

        //Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Save a specified URL into a file in user's Dropbox
     *
     * @param  string $path Path where the URL will be saved
     * @param  string $url  URL to be saved
     *
     * @return string Async Job ID
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-save_url
     *
     */
    public function saveUrl( $path, $url ) {
        //Path and URL cannot be null
        if ( is_null( $path ) || is_null( $url ) ) {
            throw new DropboxClientException( "Path and URL cannot be null." );
        }

        //Save URL
        $response = $this->postToAPI( '/files/save_url', ['path' => $path, 'url' => $url] );
        $body = $response->getDecodedBody();

        if ( ! isset( $body['async_job_id'] ) ) {
            throw new DropboxClientException( "Could not retrieve Async Job ID." );
        }

        //Return the Async Job ID
        return $body['async_job_id'];
    }

    /**
     * Save a specified URL into a file in user's Dropbox
     *
     * @param $asyncJobId
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata|string Status (failed|in_progress) or FileMetadata (if complete)
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link     https://www.dropbox.com/developers/documentation/http/documentation#files-save_url-check_job_status
     *
     */
    public function checkJobStatus( $asyncJobId ) {
        //Async Job ID cannot be null
        if ( is_null( $asyncJobId ) ) {
            throw new DropboxClientException( "Async Job ID cannot be null." );
        }

        //Get Job Status
        $response = $this->postToAPI( '/files/save_url/check_job_status', ['async_job_id' => $asyncJobId] );
        $body = $response->getDecodedBody();

        //Status
        $status = isset( $body['.tag'] ) ? $body['.tag'] : '';

        //If status is complete
        if ( $status === 'complete' ) {
            return new FileMetadata( $body );
        }

        //Return the status
        return $status;
    }

    /**
     * Upload a File to Dropbox
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  string $path Path to upload the file to
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function upload( $dropboxFile, $path, array $params = [] ) {
        //Make Dropbox File
        $dropboxFile = $this->makeDropboxFile( $dropboxFile );

        //If the file is larger than the Chunked Upload Threshold
        if ( $dropboxFile->getSize() > static::AUTO_CHUNKED_UPLOAD_THRESHOLD ) {
            //Upload the file in sessions/chunks
            return $this->uploadChunked( $dropboxFile, $path, null, null, $params );
        }

        //Simple file upload
        return $this->simpleUpload( $dropboxFile, $path, $params );
    }

    /**
     * Make DropboxFile Object
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  int                $maxLength   Max Bytes to read from the file
     * @param  int                $offset      Seek to specified offset before reading
     * @param  string             $mode        The type of access
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxFile
     */
    public function makeDropboxFile( $dropboxFile, $maxLength = null, $offset = null, $mode = DropboxFile::MODE_READ ) {
        //Uploading file by file path
        if ( ! $dropboxFile instanceof DropboxFile ) {
            //Create a DropboxFile Object
            $dropboxFile = new DropboxFile( $dropboxFile, $mode );
        } elseif ( $mode !== $dropboxFile->getMode() ) {
            //Reopen the file with expected mode
            $dropboxFile->close();
            $dropboxFile = new DropboxFile( $dropboxFile->getFilePath(), $mode );
        }

        if ( ! is_null( $offset ) ) {
            $dropboxFile->setOffset( $offset );
        }

        if ( ! is_null( $maxLength ) ) {
            $dropboxFile->setMaxLength( $maxLength );
        }

        //Return the DropboxFile Object
        return $dropboxFile;
    }

    /**
     * Upload file in sessions/chunks
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  string $path Path to save the file to, on Dropbox
     * @param  int $fileSize The size of the file
     * @param  int $chunkSize The amount of data to upload in each chunk
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-start
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-finish
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-append_v2
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function uploadChunked( $dropboxFile, $path, $fileSize = null, $chunkSize = null, array $params = [] ) {
        //Make Dropbox File
        $dropboxFile = $this->makeDropboxFile( $dropboxFile );

        //No file size specified explicitly
        if ( is_null( $fileSize ) ) {
            $fileSize = $dropboxFile->getSize();
        }

        //No chunk size specified, use default size
        if ( is_null( $chunkSize ) ) {
            $chunkSize = static::DEFAULT_CHUNK_SIZE;
        }

        //If the fileSize is smaller
        //than the chunk size, we'll
        //make the chunk size relatively
        //smaller than the file size
        if ( $fileSize <= $chunkSize ) {
            $chunkSize = intval( $fileSize / 2 );
        }

        //Start the Upload Session with the file path
        //since the DropboxFile object will be created
        //again using the new chunk size.
        $sessionId = $this->startUploadSession( $dropboxFile->getFilePath(), $chunkSize );

        //Uploaded
        $uploaded = $chunkSize;

        //Remaining
        $remaining = $fileSize - $chunkSize;

        //While the remaining bytes are
        //more than the chunk size, append
        //the chunk to the upload session.
        while ( $remaining > $chunkSize ) {
            //Append the next chunk to the Upload session
            $sessionId = $this->appendUploadSession( $dropboxFile, $sessionId, $uploaded, $chunkSize );

            //Update remaining and uploaded
            $uploaded = $uploaded + $chunkSize;
            $remaining = $remaining - $chunkSize;
        }

        //Finish the Upload Session and return the Uploaded File Metadata
        return $this->finishUploadSession( $dropboxFile, $sessionId, $uploaded, $remaining, $path, $params );
    }

    /**
     * Start an Upload Session
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  int                $chunkSize   Size of file chunk to upload
     * @param  boolean            $close       Closes the session for "appendUploadSession"
     *
     * @return string Unique identifier for the upload session
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-start
     *
     */
    public function startUploadSession( $dropboxFile, $chunkSize = -1, $close = false ) {
        //Make Dropbox File with the given chunk size
        $dropboxFile = $this->makeDropboxFile( $dropboxFile, $chunkSize );

        //Set the close param
        $params = [
            'close' => $close ? true : false,
            'file'  => $dropboxFile,
        ];

        //Upload File
        $file = $this->postToContent( '/files/upload_session/start', $params );
        $body = $file->getDecodedBody();

        //Cannot retrieve Session ID
        if ( ! isset( $body['session_id'] ) ) {
            throw new DropboxClientException( "Could not retrieve Session ID." );
        }

        //Return the Session ID
        return $body['session_id'];
    }

    /**
     * Make a HTTP POST Request to the Content endpoint type
     *
     * @param  string $endpoint Content Endpoint to send Request to
     * @param  array $params Request Query Params
     * @param  string $accessToken Access Token to send with the Request
     * @param  DropboxFile $responseFile Save response to the file
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\DropboxResponse
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function postToContent( $endpoint, array $params = [], $accessToken = null, DropboxFile $responseFile = null ) {
        return $this->sendRequest( "POST", $endpoint, 'content', $params, $accessToken, $responseFile );
    }

    /**
     * Append more data to an Upload Session
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  string             $sessionId   Session ID returned by `startUploadSession`
     * @param  int                $offset      The amount of data that has been uploaded so far
     * @param  int                $chunkSize   The amount of data to upload
     * @param  boolean            $close       Closes the session for futher "appendUploadSession" calls
     *
     * @return string Unique identifier for the upload session
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-append_v2
     *
     */
    public function appendUploadSession( $dropboxFile, $sessionId, $offset, $chunkSize, $close = false ) {
        //Make Dropbox File
        $dropboxFile = $this->makeDropboxFile( $dropboxFile, $chunkSize, $offset );

        //Session ID, offset, chunkSize and path cannot be null
        if ( is_null( $sessionId ) || is_null( $offset ) || is_null( $chunkSize ) ) {
            throw new DropboxClientException( "Session ID, offset and chunk size cannot be null" );
        }

        $params = [];

        //Set the File
        $params['file'] = $dropboxFile;

        //Set the Cursor: Session ID and Offset
        $params['cursor'] = ['session_id' => $sessionId, 'offset' => $offset];

        //Set the close param
        $params['close'] = $close ? true : false;

        //Since this endpoint doesn't have
        //any return values, we'll disable the
        //response validation for this request.
        $params['validateResponse'] = false;

        //Upload File
        $this->postToContent( '/files/upload_session/append_v2', $params );

        //Make and Return the Model
        return $sessionId;
    }

    /**
     * Finish an upload session and save the uploaded data to the given file path
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  string             $sessionId   Session ID returned by `startUploadSession`
     * @param  int                $offset      The amount of data that has been uploaded so far
     * @param  int                $remaining   The amount of data that is remaining
     * @param  string             $path        Path to save the file to, on Dropbox
     * @param  array              $params      Additional Params
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-finish
     *
     */
    public function finishUploadSession( $dropboxFile, $sessionId, $offset, $remaining, $path, array $params = [] ) {
        //Make Dropbox File
        $dropboxFile = $this->makeDropboxFile( $dropboxFile, $remaining, $offset );

        //Session ID, offset, remaining and path cannot be null
        if ( is_null( $sessionId ) || is_null( $path ) || is_null( $offset ) || is_null( $remaining ) ) {
            throw new DropboxClientException( "Session ID, offset, remaining and path cannot be null" );
        }

        $queryParams = [];

        //Set the File
        $queryParams['file'] = $dropboxFile;

        //Set the Cursor: Session ID and Offset
        $queryParams['cursor'] = ['session_id' => $sessionId, 'offset' => $offset];

        //Set the path
        $params['path'] = $path;
        //Set the Commit
        $queryParams['commit'] = $params;

        //Upload File
        $file = $this->postToContent( '/files/upload_session/finish', $queryParams );
        $body = $file->getDecodedBody();

        //Make and Return the Model
        return new FileMetadata( $body );
    }

    /**
     * Upload a File to Dropbox in a single request
     *
     * @param  string|DropboxFile $dropboxFile DropboxFile object or Path to file
     * @param  string $path Path to upload the file to
     * @param  array $params Additional Params
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function simpleUpload( $dropboxFile, $path, array $params = [] ) {
        //Make Dropbox File
        $dropboxFile = $this->makeDropboxFile( $dropboxFile );

        //Set the path and file
        $params['path'] = $path;
        $params['file'] = $dropboxFile;

        //Upload File
        $file = $this->postToContent( '/files/upload', $params );
        $body = $file->getDecodedBody();

        //Make and Return the Model
        return new FileMetadata( $body );
    }

    /**
     * Get a thumbnail for an image
     *
     * @param  string $path   Path to the file you want a thumbnail to
     * @param  string $size   Size for the thumbnail image ['thumb','small','medium','large','huge']
     * @param  string $format Format for the thumbnail image ['jpeg'|'png']
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\Thumbnail
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_thumbnail
     *
     */
    public function getThumbnail( $path, $size = 'w256h256', $format = 'jpeg', $mode = 'strict' ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Invalid Format
        if ( ! in_array( $format, ['jpeg', 'png'] ) ) {
            throw new DropboxClientException( "Invalid format. Must either be 'jpeg' or 'png'." );
        }

        //Thumbnail size
        // $size = $this->getThumbnailSize( $size );

        //Get Thumbnail
        $response = $this->postToContent( '/files/get_thumbnail', ['path' => $path, 'format' => $format, 'size' => $size] );

        //Get file metadata from response headers
        $metadata = $this->getMetadataFromResponseHeaders( $response );

        //File Contents
        $contents = $response->getBody();

        //Make and return a Thumbnail model
        return new Thumbnail( $metadata, $contents );
    }

    /**
     * Get thumbnail size
     *
     * @param  string $size Thumbnail Size
     *
     * @return string
     */
    protected function getThumbnailSize( $size ) {
        $thumbnailSizes = [
            'thumb'  => 'w32h32',
            'small'  => 'w64h64',
            'medium' => 'w128h128',
            'large'  => 'w640h480',
            'huge'   => 'w1024h768',
        ];

        return isset( $thumbnailSizes[$size] ) ? $thumbnailSizes[$size] : $thumbnailSizes['small'];
    }

    /**
     * Get metadata from response headers
     *
     * @param  DropboxResponse $response
     *
     * @return array
     */
    protected function getMetadataFromResponseHeaders( DropboxResponse $response ) {
        //Response Headers
        $headers = $response->getHeaders();

        //Empty metadata for when
        //metadata isn't returned
        $metadata = [];

        //If metadata is available
        if ( isset( $headers[static::METADATA_HEADER] ) ) {
            //File Metadata
            $data = $headers[static::METADATA_HEADER];

            //The metadata is present in the first index
            //of the metadata response header array
            if ( is_array( $data ) && isset( $data[0] ) ) {
                $data = $data[0];
            }

            //Since the metadata is returned as a json string
            //it needs to be decoded into an associative array
            $metadata = json_decode( (string) $data, true );
        }

        //Return the metadata
        return $metadata;
    }

    /**
     * Download a File
     *
     * @param  string                  $path        Path to the file you want to download
     * @param  null|string|DropboxFile $dropboxFile DropboxFile object or Path to target file
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\File
     *
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-download
     *
     */
    public function download( $path, $dropboxFile = null ) {
        //Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( "Path cannot be null." );
        }

        //Make Dropbox File if target is specified
        $dropboxFile = $dropboxFile ? $this->makeDropboxFile( $dropboxFile, null, null, DropboxFile::MODE_WRITE ) : null;

        //Download File
        $response = $this->postToContent( '/files/download', ['path' => $path], null, $dropboxFile );

        //Get file metadata from response headers
        $metadata = $this->getMetadataFromResponseHeaders( $response );

        //File Contents
        $contents = $dropboxFile ? $this->makeDropboxFile( $dropboxFile ) : $response->getBody();

        //Make and return a File model
        return new File( $metadata, $contents );
    }

    /**
     * Get Current Account
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_current_account
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\Account
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getCurrentAccount() {
        //Get current account
        $response = $this->postToAPI( '/users/get_current_account', [] );
        $body = $response->getDecodedBody();

        //Make and return the model
        return new Account( $body );
    }

    /**
     * Get Account
     *
     * @param string $account_id Account ID of the account to get details for
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_account
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\Account
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getAccount( $account_id ) {
        //Get account
        $response = $this->postToAPI( '/users/get_account', ['account_id' => $account_id] );
        $body = $response->getDecodedBody();

        //Make and return the model
        return new Account( $body );
    }

    /**
     * Get Multiple Accounts in one call
     *
     * @param array $account_ids IDs of the accounts to get details for
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_account_batch
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\AccountList
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getAccounts( array $account_ids = [] ) {
        //Get account
        $response = $this->postToAPI( '/users/get_account_batch', ['account_ids' => $account_ids] );
        $body = $response->getDecodedBody();

        //Make and return the model
        return new AccountList( $body );
    }

    /**
     * Get Space Usage for the current user's account
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_space_usage
     *
     * @return array
     * @throws \CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException
     */
    public function getSpaceUsage() {
        //Get space usage
        $response = $this->postToAPI( '/users/get_space_usage', [] );
        $body = $response->getDecodedBody();

        //Return the decoded body
        return $body;
    }

    /**
     * Create a shared link with custom settings.
     *
     * @param string             $path     The path to be shared by the shared link
     * @param SharedLinkSettings $settings the requested settings for the newly created shared link This field is optional
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-create_folder
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\FileLinkMetadata|\CodeConfig\IntegrateDropbox\SDK\Models\FolderLinkMetadata
     */
    public function createSharedLinkWithSettings( $path, $settings = [] ) {
        // Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( 'Path cannot be null.' );
        }
        // Create Folder
        $response = $this->postToAPI( '/sharing/create_shared_link_with_settings', ['path' => $path, 'settings' => $settings] );

        // Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * List shared links of this user.
     *
     * @param string $path   Path to the folder. Defaults to root.
     * @param string $cursor the cursor returned by your last call to list_shared_links
     * @param array  $params Additional Params
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_shared_links
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\MetadataCollection
     */
    public function listSharedLinks( $path = null, $cursor = null, array $params = ['direct_only' => true] ) {
        // Specify the root folder as an
        // empty string rather than as "/"
        if ( '/' === $path ) {
            $path = '';
        }

        // Set the path
        if ( ! empty( $path ) ) {
            $params['path'] = $path;
        } elseif ( ! empty( $cursor ) ) {
            $params['cursor'] = $cursor;
        }

        // Get File Metadata
        $response = $this->postToAPI( '/sharing/list_shared_links', $params );

        // Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Preview a File.
     *
     * @param string $path Path to the file you want to download
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-get_preview
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\File
     */
    public function preview( $path ) {
        // Path cannot be null
        if ( is_null( $path ) ) {
            throw new DropboxClientException( 'Path cannot be null.' );
        }

        // Download File
        $response = $this->postToContent( '/files/get_preview', ['path' => $path] );

        // Get file metadata from response headers
        $metadata = $this->getMetadataFromResponseHeaders( $response );

        // File Contents
        $contents = $response->getBody();

        // Make and return a File model
        return new File( $metadata, $contents );
    }

    /**
     * Copy a file or folder to a different location.
     *
     * @param array $entries Entries to be copied
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-copy_batch
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\ModelCollection
     */
    public function copyBatch( $params ) {
        if ( is_null( $params ) ) {
            throw new DropboxClientException( 'Entries cannot be null.' );
        }

        // Response
        $response = $this->postToAPI( '/files/copy_batch_v2', $params );

        // Make and Return the Model
        return $this->waitForAsyncRequest( $response, '/files/copy_batch/check_v2' );
    }

    public function waitForAsyncRequest( $raw_response, $request_url, $async_job_id = null ) {
        $response = $this->makeModelFromResponse( $raw_response );

        if ( ! ( $response instanceof AsyncJob ) && ! ( $response instanceof Tag ) ) {
            return $response;
        }

        if ( $response instanceof Tag && 'in_progress' !== $response->getTag() ) {
            return $response;
        }

        if ( $response instanceof AsyncJob && empty( $async_job_id ) ) {
            $async_job_id = $response->getAsyncJobId();
        }

        usleep( 1000000 );
        $raw_response = $this->postToAPI( $request_url, ['async_job_id' => $async_job_id] );

        return $this->waitForAsyncRequest( $raw_response, $request_url, $async_job_id );
    }

    /**
     * Move multiple files or folders to different locations at once.
     *
     * @param array $entries Entries to be moved
     * @param mixed $async
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-move_batch
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\ModelCollection
     */
    public function moveBatch( $params, $async = true ) {
        if ( is_null( $params ) ) {
            throw new DropboxClientException( 'From and To paths cannot be null.' );
        }

        // Response
        $response = $this->postToAPI( '/files/move_batch_v2', $params );

        if ( false === $async ) {
            return $this->makeModelFromResponse( $response );
        }

        return $this->waitForAsyncRequest( $response, '/files/move_batch/check_v2' );
    }

    /**
     * Delete a file or folder at the given path.
     *
     * @param array $entries Entries to be deleted
     * @param mixed $async
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-delete_batch
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\ModelCollection
     */
    public function deleteBatch( $params, $async = true ) {
        // Path cannot be null
        if ( is_null( $params ) ) {
            throw new DropboxClientException( 'Entries cannot be null.' );
        }

        // Delete
        $response = $this->postToAPI( '/files/delete_batch', $params );

        // Make and Return the Model
        if ( false === $async ) {
            return $this->makeModelFromResponse( $response );
        }

        return $this->waitForAsyncRequest( $response, '/files/delete_batch/check' );
    }

    /**
     * Fetches the next page of search results returned from /search_v2.
     *
     * @param string $cursor The cursor returned by your last call to search
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation?oref=e#files-search-continue:2
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\SearchResults
     */
    public function search_continue( $cursor ) {
        // Fetch Search Results
        $response = $this->postToAPI( '/files/search/continue_v2', ['cursor' => $cursor] );

        // Make and Return the Model
        return $this->makeModelFromResponse( $response );
    }

    /**
     * Get a one-time use temporary upload link to upload a file to a Dropbox location.
     *
     * @param string $path        Path to upload the file to
     * @param array  $commit_info Additional Params
     * @param array  $duration    how long before this link expires, in seconds
     * @param mixed  $origin
     *
     * @see https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     *
     * @return \CodeConfig\IntegrateDropbox\SDK\Models\TemporaryLink
     */
    public function getTemporarilyUploadLink( $path, array $commit_info = [], $origin = '', $duration = 14400 ) {
        $params = [];
        $params['commit_info'] = $commit_info;
        $params['commit_info']['path'] = $path;
        $params['duration'] = $duration;

        // Get temporarily Link
        // Make a DropboxRequest object
        $request = new DropboxRequest( 'POST', '/files/get_temporary_upload_link', $this->getAccessToken()->getToken(), ' api', $params );

        // Set Origin Header
        $request->setHeaders( ['Origin' => $origin] );

        // Send Request through the DropboxClient
        // Fetch and return the Response
        $result = $this->getClient()->sendRequest( $request );

        $body = $result->getDecodedBody();

        // Make and Return the Model
        return new TemporaryLink( $body );
    }

}
