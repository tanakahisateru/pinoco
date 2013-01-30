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
 * Flow control object
 *
 * @package Pinoco
 * @internal
 */
class Pinoco_FlowControlHttpRedirect extends Pinoco_FlowControlHttpError
{
    /**
     * Constructor
     *
     * @param string $url
     * @param bool $external
     */
    public function __construct($url, $external=false)
    {
        $this->url = $url;
        $this->external = $external;
    }

    /**
     * Redirect response implementation.
     *
     * @param Pinoco $pinoco
     * @return void
     */
    public function respond($pinoco)
    {
        $s = $pinoco->request->server;
        $protocol = $s->get('HTTPS') ? "https" : "http";
        $server_prefix = "";
        if ($s->has('HTTP_HOST')) {
            $server_prefix = $protocol . '://' . $s->get('HTTP_HOST');
        }
        else {
            $server_prefix = $protocol . '://' . $s->get('SERVER_NAME');
            $port = $s->get('SERVER_PORT');
            if ($protocol == "http" && $port != "80") {
                $server_prefix = $server_prefix . ":" . $port;
            } elseif ($protocol == "https" && $port != "443") {
                $server_prefix = $server_prefix . ":" . $port;
            }
        }
        $fixedurl = "";
        if (preg_match('/^\w+:\/\/[^\/]/', $this->url)) {
            $fixedurl = $this->url;
        }
        elseif (preg_match('/^\/\/[^\/]/', $this->url)) {
            $fixedurl = $protocol . ':' . $this->url;
        }
        elseif (preg_match('/^\/[^\/]?/', $this->url) && $this->external) {
            $fixedurl = $server_prefix. $this->url;
        }
        else {
            $filteredurl = $pinoco->url($this->url);
            if (preg_match('/^\w+:\/\/[^\/]/', $filteredurl)) {
                $fixedurl = $filteredurl;
            }
            elseif (preg_match('/^\/\/[^\/]/', $filteredurl)) {
                $fixedurl = $protocol . ':' . $filteredurl;
            }
            else {
                $fixedurl = $server_prefix. $filteredurl;
            }
        }
        $pinoco->header('Location: ' . $fixedurl);
    }
}
