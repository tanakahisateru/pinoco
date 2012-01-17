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
 * @package Pinoco
 * @internal
 */
class Pinoco_FlowControlHttpRedirect extends Pinoco_FlowControlHttpError {
    /**
     * Constructor
     * @param string $url
     * @param bool $external
     */
    public function __construct($url, $external=FALSE)
    {
        $this->url = $url;
        $this->external = $external;
    }
    
    /**
     * Redirect response implementation.
     * @param Pinoco $pinoco
     * @return void
     */
    public function respond($pinoco)
    {
        $s = $pinoco->request->server;
        $protocol = $s->get('HTTPS') ? "https" : "http";
        $server_prefix = $protocol . '://' . $s->get('SERVER_NAME');
        if ($protocol == "http" && $s->get('SERVER_PORT') != "80") {
            $server_prefix = $server_prefix . ":" . $s->get('SERVER_PORT');
        } else if ($protocol == "https" && $s->get('SERVER_PORT') != "443") {
            $server_prefix = $server_prefix . ":" . $s->get('SERVER_PORT');
        }
        $fixedurl = "";
        if(preg_match('/^\w+:\/\/[^\/]/', $this->url)) {
            $fixedurl = $this->url;
        }
        else if(preg_match('/^\/\/[^\/]/', $this->url)) {
            $fixedurl = $protocol . ':' . $this->url;
        }
        else if(preg_match('/^\/[^\/]?/', $this->url)) {
            if($this->external) {
                $fixedurl = $server_prefix. $this->url;
            }
            else {
                $fixedurl = $server_prefix. $pinoco->url($this->url);
            }
        }
        else {
            $fixedurl = $server_prefix. $pinoco->url($this->url);
        }
        $pinoco->header('Location: ' . $fixedurl);
    }
}
