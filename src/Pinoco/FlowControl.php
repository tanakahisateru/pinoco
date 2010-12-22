<?php
/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * PHP Version 5
 *
 * @category Framework
 * @package  Pinoco
 * @author   Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  0.2.4
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

/**
 * Flow control object
 * @package Pinoco
 */
class Pinoco_FlowControl extends Exception {
}

/**
 * Flow control object
 * @package Pinoco
 */
class Pinoco_FlowControlSkip extends Pinoco_FlowControl {
}

/**
 * Flow control object
 * @package Pinoco
 */
class Pinoco_FlowControlTerminate extends Pinoco_FlowControl {
}

/**
 * Flow control object
 * @package Pinoco
 */
class Pinoco_FlowControlHttpError extends Pinoco_FlowControl {
    
    private $_status_messages = NULL;
    
    /**
     * Constructor
     * @param int $code
     * @param string $title
     * @param string $message
     */
    public function __construct($code, $title=NULL, $message=NULL)
    {
        $this->code = $code;
        $this->title = $title ? $title : $this->_code2message($code);
        $this->message = $message ? $message : $this->_code2message($code);
    }
    
    private function _code2message($code) {
        if(!$this->_status_messages) {
            $this->_status_messages = Pinoco::newVars(array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Time-out',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Large',
                415 => 'Unsupported Media Type',
                416 => 'Requested range not satisfiable',
                417 => 'Expectation Failed',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Time-out'
            ));
        }
        return $this->_status_messages->get($code, 'Error ' . $code);
    }
    
    /**
     * HTTP error response implementation.
     * @param Pinoco $pinoco
     * @return void
     */
    public function respond($pinoco)
    {
        header("HTTP/1.0 " . $this->code . " " . $this->title);
        
        $pref = $pinoco->sysdir . "/error/";
        foreach(array($this->code . '.php', 'default.php') as $errfile) {
            if(file_exists($pref . $errfile)) {
                $pinoco->includeWithThis($pref . $errfile, get_object_vars($this));
                return;
            }
        }
        
        header("Content-Type: text/html; charset=iso-8859-1");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
        echo "<html><head>\n";
        echo "<title>" . $this->code . " " . $this->title . "</title>\n";
        echo "</head><body>\n";
        echo "<h1>" . $this->code . " " . $this->title . "</h1>\n";
        echo "<p>" . $this->message . "</p>\n";
        echo "</body></html>";
    }
}

/**
 * Flow control object
 * @package Pinoco
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
        $protocol = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS']) ? "https" : "http";
        $server_prefix = $protocol . '://' . $_SERVER['SERVER_NAME'];
        $fixedurl = "";
        if(preg_match('/^\w+:\/\/[^\/]/', $this->url)) {
            $fixedurl = $this->url;
        }
        else if(preg_match('/^\/\/[^\/]/', $this->url)) {
            $fixedurl = $protocol . ':' . $this->url;
        }
        else if(preg_match('/^\/[^\/]?/', $this->url)) {
            if($this->extrenal) {
                $fixedurl = $server_prefix. $this->url;
            }
            else {
                $fixedurl = $server_prefix. $pinoco->url($this->url);
            }
        }
        else {
            $fixedurl = $server_prefix. $pinoco->url($this->url);
        }
        header('Location: ' . $fixedurl);
    }
}
