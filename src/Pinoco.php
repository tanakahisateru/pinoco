<?php
/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * PHP Version 5
 *
 * @category Pinoco
 * @package  Pinoco
 * @author   Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  0.1.0-beta1
 * @link     
 */

require_once dirname(__FILE__) . '/Pinoco/Vars.php';
require_once dirname(__FILE__) . '/Pinoco/List.php';
require_once dirname(__FILE__) . '/Pinoco/Renderer.php';
require_once dirname(__FILE__) . '/Pinoco/FlowControl.php';

/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * Install PHPTAL first, anyway.
 *
 * .htaccess
 * <code>
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} \.(html|php)$ [OR]
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !_gateway\.php$
 * RewriteRule ^(.*)$   _gateway.php/$1 [L,QSA]
 * #...or RewriteRule ^(.*)$   _gateway.php?PATH_INFO=$1 [L,QSA]
 * </code>
 *
 * _gateway.php
 * <code>
 * require_once 'Pinoco.php';
 * Pinoco::create("*** your_app_dir ***", array(
 * //    'use_mod_rewrite'  => TRUE,  // TRUE or FALSE default TRUE
 * //    'use_path_info'    => TRUE,  // TRUE or FALSE default TRUE
 * //    'custom_path_info' => FALSE, // FALSE(auto) or string default FALSE
 * //    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
 * ))->run();
 * </code>
 *
 * And put hooks, lib, errors or any of else into your_app_dir.
 */
class Pinoco extends Pinoco_Vars {
    
    const VERSION = "0.1.0-beta1";
    
    private $_baseuri;   // R gateway index.php location on internet
    private $_basedir;   // R gateway index.php location on file system
    private $_sysdir;    // R base directory for scripts
    
    private $_path;      // R string
    private $_script;    // R string
    private $_activity;  // R string
    private $_subpath;   // R string
    
    private $_renderers; // R/W renderers
    private $_page;      // R/W string
    
    private $_autolocal; // R/M vars
    private $_url_modifier; // R/W callable
    
    // hidden
    private $_dispatcher;
    private $_manually_rendered;
    private static $_current_instance = null;
    
    public static function create($sysdir, $options=array())
    {
        // options
        $use_mod_rewrite  = isset($options['use_mod_rewrite'])  ? $options['use_mod_rewrite'] : TRUE;
        $use_path_info    = isset($options['use_path_info'])    ? $options['use_path_info'] : TRUE;
        $custom_path_info = isset($options['custom_path_info']) ? $options['custom_path_info'] : FALSE;
        $directory_index  = isset($options['directory_index'])  ? $options['directory_index'] : "index.html index.php";
        
        // raw path info
        $pathinfo_name = ($custom_path_info !== FALSE) ? $custom_path_info : 'PATH_INFO';
        if($use_path_info) {
            $pathinfo = isset($_SERVER[$pathinfo_name]) ? $_SERVER[$pathinfo_name] : @getenv('PATH_INFO');
        }
        else {
            $pathinfo = isset($_GET[$pathinfo_name]) ? $_GET[$pathinfo_name] : "";
        }
        
        // path
        $path = $pathinfo;
        if(!preg_match('/^\//', $path)) {
            $path = "/" . $path;
        }
        
        // dispatcher
        if($use_mod_rewrite) {
            $gateway = "";
            $dispatcher = "";
        }
        else {
            $gateway = basename($_SERVER['SCRIPT_NAME']);
            $dispatcher = "/" . $gateway;
            if(!$use_path_info) {
                $dispatcher .= "?" . $pathinfo_name . "=";
            }
        }
        
        // base uri (gateway placed path)
        $uri = $_SERVER['REQUEST_URI'];
        $seppos = min(strpos($uri, '?'), strpos($uri, '#'));
        if($seppos !== FALSE) {
            $uri = substr($uri, 0, $seppos);
        }
        if($use_mod_rewrite) {
            $trailings = $pathinfo;
        }
        else if($use_path_info) {
            $trailings = "/" . $gateway . $pathinfo;
        }
        else {
            $trailings = "/" . $gateway;
        }
        $baseuri = substr($uri, 0, strlen($uri) - strlen($trailings));
        
        // build engine
        return new self($baseuri, $dispatcher, $path, dirname($_SERVER['SCRIPT_FILENAME']), $sysdir, $directory_index);
    }
    
    function __construct($baseuri, $dispatcher, $path, $basedir, $sysdir, $directory_index="index.html index.php")
    {
        $this->_baseuri = $baseuri;
        $this->_dispatcher = $dispatcher;
        $this->_path = $path;
        $this->_basedir = $basedir;
        $this->_sysdir = realpath($sysdir);
        if(!is_dir($this->_sysdir)) {
            trigger_error("Invalid system directory:" . $sysdir . " is not exists.");
        }
        
        if(is_dir($this->_basedir . $this->_path) && $this->_path[strlen($this->_path) - 1] != '/') {
            $this->_path .= '/';
        }
        if($this->_path[strlen($this->_path) - 1] == '/') {
            foreach(explode(" ", $directory_index) as $indexfile) {
                if(file_exists($this->_basedir . $this->_path . $indexfile)) {
                    $this->_path .= $indexfile;
                    break;
                }
            }
        }
        if($this->_path[strlen($this->_path) - 1] == '/') {
            $this->_path .= 'index.html';
        }
        
        $this->_script = null;
        $this->_activity = $this->newlist();
        $this->_subpath = "";
        
        $this->_renderers = $this->newvars();
        $this->_renderers->setdefault(new Pinoco_NullRenderer($this));
        $this->_renderers->html = new Pinoco_TALRenderer($this);
        $this->_renderers->php  = new Pinoco_NativeRenderer($this);
        
        $this->_page = NULL;
        
        $this->_autolocal = $this->newvars();
        $this->_url_modifier = NULL;
        
        parent::__construct();
        
        // fix current directory here!!
        //chdir($this->_sysdir);
    }
    
    public function __toString() { return __CLASS__ . " " . self::VERSION; } // TODO: dump vars name/values
    
    private static function _credit_into_x_powerd_by()
    {
        $CREDIT_LOGO = __CLASS__ . "/" . self::VERSION;
        if(!headers_sent()) {
            $found = false;
            foreach(headers_list() as $http_header) {
                if(preg_match('/^X-Powered-By:/', $http_header)) {
                    $found = true;
                    header($http_header . " " . $CREDIT_LOGO);
                    break;
                }
            }
            if(!$found) {
                header("X-Powered-By: " . $CREDIT_LOGO);
            }
        }
    }
    
    public function default_page()
    {
        $fullpath = $this->_basedir . $this->_path;
        $ext = pathinfo($this->_path, PATHINFO_EXTENSION);
        if($ext && $this->_renderers->has($ext) && file_exists($fullpath)) {
            return $this->_path;
        }
        else {
            return NULL;
        }
    }
    
    // factory

    public function newvars($init=array())
    {
        return Pinoco_Vars::from_array($init);
    }
    
    public function newlist($init=array())
    {
        return Pinoco_List::from_array($init);
    }
    
    public function wrapvars(&$ref)
    {
        return Pinoco_Vars::wrap($ref);
    }
    
    public function wraplist(&$ref)
    {
        return Pinoco_List::wrap($ref);
    }
    
    public function newobj($class)
    {
        $seppos = strrpos($class, '/');
        if($seppos !== FALSE) {
            $srcfile = substr($class, 0, $seppos);
            $class = substr($class, $seppos + 1);
            $this->using($srcfile);
        }
        $argstr = array();
        for($i = 1; $i < func_num_args(); $i++) {
            array_push($argstr, sprintf('func_get_arg(%d)', $i));
        }
        if(class_exists($class)) {
            eval(sprintf('$object = new %s(%s);', $class, implode(', ', $argstr)));
            return $object;
        }
        else {
            if($seppos !== FALSE) {
                trigger_error($class . " may not be defined on " . $srcfile . ".");
            }
            else {
                trigger_error($class . " is not defined.");
            }
            return null;
        }
    }
    
    public function using($script_path)
    {
        $incpathes = array(
            // search into hook script dir
            dirname($this->_script) . "/" . $script_path,
            // "_system/lib" is a fallback script search path
            $this->_sysdir . "/lib/" . $script_path
        );
        foreach($incpathes as $p) {
            if(file_exists($p) && is_file($p)) {
                include_once $p;
            }
        }
        // default
        include_once $script_path;
    }
    
    public function include_with_this($abspath, $localvars=array())
    {
        extract($localvars);
        include($abspath);
    }
    
    // reserved props
    public function get_baseuri() { return $this->_baseuri; }
    public function get_basedir() { return $this->_basedir; }
    public function get_sysdir()  { return $this->_sysdir; }
    
    public function get_path()    { return $this->_path; }
    public function get_script()  { return $this->_script; }
    public function get_activity(){ return $this->_activity; }
    public function get_subpath() { return $this->_subpath; }
    public function get_page()    { return $this->_page; }
    public function get_renderers(){ return $this->_renderers; }
    
    public function get_autolocal(){ return $this->_autolocal; }
    public function get_url_modifier(){ return $this->_url_modifier; }
    
    public function set_page($page) { $this->_page = $this->resolve_path($page); }
    public function set_url_modifier($callable) { $this->_url_modifier = $callable; }
    
    
    // Bag implementation
    public function get($name)
    {
        if(method_exists($this, 'get_' . $name)) {
            return call_user_func(array($this, 'get_' . $name));
        }
        else {
            if(func_num_args() > 1) {
                return parent::get($name, func_get_arg(1));
            }
            else {
                return parent::get($name);
            }
        }
    }

    public function has($name)
    {
        return method_exists($this, 'get_' . $name) || parent::has($name);
    }
    
    public function keys()
    {
        $meths = get_class_methods($this);
        $ks = array();
        $m = array();
        foreach($meths as $meth) {
            if(preg_match("/^get_(.*)$/", $meth, $m)) {
                array_push($ks, $m[1]);
            }
        }
        $ks = Pinoco_List::from_array($ks);
        $ks->concat(parent::keys());
        return $ks;
    }
    
    // Bag as mutable
    public function set($name, $value)
    {
        if(method_exists($this, 'set_' . $name)) {
            call_user_func(array($this, 'set_' . $name), $value);
        }
        else if(method_exists($this, 'get_' . $name)) {
            trigger_error("Cannot reassign to ". $name . ".");
        }
        else {
            parent::set($name, $value);
        }
    }
    
    // flow control core
    public function skip()
    {
        throw new Pinoco_FlowControlSkip();
    }
    
    public function terminate()
    {
        throw new Pinoco_FlowControlTerminate();
    }
    
    public function error($code, $title="", $message="")
    {
        throw new Pinoco_FlowControlHttpError($code, $title, $message);
    }

    public function redirect($url, $extrenal=FALSE)
    {
        throw new Pinoco_FlowControlHttpRedirect($url, $extrenal);
    }
    
    public function notfound()
    {
        $this->error(404, "Not found",
            "The requested URL " . $_SERVER['REQUEST_URI'] . " is not availavle on this server.");
    }
    
    public function forbidden()
    {
        $this->error(403, "Forbidden",
            "You don't have privileges to access this resource.");
    }
    
    // utils
    public function parent_path($path)
    {
        $dn = dirname($path);
        if($dn == "\\") { $dn = "/"; }
        if($dn == ".") { $dn = ""; }
        return $dn;
    }
    
    public function resolve_path($path, $base=FALSE)
    {
        if($path[0] != '/') {
            // make path absolute if relative
            $base = $base === FALSE ? $this->parent_path($this->_path) : $base;
            $bes = explode("/", rtrim($base, "/"));
            $pes = explode("/", $path);
            foreach($pes as $pe) {
                if($pe == "..") {
                    array_pop($bes);
                }
                else if($pe != ".") {
                    array_push($bes, $pe);
                }
            }
            return implode("/", $bes);
        }
        else {
            return $path;
        }
    }
    
    public function is_renderable_path($path)
    {
        $sepp = strpos($path, "?");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $sepp = strpos($path, "#");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return $ext && $this->_renderers->has($ext);
    }
    
    public function url($path='')
    {
        if($path != '') {
            $path = $this->resolve_path($path);
        }
        // guess to use gateway script but not in use mod_rewrite.
        if((
                $this->is_renderable_path($path) ||
                !file_exists($this->_basedir . $path) ||
                is_dir($this->_basedir . $path)
            ) && $this->_dispatcher != ""
        ) {
            // join both url params of dispatcher and path if they have "?" commonly.
            $dqpos = strpos($this->_dispatcher, "?");
            $pqpos = strpos($path, "?");
            if($dqpos !== FALSE && $pqpos !== FALSE) {
                $path = substr($path, 0, $pqpos) . "&" . substr($path, $pqpos + 1);
            }
            $url = rtrim($this->_baseuri, "/") . $this->_dispatcher . $path;
            $renderable = TRUE;
        }
        else {
            $url = rtrim($this->_baseuri, "/") . $path;
            $renderable = FALSE;
        }
        return $this->_url_modifier ? call_user_func($this->_url_modifier, $url, $renderable) : $url;
    }
    
    public function render($page)
    {
        $page = $this->resolve_path($page);
        $ext = pathinfo($page, PATHINFO_EXTENSION);
        if($ext && file_exists($this->_basedir . '/' . $page)) {
            $renderer = $this->_renderers[$ext];
            $renderer->render($page);
        }
        $this->_manually_rendered = true;
    }
    
    public function mime_type($filename)
    {
        if(file_exists($filename)) {

            if(function_exists('finfo_open'))
            {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $type = finfo_file($finfo, $filename);
                finfo_close($finfo);
                if($type) {
                    return $type;
                }
            }
            if(function_exists('mime_content_type')) {
                $type = mime_content_type($filename);
                if($type) {
                    return $type;
                }
            }
        }
        @include_once 'MIME/Type.php';
        if (class_exists('MIME_Type')) {
            return MIME_Type::autoDetect($filename);
        }
        // final fallback process
        include_once dirname(__FILE__) . '/Pinoco/MIMEType.php';
        return Pinoco_MIMEType::from_filename($filename);
    }
    
    public static function instance()
    {
        return self::$_current_instance;
    }
    /* 
     * IDEA: Pinoco::instance()->some_method() can write as Pinoco::some_method()
     * PHP >= 5.3.0
     
    public static function __callStatic($name, $arguments)
    {
        $instance = self::instance();
        if($instance) {
            return call_user_func_array(array($instance, $name), $arguments);
        }
        else {
            trigger_error(__CLASS__ . " method called in invalid state", E_USER_WARNING);
        }
    }
    
    public static function __getStatic() / public static function __setStatic() ...
    */
    
    // runtime core
    private function _run_script_in_infected_scope()
    {
        $this->include_with_this($this->_script, $this->_autolocal->to_array());
    }
    
    private function _hook_or_page_exists()
    {
        $hookbase = $this->_sysdir . "/hooks";
        $uris = explode("/", ltrim($this->_path, "/"));
        $dpath = "";
        foreach($uris as $fename) {
            if(preg_match('/^_.*$/', $fename)) {
                return false;
            }
            if(file_exists($hookbase . $dpath . "/" . $fename . ".php")) {
                return true;
            }
            $dpath .= "/" . $fename;
        }
        if(file_exists($this->basedir . $this->_path) ||
            file_exists($hookbase . dirname($this->_path) . "/_default.php")) {
            return true;
        }
        return false;
    }
    
    public function run($output_buffering=TRUE)
    {
        // insert credit into X-Powered-By header
        self::_credit_into_x_powerd_by();
        
        try {
            // No dispatcher indicates to force to use mod_rewrite.
            $with_rewite = strpos($_SERVER['REQUEST_URI'], "/" . basename($_SERVER['SCRIPT_NAME'])) === FALSE;
            // and index.php/ or PATH_INFO= after index.php
            if($this->_dispatcher=="" && !$with_rewite) {
                //$this->redirect($this->_baseuri);
                //$this->notfound();
                $this->forbidden();
            }
            // preprocess notfound -- if handler or page is not exists
            if(!$this->_hook_or_page_exists()){
                $this->notfound();
            }
            // unexpected request: non-html but existing
            $realfile = $this->_basedir . $this->_path;
            if(!$this->is_renderable_path($this->_path) && file_exists($realfile)) {
                header('Content-Type:' . $this->mime_type($realfile));
                $st = stat($realfile);
                header('Last-Modified:' . str_replace('+0000', 'GMT', gmdate("r", $st['mtime'])));
                readfile($realfile);  // TODO : streaming
                return;
            }
        }
        catch(Pinoco_FlowControlHttpError $ex) {
            $ex->respond($this);
            return;
        }
        
        self::$_current_instance = $this;
        //set_error_handler(array($this, "_exception_error_handler"));
        if($output_buffering) {
            ob_start();
        }
        $this->_manually_rendered = false;
        try {
            $hookbase = $this->_sysdir . "/hooks";
            
            $_handler_available = false;
            
            $uris = explode("/", ltrim($this->_path, "/"));
            $process = array();
            $proccessed = false;
            try {
                while(count($uris) > 0) {
                    $dpath = (count($process) == 0 ? "" : "/") . implode('/', $process);
                    $fename = array_shift($uris);
                    array_push($process, $fename);
                    
                    // invisible file entry name.
                    if(preg_match('/^_.*$/', $fename)) {
                        $this->notfound();
                    }
                    
                    $this->_script = $hookbase . $dpath . "/_enter.php";
                    if(file_exists($this->_script)) {
                        $this->_subpath = implode('/', $uris);
                        try {
                            $this->_run_script_in_infected_scope();
                        }
                        catch(Pinoco_FlowControlSkip $ex) { }
                        $this->_activity->push($this->_script);
                        $this->_subpath = "";
                    }
                    $this->_script = null;
                    
                    $this->_script = $hookbase . $dpath . "/" . $fename . ".php";
                    $fallback_script = $hookbase . $dpath . "/_default.php";
                    if(file_exists($this->_script)) {
                        $_handler_available = true;
                        $this->_subpath = implode('/', $uris);
                        try {
                            $this->_run_script_in_infected_scope();
                        }
                        catch(Pinoco_FlowControlSkip $ex) { }
                        $this->_activity->push($this->_script);
                        $this->_subpath = "";
                        $proccessed = true;
                        $this->_script = null;
                        break;
                    }
                    $this->_script = null;
                }
                if(!$proccessed && isset($fallback_script)) {
                    $this->_script = $fallback_script;
                    if(file_exists($this->_script)) {
                        $_handler_available = true;
                        $this->_subpath = $fename;
                        try {
                            $this->_run_script_in_infected_scope();
                        }
                        catch(Pinoco_FlowControlSkip $ex) { }
                        $this->_activity->push($this->_script);
                        $this->_subpath = "";
                    }
                    $this->_script = null;
                }
            }
            catch(Pinoco_FlowControlTerminate $ex) {
                $this->_activity->push($this->_script);
                $this->_script = null;
                $this->_subpath = "";
            }
            
            if(!$this->_manually_rendered) {
                $page = $this->_page ? $this->_page : $this->default_page();
                if($page) {
                    $this->render($page);
                }
                else if(!$_handler_available) {
                    $this->notfound();
                }
            }
        }
        catch(Pinoco_FlowControlHttpError $ex) { // contains Redirect
            $this->_activity->push($this->_script);
            $this->_script = null;
            $this->_subpath = "";
            $ex->respond($this);
        }
        
        // cleanup process
        do {
            $fename = array_pop($process);
            array_unshift($uris, $fename);
            $dpath = (count($process) == 0 ? "" : "/") . implode('/', $process);
            $this->_script = $hookbase . $dpath . "/_leave.php";
            if(file_exists($this->_script)) {
                $this->_subpath = implode("/", $uris);
                try {
                    $this->_run_script_in_infected_scope();
                }
                catch(Pinoco_FlowControl $ex) { }
                $this->_activity->push($this->_script);
                $this->_subpath = "";
            }
            $this->_script = null;
        } while(count($process) > 0);
        
        if($output_buffering) {
            ob_end_flush();
        }
        //restore_error_handler();
        self::$_current_instance = null;
    }
}


