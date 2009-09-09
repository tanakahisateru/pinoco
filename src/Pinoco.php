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
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

require_once dirname(__FILE__) . '/Pinoco/Vars.php';
require_once dirname(__FILE__) . '/Pinoco/List.php';
require_once dirname(__FILE__) . '/Pinoco/Renderer.php';
require_once dirname(__FILE__) . '/Pinoco/FlowControl.php';

/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * Install PHPTAL.
 * Make your application directory anywhere.
 * Put .htaccess in your site root.
 * <code>
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} \.(html|php)$ [OR]
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !_gateway\.php$
 * RewriteRule ^(.*)$   _gateway.php/$1 [L,QSA]
 * #...or RewriteRule ^(.*)$   _gateway.php?PATH_INFO=$1 [L,QSA]
 * </code>
 * Put _gateway.php in your site root.
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
 * @package Pinoco
 * @property-read string $baseuri
 * @property-read string $basedir
 * @property-read string $sysdir
 * @property-read string $path
 * @property-read string $script
 * @property-read Pinoco_List $activity
 * @property-read string $subpath
 * @property string $directory_index
 * @property string $page
 * @property-read Pinoco_Vars $renderers
 * @property-read Pinoco_Vars $autolocal
 * @property callback $url_modifier
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
    private $_directory_index;  // R/W string
    
    private $_renderers; // R/M renderers
    private $_page;      // R/W string
    
    private $_autolocal; // R/M vars
    private $_url_modifier; // R/W callable
    
    // hidden
    private $_dispatcher;
    private $_manually_rendered;
    private $_script_include_stack;
    
    private static $_current_instance = null;
    
    /**
     * 
     * @param string $sysdir
     * @param array $options
     * @return Pinoco
     */
    public static function create($sysdir, $options=array())
    {
        // options
        $use_mod_rewrite  = isset($options['use_mod_rewrite'])  ? $options['use_mod_rewrite'] : TRUE;
        $use_path_info    = isset($options['use_path_info'])    ? $options['use_path_info'] : TRUE;
        $custom_path_info = isset($options['custom_path_info']) ? $options['custom_path_info'] : FALSE;
        
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
        $seppos = strpos($uri, '?');
        if($seppos !== FALSE) {
            $uri = substr($uri, 0, $seppos);
        }
        $seppos = strpos($uri, '#');
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
        return new self($baseuri, $dispatcher, $path, dirname($_SERVER['SCRIPT_FILENAME']), $sysdir);
    }
    
    /**
     * 
     * @param string $baseuri
     * @param string $dispatcher
     * @param string $path
     * @param string $basedir
     * @param string $sysdir
     * @param array $directory_index
     */
    function __construct($baseuri, $dispatcher, $path, $basedir, $sysdir)
    {
        $this->_baseuri = $baseuri;
        $this->_dispatcher = $dispatcher;
        $this->_path = $path;
        $this->_basedir = $basedir;
        $this->_sysdir = realpath($sysdir);
        if(!is_dir($this->_sysdir)) {
            trigger_error("Invalid system directory:" . $sysdir . " is not exists.");
        }
        
        if($this->_path[strlen($this->_path) - 1] != '/' &&
            (is_dir($this->_basedir . $this->_path) || is_dir($this->_sysdir . "/hooks" . $this->_path))) {
            $this->_path .= '/';
        }
        
        $this->_directory_index = "index.html index.php"; // default index files
        
        /*
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
        */
        
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
    
    public function __toString() { return __CLASS__ . " " . self::VERSION; }
    
    // factory
    /**
     * @param mixed $init
     * @return Pinoco_Vars
     */
    public function newvars($init=array())
    {
        return Pinoco_Vars::from_array($init);
    }
    
    /**
     * 
     * @param mixed $init
     * @return Pinoco_List
     */
    public function newlist($init=array())
    {
        return Pinoco_List::from_array($init);
    }
    
    /**
     * 
     * @param array &$ref
     * @return Pinoco_Vars
     */
    public function wrapvars(&$ref)
    {
        return Pinoco_Vars::wrap($ref);
    }
    
    /**
     * 
     * @param array &$ref
     * @return Pinoco_List
     */
    public function wraplist(&$ref)
    {
        return Pinoco_List::wrap($ref);
    }
    
    /**
     * 
     * @param string $class
     * @return object
     */
    public function newobj($class)
    {
        $seppos = strrpos($class, '/');
        if($seppos !== FALSE) {
            $srcfile = substr($class, 0, $seppos);
            $class = substr($class, $seppos + 1);
            if(!$this->using($srcfile)){
                trigger_error($srcfile . " was not found.");
                return null;
            }
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
    
    /**
     *
     * @return string
     */
    private function _extended_include_path() {
        $incpathes = array();
        
        // search from hook script dir
        if($this->_script) {
            array_push($incpathes, realpath($this->parent_path($this->_script)));
        }
        
        // "lib" in sysdir is a fallback script search path
        array_push($incpathes, realpath($this->_sysdir . "/lib"));
        
        // append existing elements
        $orig_incpath = ini_get('include_path');
        $sep = substr(PHP_OS, 0, 3) == "WIN" ? ";" : ":";
        foreach(explode($sep, $orig_incpath) as $ip) {
            $absip = realpath($ip);
            if(!array_search($absip, $incpathes)) {
                array_push($incpathes, $absip);
            }
        };
        
        return implode($sep, $incpathes);
    }
    
    /**
     *
     * @param string $filename
     * @param string $pathes
     * @return bool
     */
    private function _file_exists_in_search_path($filename, $pathes) {
        if(preg_match('/^([A-Za-z]+:)?[\\/\\\\].+/', $filename)){
            return is_file($filename);
        }
        else {
            $sep = substr(PHP_OS, 0, 3) == "WIN" ? ";" : ":";
            foreach(explode($sep, $pathes) as $p) {
                if(is_file($p . "/" . $filename)) {
                    return TRUE;
                }
            }
            return FALSE;
        }
    }
    
    /**
     * 
     * @param string $script_path
     * @return bool
     */
    public function using($script_path)
    {
        $extended_incpath = $this->_extended_include_path();
        if(!$this->_file_exists_in_search_path($script_path, $extended_incpath)) {
            return FALSE;
        }
        $orig_incpath = ini_get('include_path');
        ini_set('include_path', $extended_incpath);
        include_once $script_path;
        ini_set('include_path', $orig_incpath);
        return TRUE;
    }
    
    /**
     * 
     * @param string $script_abs_path
     * @param array $localvars
     * @return bool
     */
    public function include_with_this($script_abs_path, $localvars=array())
    {
        // script path must be absolute and exist.
        if(!preg_match('/^([A-Za-z]+:)?[\\/\\\\].+/', $script_abs_path) ||
            !is_file($script_abs_path)) {
            return FALSE;
        }
        
        if(!is_array($this->_script_include_stack)) {
            $this->_script_include_stack = array();
        }
        array_push($this->_script_include_stack, $script_abs_path);
        unset($script_abs_path);
        extract($localvars);
        unset($localvars);
        include($this->_script_include_stack[count($this->_script_include_stack) - 1]);
        array_pop($this->_script_include_stack);
        return TRUE;
    }
    
    // reserved props
    /**
     * 
     * @return string
     */
    public function get_baseuri() { return $this->_baseuri; }
    
    /**
     * 
     * @return string
     */
    public function get_basedir() { return $this->_basedir; }
    
    /**
     * 
     * @return string
     */
    public function get_sysdir()  { return $this->_sysdir; }
    
    /**
     * 
     * @return string
     */
    public function get_path()    { return $this->_path; }
    
    /**
     * 
     * @return string
     */
    public function get_script()  { return $this->_script; }
    
    /**
     * 
     * @return string
     */
    public function get_activity(){ return $this->_activity; }
    
    /**
     * 
     * @return string
     */
    public function get_subpath() { return $this->_subpath; }
    
    /**
     * 
     * @return string
     */
    public function get_directory_index() { return $this->_directory_index; }
    
    /**
     * 
     * @return string
     */
    public function get_page()    { return $this->_page; }
    
    /**
     * 
     * @return string
     */
    public function get_renderers(){ return $this->_renderers; }
    
    /**
     * 
     * @return string
     */
    public function get_autolocal(){ return $this->_autolocal; }
        
    /**
     * 
     * @return string
     */
    public function get_url_modifier(){ return $this->_url_modifier; }
    
    /**
     * 
     * @param $page
     * @return void
     */
    public function set_page($page) { $this->_page = $this->resolve_path($page); }
    
    /**
     * 
     * @param $files
     * @return void
     */
    public function set_directory_index($files) { $this->_directory_index = $files; }
    
    /**
     * 
     * @param $callable
     * @return void
     */
    public function set_url_modifier($callable) { $this->_url_modifier = $callable; }
    
    /**
     * 
     * @param stirng $name
     * @return mixed
     * @see src/Pinoco/Pinoco_Vars#get($name)
     */
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
    
    /**
     *
     * @param stirng $name
     * @return bool
     * @see src/Pinoco/Pinoco_Vars#has($name)
     */
    public function has($name)
    {
        return method_exists($this, 'get_' . $name) || parent::has($name);
    }
    
    /**
     * 
     * @return Pinoco_List
     * @see src/Pinoco/Pinoco_Vars#keys()
     */
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
    
    /**
     *
     * @param string $name
     * @param mixed $value
     * @see src/Pinoco/Pinoco_Vars#set($name, $value)
     */
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
    
    /**
     * 
     * @return void
     */
    public function skip()
    {
        throw new Pinoco_FlowControlSkip();
    }
    
    /**
     * 
     * @return void
     */
    public function terminate()
    {
        throw new Pinoco_FlowControlTerminate();
    }
    
    /**
     * 
     * @param int $code
     * @param string $title
     * @param string $message
     * @return void
     */
    public function error($code, $title="", $message="")
    {
        throw new Pinoco_FlowControlHttpError($code, $title, $message);
    }
    
    /**
     * 
     * @param string $url
     * @param bool $extrenal
     * @return void
     */
    public function redirect($url, $extrenal=FALSE)
    {
        throw new Pinoco_FlowControlHttpRedirect($url, $extrenal);
    }
    
    /**
     * 
     * @return void
     */
    public function notfound()
    {
        $this->error(404, "Not found",
            "The requested URL " . $_SERVER['REQUEST_URI'] . " is not availavle on this server.");
    }
    
    /**
     * 
     * @return void
     */
    public function forbidden()
    {
        $this->error(403, "Forbidden",
            "You don't have privileges to access this resource.");
    }
    
    /**
     * 
     * @param string $path
     * @return string
     */
    public function parent_path($path)
    {
        $dn = dirname($path);
        if($dn == "\\") { $dn = "/"; }
        if($dn == ".") { $dn = ""; }
        return $dn;
    }
    
    /**
     * 
     * @param string $path
     * @param string $base
     * @return string
     */
    public function resolve_path($path, $base=FALSE)
    {
        if(strlen($path) > 0 && $path[0] != '/') {
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
            if(count($bes) == 1) {
                array_push($bes, '');
            }
            return implode("/", $bes);
        }
        else {
            return $path;
        }
    }
    
    /**
     * 
     * @param string $path
     * @return bool
     */
    public function is_renderable_path($path)
    {
        $sepp = strpos($path, "?");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $sepp = strpos($path, "#");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return $ext && $this->_renderers->has($ext);
    }
    
    /**
     * 
     * @param string $path
     * @return string
     */
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
    
    /**
     * 
     * @return string|null
     */
    public function default_page()
    {
        $page = $this->_path;
        if($page[strlen($page) - 1] == "/") {
            foreach(explode(" ", $this->_directory_index) as $idx) {
                if(is_file($this->_basedir . $page . $idx)) {
                    $page .= $idx;
                    break;
                }
            }
        }
        if($page[strlen($page) - 1] == "/") {
            return NULL;
        }
        
        $fullpath = $this->_basedir . $page;
        $ext = pathinfo($fullpath, PATHINFO_EXTENSION);
        if($ext && $this->_renderers->has($ext) && is_file($fullpath)) {
            return $page;
        }
        else {
            return NULL;
        }
    }
    
    /**
     * 
     * @param string $page
     * @return void
     */
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
    
    /**
     * 
     * @param string $filename
     * @return string
     */
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
        if ($this->using('MIME/Type.php')) {
            return MIME_Type::autoDetect($filename);
        }
        // final fallback process
        include_once dirname(__FILE__) . '/Pinoco/MIMEType.php';
        return Pinoco_MIMEType::from_filename($filename);
    }
    
    /**
     * 
     * @return Pinoco
     */
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
    /**
     * 
     * @internal
     * @return void
     */
    private function _credit_into_x_powerd_by()
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
    
    /*
    NOT IN USE NOW!
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
    */
    
    /**
     * 
     * @param string $script
     * @param string $subpath
     * @return bool
     * @internal
     */
    private function _run_hook_if_exists($script, $subpath) {
        if(file_exists($script)) {
            $this->_script = $script;
            $this->_subpath = $subpath;
            try {
                $this->include_with_this($this->_script, $this->_autolocal->to_array());
            }
            catch(Pinoco_FlowControlSkip $ex) { }
            $this->_activity->push($this->_script);
            $this->_subpath = "";
            $this->_script = null;
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    /**
     * 
     * @param bool $output_buffering
     * @return void
     */
    public function run($output_buffering=TRUE)
    {
        // insert credit into X-Powered-By header
        $this->_credit_into_x_powerd_by();
        
        try {
            // No dispatcher indicates to force to use mod_rewrite.
            $with_rewite = strpos($_SERVER['REQUEST_URI'], "/" . basename($_SERVER['SCRIPT_NAME'])) === FALSE;
            // and index.php/ or PATH_INFO= after index.php
            if($this->_dispatcher=="" && !$with_rewite) {
                $this->forbidden();
            }
            
            // NOT IN USE NOW!
            // preprocess notfound -- if handler or page is not exists
            //if(!$this->_hook_or_page_exists()){
            //    $this->notfound();
            //}
        }
        catch(Pinoco_FlowControlHttpError $ex) {
            $ex->respond($this);
            return;
        }
        
        // non-html but existing => raw binary with mime-type header
        $realfile = $this->_basedir . $this->_path;
        if(!$this->is_renderable_path($this->_path) && is_file($realfile)) {
            header('Content-Type:' . $this->mime_type($realfile));
            $st = stat($realfile);
            header('Last-Modified:' . str_replace('+0000', 'GMT', gmdate("r", $st['mtime'])));
            readfile($realfile);  // TODO : streaming
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
                    
                    // directory access : the last element is empty like "/foo/".
                    if(count($uris) == 0 && $fename == "") {
                        foreach(explode(" ", $this->_directory_index) as $idx) {
                            if(is_file($hookbase . $dpath . "/" . $idx . ".php")) {
                                $fename = $idx;
                                break;
                            }
                        }
                        if($fename == "") {
                            $fename = "_default";
                        }
                    }
                    
                    // enter
                    $this->_run_hook_if_exists($hookbase . $dpath . "/_enter.php", implode('/', $uris));
                    
                    // main script
                    if($this->_run_hook_if_exists($hookbase . $dpath . "/" . $fename . ".php", implode('/', $uris))) {
                        $proccessed = true;
                        break;
                    }
                    else if(count($uris) == 0) {
                        if($this->_run_hook_if_exists($hookbase . $dpath . "/_default.php", implode('/', $uris))) {
                            $proccessed = true;
                            break;
                        }
                    }
                }
            }
            catch(Pinoco_FlowControlTerminate $ex) {
                $this->_activity->push($this->_script);
                $this->_script = null;
                $this->_subpath = "";
            }
            
            //render
            if(!$this->_manually_rendered) {
                $page = $this->_page ? $this->_page : $this->default_page();
                if($page) {
                    $this->render($page);
                }
                else if(!$proccessed) {
                    if($this->_path[strlen($this->_path) - 1] == "/") {
                        $this->forbidden();
                    }
                    else {
                        $this->notfound();
                    }
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
            
            // leave
            $this->_run_hook_if_exists($hookbase . $dpath . "/_leave.php", implode('/', $uris));
        } while(count($process) > 0);
        
        if($output_buffering) {
            ob_end_flush();
        }
        //restore_error_handler();
        self::$_current_instance = null;
    }
}

