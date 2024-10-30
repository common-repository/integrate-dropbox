<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\Helpers;

class Processor
{

    protected $listToken = '';
    protected $_requestedFile;
    protected $_requestedDir;
    protected $_requestedPath;
    protected $_requestedCompletePath;
    protected $_lastPath = '/';
    protected $_rootFolder = '';
    protected $_load_scripts = ['general' => false, 'files' => false, 'upload' => false, 'mediaplayer' => false, 'carousel' => false];

    /**
     * @var Processor
     */
    private static $_instance;

    /**
     * @var array
     */
    private $_settings;

    /**
     * @var string
     */
    private $_settings_key = 'integrate_dropbox_settings';

    public function __construct()
    {
        $this->_settings = get_option($this->_settings_key, []);
    }

    /**
     * @return Processor
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_setting($key, $default = null)
    {
        if (!isset($this->_settings[$key])) {
            return $default;
        }

        return $this->_settings[$key];
    }

    public function set_setting($key, $value)
    {

        if (!is_array($this->_settings)) {
            $this->_settings = [];
        }

        if (empty($key)) {
            return;
        }

        $this->_settings[$key] = $value;

        $success = update_option($this->_settings_key, $this->_settings);

        $this->_settings = get_option($this->_settings_key);

        return $success;
    }

    public function get_listToken()
    {
        return $this->listToken;
    }

    public function get_requested_complete_path()
    {
        return $this->_requestedCompletePath;
    }

    private function _set_requested_path($path = '')
    {
        if ('' === $path) {
            if ('' !== $this->_lastPath) {
                $path = $this->_lastPath;
            } else {
                $path = '/';
            }
        }

        $regex = '/(id:.*)|(ns:[0-9]+(\/.*)?)/i';
        if (1 === preg_match($regex, $path)) {
            $this->_requestedPath = $path;
            $this->_requestedCompletePath = $path;

            return;
        }

        $path = Helpers::clean_folder_path($path);
        $path_parts = Helpers::get_pathinfo($path);

        $this->_requestedDir = '';
        $this->_requestedFile = '';

        if (isset($path_parts['extension'])) {
            // it's a file
            $this->_requestedFile = $path_parts['basename'];
            $this->_requestedDir = str_replace('\\', '/', $path_parts['dirname']);
            $requestedDir = ('/' === $this->_requestedDir) ? '/' : $this->_requestedDir . '/';
            $this->_requestedPath = $requestedDir . $this->_requestedFile;
        } else {
            // it's a dir
            $this->_requestedDir = str_replace('\\', '/', $path);
            $this->_requestedFile = '';
            $this->_requestedPath = $this->_requestedDir;
        }

        $requestedCompletePath = $this->_rootFolder;
        if ($this->_rootFolder !== $this->_requestedPath) {
            $requestedCompletePath = html_entity_decode($this->_rootFolder . $this->_requestedPath);
        }

        $this->_requestedCompletePath = str_replace('//', '/', $requestedCompletePath);
    }

    public function get_root_folder()
    {
        return $this->_rootFolder;
    }

    public function get_relative_path($full_path, $from_path = null)
    {
        if (empty($from_path)) {
            if ('' === $this->get_root_folder() || '/' === $this->get_root_folder()) {
                return $full_path;
            }

            $from_path = $this->get_root_folder();
        }

        $from_path_arr = explode('/', $from_path);
        $full_path_arr = explode('/', $full_path);
        $difference = (count($full_path_arr) - count($from_path_arr));

        if ($difference < 1) {
            return '/';
        }

        if (1 === $difference) {
            return '/' . end($full_path_arr);
        }

        return '/' . implode('/', array_slice($full_path_arr, -$difference));
    }

    // Check if $entry is allowed

    public function _is_entry_authorized(Entry $entry)
    {

        // Action for custom filters
        $is_authorized_hook = apply_filters('integrate_dropbox_is_entry_authorized', true, $entry, $this);
        if (false === $is_authorized_hook) {
            return false;
        }

        if (strtolower($entry->get_path()) === strtolower($this->_rootFolder)) {
            return true;
        }

        // TODO...

        return true;
    }
}