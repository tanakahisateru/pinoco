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
class Pinoco_FlowControlHttpError extends Pinoco_FlowControl
{
    private $_status_messages = null;

    private $title;

    /**
     * Constructor
     *
     * @param int $code
     * @param string $title
     * @param string $message
     */
    public function __construct($code, $title = null, $message = null)
    {
        parent::__construct();
        $this->code = $code;
        $this->title = $title ? $title : $this->_code2message($code, 'title');
        $this->message = $message ? $message : $this->_code2message($code, 'message');
    }

    private function _code2message($code, $field)
    {
        $ise = "The server encountered an internal error or misconfiguration and was unable to complete your request.";
        if (!$this->_status_messages) {
            $this->_status_messages = array(
                100 => array('title' => 'Continue', 'message' => $ise),
                101 => array('title' => 'Switching Protocols', 'message' => $ise),
                200 => array('title' => 'OK', 'message' => $ise),
                201 => array('title' => 'Created', 'message' => $ise),
                202 => array('title' => 'Accepted', 'message' => $ise),
                203 => array('title' => 'Non-Authoritative Information', 'message' => $ise),
                204 => array('title' => 'No Content', 'message' => ""),
                205 => array('title' => 'Reset Content', 'message' => $ise),
                206 => array('title' => 'Partial Content', 'message' => $ise),
                300 => array('title' => 'Multiple Choices', 'message' => ""),
                301 => array('title' => 'Moved Permanently', 'message' => ""),
                302 => array('title' => 'Found', 'message' => ""),
                303 => array('title' => 'See Other', 'message' => ""),
                304 => array('title' => 'Not Modified', 'message' => ""),
                305 => array('title' => 'Use Proxy', 'message' => ""),
                307 => array('title' => 'Temporary Redirect', 'message' => ""),
                400 => array(
                    'title' => 'Bad Request',
                    'message' => "Your browser sent a request that this server could not understand."
                ),
                401 => array(
                    'title' => 'Unauthorized',
                    'message' => "This server could not verify that you are authorized to access the document" .
                        " requested.  Either you supplied the wrong credentials (e.g., bad password), or your browser" .
                        " doesn't understand how to supply the credentials required."
                ),
                402 => array(
                    'title' => 'Payment Required',
                    'message' => "The server encountered an internal error or misconfiguration and was unable to" .
                        " complete your request."
                ),
                403 => array(
                    'title' => 'Forbidden',
                    'message' => "You don't have privileges to access the URL on this server."
                ), // permission -> privileges
                404 => array(
                    'title' => 'Not Found',
                    'message' => "The requested URL was not available on this server."
                ), // found -> available
                405 => array(
                    'title' => 'Method Not Allowed',
                    'message' => "The requested method GET is not allowed for the URL."
                ),
                406 => array(
                    'title' => 'Not Acceptable',
                    'message' => "An appropriate representation of the requested resource could not be found on this" .
                        " server."
                ),
                407 => array(
                    'title' => 'Proxy Authentication Required',
                    'message' => "This server could not verify that you are authorized to access the document" .
                        " requested.  Either you supplied the wrong credentials (e.g., bad password), or your" .
                        " browser doesn't understand how to supply the credentials required."
                ),
                408 => array(
                    'title' => 'Request Time-out',
                    'message' => "Server timeout waiting for the HTTP request from the client."),
                409 => array('title' => 'Conflict', 'message' => $ise),
                410 => array(
                    'title' => 'Gone',
                    'message' => "The requested resource is no longer available on this server and there is no" .
                        " forwarding address. Please remove all references to this resource."
                ),
                411 => array(
                    'title' => 'Length Required',
                    'message' => "A request of the requested method GET requires a valid Content-length."
                ),
                412 => array(
                    'title' => 'Precondition Failed',
                    'message' => "The precondition on the request for the URL evaluated to false."
                ),
                413 => array(
                    'title' => 'Request Entity Too Large',
                    'message' => "The requested resource does not allow request data with GET requests, or the amount" .
                        " of data provided in the request exceeds the capacity limit."
                ),
                414 => array(
                    'title' => 'Request-URI Too Large',
                    'message' => "The requested URL's length exceeds the capacity limit for this server."
                ),
                415 => array(
                    'title' => 'Unsupported Media Type',
                    'message' => "The supplied request data is not in a format acceptable for processing by this" .
                        " resource."
                ),
                416 => array('title' => 'Requested range not satisfiable', 'message' => ""),
                417 => array(
                    'title' => 'Expectation Failed',
                    'message' => "The expectation given in the Expect request-header field could not be met by this" .
                        " server."
                ),
                500 => array('title' => 'Internal Server Error', 'message' => $ise),
                501 => array(
                    'title' => 'Not Implemented',
                    'message' => "GET to the URL is not supported."
                ),
                502 => array(
                    'title' => 'Bad Gateway',
                    'message' => "The proxy server received an invalid response from an upstream server."
                ),
                503 => array(
                    'title' => 'Service Unavailable',
                    'message' => "The server is temporarily unable to service your request due to maintenance" .
                        " downtime or capacity problems. Please try again later."
                ),
                504 => array(
                    'title' => 'Gateway Time-out',
                    'message' => "The proxy server did not receive a timely response from the upstream server."
                )
            );
        }

        if (isset($this->_status_messages[$code])) {
            return $this->_status_messages[$code][$field];
        } else {
            return $field == 'title' ? 'Error' : $ise;
        }
    }

    /**
     * HTTP error response implementation.
     *
     * @param Pinoco $pinoco
     * @return void
     */
    public function respond($pinoco)
    {
        $protocol = $pinoco->request->server->get('SERVER_PROTOCOL', 'HTTP/1.0');
        if (!preg_match('/^HTTP\/.*$/', $protocol)) {
            $protocol = 'HTTP/1.0';
        }
        $pinoco->header($protocol . " " . $this->code . " " . $this->title);

        if ($pinoco->testing) {
            return;
        }
        if (in_array($this->code, array(204, 205, 304))) {
            return;
        }

        $pref = $pinoco->sysdir . "/error/";
        foreach (array($this->code . '.php', 'default.php') as $errfile) {
            if (file_exists($pref . $errfile)) {
                $pinoco->_includeWithThis($pref . $errfile, get_object_vars($this));
                return;
            }
        }

        $pinoco->header("Content-Type: text/html; charset=iso-8859-1");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
        echo "<html><head>\n";
        echo "<title>" . $this->code . " " . $this->title . "</title>\n";
        echo "</head><body>\n";
        echo "<h1>" . $this->code . " " . $this->title . "</h1>\n";
        echo "<p>" . $this->message . "</p>\n";
        echo "</body></html>";
    }
}
