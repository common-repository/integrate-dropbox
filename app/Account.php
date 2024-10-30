<?php

namespace CodeConfig\IntegrateDropbox\App;

defined('ABSPATH') or exit('Hey, what are you doing here? You silly human!');

use CodeConfig\IntegrateDropbox\App\Authorization;

class Account
{

    private $_id;
    private $_name;

    private $_email;
    private $_image;
    private $_type;
    private $_root_namespace_id = '';
    private $_is_verified = false;

    private $_authorization;

    public function __construct($id, $name, $email, $type = null, $image = null, $root_namespace_id = null)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_email = $email;
        $this->_image = $image;
        $this->_root_namespace_id = $root_namespace_id;
        $this->_type = $type;
        $this->_authorization = new Authorization($this);
    }

    public function __sleep()
    {
        // Don't store authorization class in DB */
        $keys = get_object_vars($this);
        unset($keys['_authorization']);

        return array_keys($keys);
    }

    public function __wakeup()
    {
        $this->_authorization = new Authorization($this);
    }

    public function get_id()
    {
        return $this->_id;
    }

    public function get_name()
    {
        return $this->_name;
    }

    public function get_email()
    {
        return $this->_email;
    }

    public function get_image()
    {
        if (empty($this->_image)) {
            return INDBOX_URL . '/assets/admin/images/dropbox_logo_small.png';
        }

        return $this->_image;
    }

    public function set_id($_id)
    {
        $this->_id = $_id;
    }

    public function set_name($_name)
    {
        $this->_name = $_name;
    }

    public function set_email($_email)
    {
        $this->_email = $_email;
    }

    public function set_image($_image)
    {
        $this->_image = $_image;
    }

    public function get_type()
    {
        return $this->_type;
    }

    public function set_type($_type)
    {
        $this->_type = $_type;
    }

    public function get_root_namespace_id()
    {
        return $this->_root_namespace_id;
    }

    public function set_root_namespace_id($root_namespace_id)
    {
        $this->_root_namespace_id = $root_namespace_id;
    }

    public function is_verified()
    {

        return $this->_is_verified;
    }

    public function set_is_verified($is_verified = true)
    {
        $this->_is_verified = $is_verified;
    }

    public function get_storage_info()
    {

        return;
    }

    /**
     * @return \CodeConfig\IntegrateDropbox\App\Authorization
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
}
