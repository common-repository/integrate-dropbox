<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\Database;
use CodeConfig\IntegrateDropbox\App\Entry;
use CodeConfig\IntegrateDropbox\Helpers;
use CodeConfig\IntegrateDropbox\SDK\Models\FileMetadata;
use CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata;

class Client
{
    /**
     * The single instance of the class.
     *
     * @var Client
     */
    protected static $_instance;

    private $_entry;

    /**
     * Client Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Client - Client instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_account_info()
    {
        return App::instance()->get_sdk_client()->getCurrentAccount();
    }

    public function get_shared_links()
    {
        return App::instance()->get_sdk_client()->listSharedLinks('/');
    }

    public function get_account_space_info()
    {
        return App::instance()->get_sdk_client()->getSpaceUsage();
    }

    public function get_entry($requested_path = null, $check_if_allowed = true)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        // Get entry meta data (no meta data for root folder_
        if ('/' === $requested_path || '' === $requested_path) {
            $this->_entry = new Entry();
            $this->_entry->set_id('Dropbox');
            $this->_entry->set_name('Dropbox');
            $this->_entry->set_basename('Dropbox');
            $this->_entry->set_path('/');
            $this->_entry->set_path_display('/');
            $this->_entry->set_is_dir(true);
        } else {
            try {
                $this->_entry = API::get_entry($requested_path, ['include_media_info' => true]);
            } catch (\Exception $ex) {
                return false;
            }
        }

        if ($check_if_allowed && !Processor::instance()->_is_entry_authorized($this->_entry)) {
            exit('-1');
        }

        return $this->_entry;
    }

    public function get_multiple_entries($entries = [])
    {
        $dropbox_entries = [];
        foreach ($entries as $entry) {
            $dropbox_entry = $this->get_entry($entry, false);
            if (!empty($dropbox_entry)) {
                $dropbox_entries[] = $dropbox_entry;
            }
        }

        return $dropbox_entries;
    }

    /**
     * @param string $requested_path
     * @param bool   $check_if_allowed
     * @param mixed  $recursive
     * @param mixed  $hierarchical
     *
     * @return bool|Entry
     */
    public function get_folder($requested_path = null, $check_if_allowed = true, $recursive = false, $hierarchical = true)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        try {
            $folder = API::get_folder($requested_path, ['recursive' => $recursive, 'hierarchical' => $hierarchical]);
        } catch (\Exception $ex) {
            return false;
        }

        foreach ($folder->get_children() as $key => $child) {
            if ($check_if_allowed && false === Processor::instance()->_is_entry_authorized($child)) {
                unset($folder->children[$key]);

                continue;
            }
        }

        return $folder;
    }

    public function get_images($requested_path = null, $check_if_allowed = true, $recursive = false, $hierarchical = true)
    {
        $response = $this->get_folder($requested_path, $check_if_allowed, $recursive, $hierarchical);

        return $this->filter_entry($response);
    }

    private function filter_entry($entry, $result = [])
    {
        if ($entry->is_dir) {
            foreach ($entry->children as $ch_entry) {

                if ($ch_entry->is_dir) {
                    $new_entry = $this->get_folder($ch_entry->path);
                    // $result[] = $this->filter_entry( $new_entry );
                    $result[] = $this->filter_entry($new_entry);
                } else {
                    $result[] = $ch_entry->path;
                }
            }
        } else {
            $result[] = $entry;
        }

        return $result;
    }

    public function search($search_query)
    {
        // TODO...
    }

    public function get_api_entries($requested_path = null)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        // Get folder children
        try {
            $api_folders_contents = App::instance()->get_sdk_client()->listFolder($requested_path, ['recursive' => true, "include_deleted" => false]);
            $api_entries = $api_folders_contents->getItems()->toArray();

            while ($api_folders_contents->hasMoreItems()) {
                $cursor = $api_folders_contents->getCursor();
                $api_folders_contents = App::instance()->get_sdk_client()->listFolderContinue($cursor);
                $api_entries = array_merge($api_entries, $api_folders_contents->getItems()->toArray());
            }

            unset($api_folders_contents);
        } catch (\Exception $ex) {
            error_log('[Integrate Dropbox message]: ' . sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return null;
        }

        return $api_entries;
    }

    public function get_folder_size($requested_path = null)
    {

        $api_entries = $this->get_api_entries($requested_path);

        $total_size = 0;

        foreach ($api_entries as $api_entry) {
            $total_size += ($api_entry instanceof \CodeConfig\IntegrateDropbox\SDK\Models\FolderMetadata) ? 0 : $api_entry->size;
        }

        unset($api_entries);

        return $total_size;
    }

    public function get_all_photos($args)
    {
        $default = [
            'parent_id' => 'all_photos',
            'force' => false,
            'extensions' => ['jpg', 'png'],
        ];

        $args = wp_parse_args($args, $default);

        $force = isset($args['force']) ? $args['force'] : false;

        if (empty($force)) {
            $entries = Database::instance()->get_files($args);
            if (!empty($entries)) {

                return $entries;
            }
        }

        $api_entries = $this->get_api_entries('/');

        foreach ($api_entries as $api_entry) {

            if (($api_entry instanceof FileMetadata) || ($api_entry instanceof FolderMetadata)) {

                $entry = new Entry($api_entry);
                $parent_slug = $entry->get_parent();
                $find_parent = array_filter($api_entries, function ($item) use ($parent_slug) {
                    return $item->getPathLower() === $parent_slug;
                });

                if (!empty($find_parent)) {
                    $find_parent = reset($find_parent);
                }

                if ($find_parent instanceof FolderMetadata) {
                    $parent_id = $find_parent->getId();
                    $entry->set_parent($parent_id);
                }

                Database::instance()->set_file($entry, $force);
            }
        }

        return Database::instance()->get_files($args);
    }

    public function preview_entry($id = null)
    {
        // Get file meta data
        if (is_null($this->_entry)) {
            $this->_entry = $this->get_entry($id);
        }

        if (false === $this->_entry) {
            wp_send_json_error(['message' => "Couldn't locate the file or folder you're looking for."]);
        }

        if (false === $this->_entry->get_can_preview_by_cloud()) {
            wp_send_json_error(['message' => "Unfortunately, this file is not possible to preview, the file is: {$this->_entry->id}. and file Name: {$this->_entry->name}"]);
        }

        if (false === User::can_preview()) {
            wp_send_json_error(['message' => "Regrettably, you are unable to preview, the file is: {$this->_entry->id}."]);
        }

        do_action('integrate_dropbox_log_event', 'integrate_dropbox_previewed_entry', $this->_entry);

        // Preview for Media files in HTML5 Player
        if (in_array($this->_entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {
            if ($this->has_shared_link($this->_entry)) {
                $temporarily_link = str_replace('/s/', '/s/raw/', $this->get_shared_link($this->_entry));

                // Support for new /scl/fi links
                if (false !== strpos($temporarily_link, 'scl/fi/')) {
                    $temporarily_link .= '&raw=1';
                }
            } else {
                $temporarily_link = $this->get_temporarily_link($this->_entry);
            }
            return $temporarily_link;
        }

        // Preview for Image files
        if (in_array($this->_entry->get_extension(), ['txt', 'jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            $shared_link = str_replace('/s/', '/s/raw/', $this->get_shared_link($this->_entry));

            // Support for new /scl/fi links
            if (false !== strpos($shared_link, 'scl/fi/')) {
                $shared_link .= '&raw=1';
            }

            return $shared_link;
        }

        // Preview for PDF files, read only via Google Viewer when needed
        if ('pdf' === $this->_entry->get_extension()) {
            $shared_link = $this->get_shared_link($this->_entry);
            if (false === User::can_download() && $this->_entry->get_size() < 25000000) {
                $shared_link = 'https://docs.google.com/viewerng/viewer?embedded=true&url=' . urlencode($shared_link . '&dl=1');
            } else {
                $shared_link = str_replace('/s/', '/s/raw/', $shared_link);

                // Support for new /scl/fi links
                if (false !== strpos($shared_link, 'scl/fi/')) {
                    $shared_link .= '&raw=1';
                }
            }
            return $shared_link;
        }

        // Preview for Office files via Office Viewer (Except read-only)
        if (
            User::can_download()
            && in_array($this->_entry->get_extension(), [
                'xls',
                'xlsx',
                'xlsm',
                'doc',
                'docx',
                'docm',
                'ppt',
                'pptx',
                'pptm',
                'pps',
                'ppsm',
                'ppsx',
            ])
        ) {
            $temporarily_link = $this->get_temporarily_link($this->_entry);
            $office_previewer = 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($temporarily_link);

            return $office_previewer;
        }

        // HTML previews are generated for files with the following extensions: .csv, .ods, .xls, .xlsm, .gsheet, .xlsx.
        if (in_array($this->_entry->get_extension(), ['xls', 'xlsx', 'xlsm', 'gsheet', 'csv', 'ods'])) {
            header('Content-Type: text/html');
        } else {
            // PDF previews are generated for files with the following extensions: .ai, .doc, .docm, .docx, .eps, .gdoc, .gslides, .odp, .odt, .pps, .ppsm, .ppsx, .ppt, .pptm, .pptx, .rtf.
            header('Content-Disposition: inline; filename="' . $this->_entry->get_basename() . '.pdf"');
            header('Content-Description: "' . $this->_entry->get_basename() . '"');
            header('Content-Type: application/pdf');
        }

        try {
            $preview_file = API::get_preview($this->_entry->get_id());
            return $preview_file->getContents();
        } catch (\Exception $ex) {
            error_log($ex);
            exit;
        }

        exit;
    }

    public function preview_entry_info($id)
    {

        $preview = Database::instance()->get_preview_entry($id);

        $force = isset($_POST['force']) ? rest_sanitize_boolean($_POST['force']) : false;

        if (empty($preview) || $force) {
            $this->_entry = $this->get_entry($id);

            if (empty($this->_entry) || is_null($this->_entry)) {
                wp_send_json_error(['message' => "File not found!"]);
            }

            $entry_info = [
                'extension' => $this->_entry->get_extension(),
                'url' => $this->preview_entry(),
            ];

            Database::instance()->set_preview_entry($id, $entry_info['url']);
            return $entry_info;

        } else {
            $entry_info = [
                'url' => $preview,
                'extension' => Database::instance()->get_entry_extension($id),
            ];
        }

        return $entry_info;
    }

    public function download_entry($id = null)
    {
        $this->_entry = $this->get_entry($id);

        if (false === $this->_entry) {
            exit(-1);
        }

        $stream = (isset($_REQUEST['action']) && 'integrate-dropbox-stream' === $_REQUEST['action'] && !isset($_REQUEST['caption']));

        if (!empty($this->_entry->save_as) && 'web' !== $this->_entry->get_extension()) {
            $this->export_entry($this->_entry);

            do_action('integrate_dropbox_download', $this->_entry, null);
            do_action('integrate_dropbox_log_event', 'integrate_dropbox_downloaded_entry', $this->_entry);

            exit;
        }

        if ('url' === $this->_entry->get_extension()) {
            $download_file = App::instance()->get_sdk_client()->download($this->_entry->get_id());
            preg_match_all('/URL=(.*)/', $download_file->getContents(), $location, PREG_SET_ORDER);

            if (2 === count($location[0])) {
                $temporarily_link = $location[0][1];
            }
        } elseif ('web' === $this->_entry->get_extension()) {
            $download_file = App::instance()->get_sdk_client()->download($this->_entry->get_id(), true);
            $data = json_decode($download_file->getContents());

            if (isset($data->url)) {
                $temporarily_link = $data->url;
            }
        } elseif ($stream && in_array($this->_entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {

            if (!$this->_entry->is_dir()) {
                $temporarily_link = str_replace('/s/', '/s/raw/', $this->get_shared_link($this->_entry));

                if (false !== strpos($temporarily_link, 'scl/fi/')) {
                    $temporarily_link .= '&raw=1';
                }
            } else {
                $temporarily_link = $this->get_temporarily_link($this->_entry);
            }
        } else {
            $temporarily_link = $this->get_temporarily_link($this->_entry);
        }

        return $temporarily_link;

        do_action('integrate_dropbox_download', $this->_entry, $temporarily_link);

        $event_type = $stream ? 'integrate_dropbox_streamed_entry' : 'integrate_dropbox_downloaded_entry';
        do_action('integrate_dropbox_log_event', $event_type, $this->_entry);

        if (!isset($_REQUEST['proxy'])) {
            header('Location: ' . $temporarily_link);
            set_transient('integrate_dropbox_' . (($stream) ? 'stream' : 'download') . '_' . $this->_entry->get_id() . '_' . $this->_entry->get_extension(), $temporarily_link, MINUTE_IN_SECONDS * 5);
        } else {
            $this->download_via_proxy($this->_entry, $temporarily_link);
        }

        exit;
    }

    public function export_entry(Entry $entry, $export_as = 'default')
    {
        if ('default' === $export_as) {
            $export_as = $entry->get_save_as();
        }

        $filename = ('default' === $export_as) ? $entry->get_name() : $entry->get_basename() . '.' . $export_as;

        // Get file
        $stream = fopen('php://temp', 'r+');

        // Stop WP from buffering
        wp_ob_end_flush_all();

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; ' . sprintf('filename="%s"; ', rawurlencode($filename)) . sprintf("filename*=utf-8''%s", rawurlencode($filename)));

        try {
            flush();

            $export_file = App::instance()->get_sdk_client()->download($entry->get_id(), $export_as);

            fwrite($stream, $export_file->getContents());
            rewind($stream);

            unset($export_file);

            while (!@feof($stream)) {
                echo esc_html(@fread($stream, 1024 * 1024));
                ob_flush();
                flush();
            }
        } catch (\Exception $ex) {
            error_log('[Integrate Dropbox message]: ' . sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));
        }

        fclose($stream);

        exit;
    }

    public function download_via_proxy(Entry $entry, $url, $inline = false)
    {
        // Stop WP from buffering
        wp_ob_end_flush_all();

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . basename($entry->get_name()) . '"');
        header("Content-length: {$entry->get_size()}");

        if ($inline) {
            header("Content-type: {$entry->get_mimetype()}");
        }

        $options = [
            'curl' => [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RANGE => null,
                CURLOPT_NOBODY => null,
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT => null,
                CURLOPT_TIMEOUT => null,
                CURLOPT_WRITEFUNCTION => function ($curl, $data) {
                    echo esc_html($data);

                    return strlen($data);
                },
            ]
        ];
        App::instance()->get_sdk_client()->getClient()->getHttpClient()->send($url, 'GET', '', [], $options);

        exit;
    }

    public function stream_entry()
    {
        // Get file meta data
        $entry = $this->get_entry();

        if (false === $entry) {
            exit(-1);
        }

        $extension = $entry->get_extension();
        $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm', 'vtt', 'srt'];

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            exit;
        }

        // Download Captions directly
        if (in_array($extension, ['vtt', 'srt'])) {
            $temporarily_link = $this->get_temporarily_link($entry);
            $this->download_via_proxy($entry, $temporarily_link);

            exit;
        }

        $this->download_entry($entry);
    }

    public function get_thumbnail(Entry $entry, $width = null, $height = null, $crop = false, $only_own_thumbnail = false)
    {

        if (false === $entry->has_own_thumbnail()) {
            if ($only_own_thumbnail) {
                return false;
            }

            $thumbnail_url = $entry->get_icon_large();
        } else {
            $thumbnail = new \CodeConfig\IntegrateDropbox\App\Thumbnail($entry, $width, $height, $crop);
            $thumbnail_url = $thumbnail->get_url();
            list($width, $height) = getimagesize($thumbnail->get_location_thumbnail());
        }

        $thumbnail_info = [
            'url' => $thumbnail_url,
            'height' => $height,
            'width' => $width,
        ];

        return $thumbnail_info;
    }

    /**
     * @param string $src
     * @return string|null
     */
    public function build_thumbnail($src)
    {
        // $src = sanitize_text_field( $_REQUEST['src'] );
        preg_match_all('/(.+)_w(\d+)h(\d+)_c(\d)_([a-z]+)/', $src, $attr, PREG_SET_ORDER);

        if (1 !== count($attr) || 6 !== count($attr[0])) {
            exit;
        }

        $entry_id = $attr[0][1];
        $width = $attr[0][2];
        $height = $attr[0][3];
        $crop = $attr[0][4];
        $format = $attr[0][5];

        $entry = $this->get_entry($entry_id, false);

        if (false === $entry) {
            exit(-1);
        }

        // get the last-modified-date of this very file
        $lastModified = strtotime($entry->get_last_edited());
        // get a unique hash of this file (etag)
        $etagFile = md5($lastModified);
        // get the HTTP_IF_MODIFIED_SINCE header if set
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false);
        // get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header("Etag: {$etagFile}");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 5) . ' GMT');
        header('Cache-Control: must-revalidate');

        if ((false !== $ifModifiedSince && false !== $etagHeader) && $ifModifiedSince == $lastModified || $etagHeader == $etagFile) {
            header('HTTP/1.1 304 Not Modified');

            exit;
        }

        if (false === $entry->has_own_thumbnail()) {
            header('Location: ' . $entry->get_icon_large());

            exit;
        }

        $thumbnail = new Thumbnail($entry, $width, $height, $crop, $format);

        if (false === $thumbnail->does_thumbnail_exist()) {
            $thumbnail_created = $thumbnail->build_thumbnail();

            if (false === $thumbnail_created) {
                header('Location: ' . $entry->get_icon_large());

                exit;
            }
        }

        $thumbnail_url = $thumbnail->get_url();
        return $thumbnail_url;
    }

    public function has_temporarily_link(Entry $entry)
    {
        $cached_entry = Database::instance()->get_file($entry->get_id());

        if (false === $cached_entry) {
            return false;
        }
        $temporarily_link = $cached_entry->get_temporarily_link();

        return !empty($temporarily_link);
    }

    public function get_temporarily_link(Entry $entry)
    {
        // $cached_entry = Cache::instance()->is_cached( $entry->get_id() );

        // ISSUE: Dropbox API can return errors for temporarily download links
        // When fixed, enable the following code:
        // if (false !== $cached_entry) {
        //     if ($temporarily_link = $cached_entry->get_temporarily_link()) {
        //         return $temporarily_link;
        //     }
        // }

        try {
            $temporarily_link = API::create_temporarily_download_url($entry->get_id());
            // $cached_entry = Database::instance()->get_file( $entry->get_id() );

            // $max_cache_request = ( (int) Processor::instance()->get_setting( 'request_cache_max_age' ) ) * 60;
            // $expires = time() + ( 4 * 60 * 60 ) - $max_cache_request;

            // $cached_entry->add_temporarily_link( $temporarily_link->getLink(), $expires );
        } catch (\Exception $ex) {
            return false;
        }

        // Cache::instance()->set_updated();

        // return $cached_entry->get_temporarily_link();
        return $temporarily_link->getLink();
    }

    public function has_shared_link(Entry $entry, $link_settings = ['audience' => 'public'])
    {
        $cached_entry = Database::instance()->get_preview_entry($entry->get_id());

        return !empty($cached_entry);
    }

    public function get_shared_link(Entry $entry, $link_settings = ['audience' => 'public'], $create = true)
    {
        $shared_link = Database::instance()->get_preview_entry($entry->get_id());

        if (!empty($shared_link)) {
            return $shared_link;
        }

        // Custom link settings for non Basic accounts
        // TODO...

        $default_settings = [
            'audience' => 'public',
            'allow_download' => true,
            'require_password' => false,
            'expires' => null,
        ];

        $link_settings = array_merge($default_settings, $link_settings);

        return ($create) ? $this->create_shared_link($entry, $link_settings) : false;
    }

    public function create_shared_link(Entry $entry, $link_settings)
    {
        $shared_links = API::create_shared_url($entry->get_id(), $link_settings);

        if (empty($shared_links)) {
            exit(esc_html__('The sharing permissions on this file is preventing you from accessing this shared link. Please contact the administrator to change the sharing settings for this document in the cloud.'));
        }

        if (1 === $shared_links) {
            do_action('integrate_dropbox_log_event', 'integrate_dropbox_created_link_to_entry', $entry, ['url' => reset($shared_links)]);
        }

        if (!empty($shared_links)) {
            $file_metadata = reset($shared_links);
            return $file_metadata->getUrl();
        }

        return false;
    }

    public function get_embedded_link(Entry $entry)
    {
        if (
            false === $entry->get_can_preview_by_cloud()
            || in_array($entry->get_extension(), ['pdf', 'jpg', 'jpeg', 'png', 'gif'])
            || in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])
        ) {
            $embed_link = str_replace('/s/', '/s/raw/', $this->get_shared_link($entry));

            // Support for new /scl/fi links
            if (false !== strpos($embed_link, 'scl/fi/')) {
                $embed_link .= '&raw=1';
            }

            return $embed_link;
        }

        // Preview for Office files via Office Viewer (Except read-only)
        if (
            User::can_download()
            && in_array($entry->get_extension(), [
                'xls',
                'xlsx',
                'xlsm',
                'doc',
                'docx',
                'docm',
                'ppt',
                'pptx',
                'pptm',
                'pps',
                'ppsm',
                'ppsx',
            ])
        ) {
            $shared_link = str_replace('/s/', '/s/raw/', $this->get_shared_link($entry));

            return 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($shared_link);
        }

        // TODO...
        return INDBOX_URL . "?action=integrate_dropbox-embed-entry&integrate_dropboxpath={$entry->get_id()}&account_id=" . App::get_current_account()->get_id();
    }

    public function get_shared_link_for_output($entry_path = null)
    {
        $entry = $this->get_entry($entry_path);

        if (false === $entry) {
            exit(-1);
        }

        $shared_link = $this->get_shared_link($entry, []) . '&dl=1';
        $embed_link = $this->get_embedded_link($entry);

        return [
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => API::shorten_url($shared_link, null, ['name' => $entry->get_name()]),
            'embeddedlink' => $embed_link,
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false,
        ];
    }

    public function add_folder($name_of_folder_to_create, $target_folder_path = null)
    {

        if (null === $target_folder_path) {
            $target_folder_path = Processor::instance()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_folder_path);

        try {
            $new_entry = API::create_folder($name_of_folder_to_create, $target_entry->get_path(), ['autorename' => false]);
        } catch (\CodeConfig\IntegrateDropbox\SDK\Exceptions\DropboxClientException $ex) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        return $new_entry;
    }

    public function rename_entry($new_name, $target_entry_path = null)
    {
        if (null === $target_entry_path) {
            $target_entry_path = Processor::instance()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_entry_path);

        if (
            $target_entry->is_file() && false === User::can_rename_files()
        ) {
            // TO DO LOG + FAIL ERROR
            exit(-1);
        }

        if (
            $target_entry->is_dir() && false === User::can_rename_folders()
        ) {
            // TO DO LOG + FAIL ERROR
            exit(-1);
        }

        try {
            $new_entry = API::rename($target_entry, $new_name);
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('[Integrate Dropbox] Failed to rename file.', 'integrate-dropbox'));
        }

        return $new_entry;
    }

    public function move_entries($entries, $target_entry_path, $copy = false)
    {
        $entries_to_move = [];
        $batch_request = [
            'entries' => [],
        ];
        $target = $this->get_entry($target_entry_path);

        if (false === $target) {
            error_log('[Integrate Dropbox message]: ' . sprintf('Failed to move as target folder %s is not found.', $target_entry_path));

            return $entries_to_move;
        }

        foreach ($entries as $entry_path) {
            $entry = $this->get_entry($entry_path);

            if (false === $entry) {
                continue;
            }

            if (!$copy && $entry->is_dir() && (false === User::can_move_folders())) {
                error_log('[Integrate Dropbox message]: ' . sprintf('Failed to move %s as user is not allowed to move folders.', $target->get_path()));
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_dir() && (false === User::can_copy_folders())) {
                error_log('[Integrate Dropbox message]: ' . sprintf('Failed to copy %s as user is not allowed to copy folders.', $entry->get_path()));
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if (!$copy && $entry->is_file() && (false === User::can_move_files())) {
                error_log('[Integrate Dropbox message]: ' . sprintf('Failed to move %s as user is not allowed to move files.', $target->get_path()));
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_file() && (false === User::can_copy_files())) {
                error_log('[Integrate Dropbox message]: ' . sprintf('Failed to copy %s as user is not allowed to copy files.', $entry->get_path()));
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            // Check user permission
            if (!$copy && !$entry->get_permission('canmove')) {
                error_log('[Integrate Dropbox message]: ' . sprintf('Failed to move %s as the sharing permissions on it prevent this.', $target->get_path()));
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            $new_entry_path = \CodeConfig\IntegrateDropbox\Helpers::clean_folder_path($target->get_path() . '/' . $entry->get_name());

            $batch_request['entries'][] = [
                'from_path' => $entry->get_path(),
                'to_path' => $new_entry_path,
            ];

            $entries_to_move[$entry->get_id()] = false; // update if batch request was succesfull
        }

        try {
            if ($copy) {
                $batch_request['autorename'] = true;
                $processed_entries = API::copy($batch_request);
            } else {
                $processed_entries = API::move($batch_request);
            }
        } catch (\Exception $ex) {
            error_log('[Integrate Dropbox message]: ' . sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $entries_to_move;
        }

        return $processed_entries;
    }

    public function delete_entries($entries_to_delete = [])
    {
        $deleted_entries = [];
        $batch_request = ['entries' => []];

        foreach ($entries_to_delete as $target_entry_id) {
            $target_entry = $this->get_entry($target_entry_id);

            if (false === $target_entry) {
                continue;
            }

            if ($target_entry->is_file() && false === User::can_delete_files()) {
                // TO DO LOG + FAIL ERROR
                error_log("OPS User can't delete this file: " . $target_entry->get_name());
                continue;
            }

            if ($target_entry->is_dir() && false === User::can_delete_folders()) {
                // TO DO LOG + FAIL ERROR
                error_log("OPS User can't delete this folder: " . $target_entry->get_name());
                continue;
            }

            $deleted_entries[$target_entry->get_id()] = $target_entry;

            $batch_request['entries'][] = [
                'path' => $target_entry->get_id(),
            ];
        }

        try {
            $deleted_entries = API::delete($batch_request);
            foreach ($batch_request['entries'] as $file) {
                Database::instance()->delete_file($file['path']);
            }

        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to delete file.', 'integrate-dropbox'));
        }

        return $deleted_entries;
    }
}
