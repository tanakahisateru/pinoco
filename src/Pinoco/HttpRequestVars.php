<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * Global variables wrapper for HTTP request.
 *
 * @package Pinoco
 * @property-read Pinoco_Vars $server wraps $_SERVER
 * @property-read Pinoco_Vars $get wraps $_GET
 * @property-read Pinoco_Vars $post wraps $_POST
 * @property-read Pinoco_Vars $cookie wraps $_COOKIE
 * @property-read Pinoco_Vars $files wraps $_FILES
 * @property-read Pinoco_Vars $session wraps $_SESSION
 * @property-read Pinoco_Vars $env wraps $_ENV
 * @property-read string $method alias to $_SERVER['REQUEST_METHOD']
 */
class Pinoco_HttpRequestVars extends Pinoco_DynamicVars
{
    private $_pinoco;

    private $_server;
    private $_get;
    private $_post;
    private $_cookie;
    private $_files;
    private $_session;
    private $_env;

    /**
     * Constructor
     *
     * @param Pinoco $pinoco
     */
    public function __construct($pinoco)
    {
        parent::__construct();
        $this->_pinoco = $pinoco;
    }

    public function get_server()
    {
        if (!isset($this->_server)) {
            if ($this->_pinoco->testing) {
                $this->_server = empty($_SERVER) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_SERVER);
            }
            else {
                $this->_server = Pinoco_Vars::wrap($_SERVER);
            }
        }
        return $this->_server;
    }

    public function get_get()
    {
        if (!isset($this->_get)) {
            if ($this->_pinoco->testing) {
                $this->_get = empty($_GET) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_GET);
            }
            else {
                $this->_get = Pinoco_Vars::wrap($_GET);
            }
        }
        return $this->_get;
    }

    public function get_post()
    {
        if (!isset($this->_post)) {
            if ($this->_pinoco->testing) {
                $this->_post = empty($_POST) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_POST);
            }
            else {
                $this->_post = Pinoco_Vars::wrap($_POST);
            }
        }
        return $this->_post;
    }

    public function get_cookie()
    {
        if (!isset($this->_cookie)) {
            if ($this->_pinoco->testing) {
                $this->_cookie = empty($_COOKIE) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_COOKIE);
            }
            else {
                $this->_cookie = Pinoco_Vars::wrap($_COOKIE);
            }
        }
        return $this->_cookie;
    }

    public function get_files()
    {
        if (!isset($this->_files)) {
            if ($this->_pinoco->testing) {
                $this->_files = empty($_FILES) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_FILES);
            }
            else {
                $this->_files = Pinoco_Vars::wrap($_FILES);
            }
        }
        return $this->_files;
    }

    public function get_session()
    {
        if (!isset($this->_session)) {
            if ($this->_pinoco->testing) {
                // fake cookie header
                $this->_pinoco->setcookie(session_name(), '0');
                $this->_session = empty($_SESSION) ? (new Pinoco_Vars()) : Pinoco_Vars::wrap($_SESSION);
            }
            else {
                @session_start();
                // fake cookie header
                $this->_pinoco->sent_headers->push(
                    'Set-Cookie: ' . urlencode(session_name()) . '=' . urlencode(session_id())
                );
                $this->_session = Pinoco_Vars::wrap($_SESSION);
            }
        }
        return $this->_session;
    }

    public function get_env()
    {
        if (!isset($this->_env)) {
            if ($this->_pinoco->testing) {
                $this->_env = empty($_ENV) ? (new Pinoco_Vars()) : Pinoco_Vars::fromArray($_ENV);
            }
            else {
                $this->_env = Pinoco_Vars::wrap($_ENV);
            }
        }
        return $this->_env;
    }

    public function get_method()
    {
        return $this->server->get('REQUEST_METHOD', null);
    }

    public function isHead()
    {
        return $this->method == 'HEAD';
    }

    public function isGet()
    {
        return $this->method == 'GET';
    }

    public function isPost()
    {
        return $this->method == 'POST';
    }

    public function isPut()
    {
        return $this->method == 'PUT';
    }

    public function isDelete()
    {
        return $this->method == 'DELETE';
    }
}

