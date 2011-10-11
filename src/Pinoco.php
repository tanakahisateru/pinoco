<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @category   Framework
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version    0.5.2
 * @link       https://github.com/tanakahisateru/pinoco
 * @filesource
 * @package    Pinoco
 */

/**
 * SPL autoload implementation for Pinoco libs
 * @param string $class
 * @return void
 */
function __pinoco_autoload_impl($class)
{
    if(substr($class, 0, 7) === 'Pinoco_') {
        require_once dirname(__FILE__) . '/' . strtr($class, "_", "/") . '.php';
    }
}
spl_autoload_register('__pinoco_autoload_impl');

/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * Install PHPTAL.
 * Make your application directory anywhere.
 *
 * Put .htaccess in your site root.
 * <code>
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} \.(html|php)$ [OR]
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !_gateway\.php$
 * RewriteRule ^(.*)$   _gateway.php/$1 [L,QSA]
 * #...or RewriteRule ^(.*)$   _gateway.php?PATH_INFO=$1 [L,QSA]
 * </code>
 *
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
 * Pinoco::create guesses HTTP request to create Pinoco instance.
 * If this method can't work file in your environment, you can cretae Pinoco
 * environment manually via "new" operator using your own parameters.
 *
 * @package Pinoco
 * @property-read string $baseuri Base URI
 * @property-read string $basedir Base directory
 * @property-read string $sysdir  Application directory
 * @property Pinoco_List $incdir  Include pathes
 * @property-read string $path    Path under base URI
 * @property-read string $script  Current hook script
 * @property-read Pinoco_List $activity  Activity history of hook scripts
 * @property-read string $subpath Sub path under current hook script
 * @property-read Pinoco_List $pathargs Path elements matches _default[.*] hooks
 * @property string $directory_index Space separated directory index files(like Apache)
 * @property string $page         Template file to be rendered
 * @property-read Pinoco_Vars $renderers File extension to rendering module mappings
 * @property-read Pinoco_Vars $autolocal Auto extraced variables into local scope
 * @property callback $url_modifier  URL modification callback
 * @property callback $page_modifier Template page base path modification callback
 */
class Pinoco extends Pinoco_DynamicVars {
    
    const VERSION = "0.5.2";
    
    private $_baseuri;   // R gateway index.php location on internet
    private $_basedir;   // R gateway index.php location on file system
    private $_sysdir;    // R base directory for scripts
    
    private $_incdir;  // R/W include search directories
    
    private $_path;      // R string
    private $_script;    // R string
    private $_activity;  // R list
    private $_subpath;   // R string
    private $_pathargs;  // R list
    private $_directory_index;  // R/W string
    
    private $_renderers; // R/M vars
    private $_page;       // R/W string
    
    private $_autolocal; // R/M vars
    
    private $_url_modifier;  // R/W callable
    private $_page_modifier; // R/W callable
    
    // hidden
    private $_system_incdir;
    private $_dispatcher;
    private $_manually_rendered;
    private $_script_include_stack;
    
    private static $_current_instance = null;
    
    /**
     * It provides a suitable Pinoco instance in regular usage.
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
        $uri = urldecode($_SERVER['REQUEST_URI']);  // to urldecoded path like path_info or _GET params
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
            if(strpos($_SERVER['REQUEST_URI'], "/" . basename($_SERVER['SCRIPT_NAME'])) !== FALSE) {
                $trailings = "/" . basename($_SERVER['SCRIPT_NAME']) . $pathinfo;
            }
        }
        else if($use_path_info) {
            $trailings = "/" . $gateway . $pathinfo;
        }
        else {
            $trailings = "/" . $gateway;
        }
        
        // remove double slashes in path expression (e.g. /foo//bar)
        $uri = preg_replace('/\/\/+/', '/', $uri);
        $trailings = preg_replace('/\/\/+/', '/', $trailings);
        
        $baseuri = substr($uri, 0, strlen($uri) - strlen($trailings));
        
        // build engine
        return new self($baseuri, $dispatcher, $path, dirname($_SERVER['SCRIPT_FILENAME']), $sysdir);
    }
    
    /**
     * Pinoco constructor. 
     * @param string $baseuri
     * @param string $dispatcher
     * @param string $path
     * @param string $basedir
     * @param string $sysdir
     * @param array $directory_index
     * @see src/Pinoco#create($sysdir, $options)
     */
    function __construct($baseuri, $dispatcher, $path, $basedir, $sysdir)
    {
        $this->_baseuri = $baseuri;
        $this->_dispatcher = $dispatcher;
        $this->_path = $path;
        $this->_basedir = realpath($basedir);
        $this->_sysdir = realpath($sysdir);
        if(!is_dir($this->_sysdir)) {
            $exclass = class_exists('InvalidArgumentException') ? 'InvalidArgumentException' : 'Exception';
            throw new $exclass("Invalid system directory:" . $sysdir . " does not exist.");
        }
        
        $this->_incdir = self::newList();
        $this->_incdir->push($this->sysdir . "/lib"); // default lib dir
        
        $this->_system_incdir = ini_get('include_path');
        
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
        $this->_activity = self::newList();
        $this->_subpath = "";
        $this->_pathargs = self::newList();
        
        $this->_renderers = self::newVars();
        $this->_renderers->setDefault(new Pinoco_NullRenderer($this));
        $this->_renderers->html = new Pinoco_TALRenderer($this);
        $this->_renderers->php  = new Pinoco_NativeRenderer($this);
        
        $this->_page = "<default>";  // To be resolved automatically
        
        $this->_autolocal = self::newVars();
        
        $this->_url_modifier = NULL;
        $this->_page_modifier = NULL;
        
        parent::__construct();
        
        // chdir($this->_sysdir);
    }
    
    public function __toString() { return __CLASS__ . " " . self::VERSION; }
    
    // factory
    /**
     * It provides a new Vars object (that can be filled with existing Array).
     * @param mixed $init
     * @return Pinoco_Vars
     */
    public static function newVars($init=array())
    {
        return Pinoco_Vars::fromArray($init);
    }
    
    /**
     * It provides a new List object (that can be filled with existing Array).
     * @param mixed $init
     * @return Pinoco_List
     */
    public static function newList($init=array())
    {
        return Pinoco_List::fromArray($init);
    }
    
    /**
     * It provides a Vars object as existing Array wrapper.
     * @param array &$ref
     * @return Pinoco_Vars
     */
    public static function wrapVars(&$ref)
    {
        return Pinoco_Vars::wrap($ref);
    }
    
    /**
     * It provides a List object as existing Array wrapper.
     * @param array &$ref
     * @return Pinoco_List
     */
    public static function wrapList(&$ref)
    {
        return Pinoco_List::wrap($ref);
    }
    
    /**
     * It provides a new object by "path/to/src.php/ClassName" syntax.
     * @param string $class
     * @param mixed $args,...
     * @return object
     */
    public static function newObj($class /*[, $args[, ...]]*/)
    {
        $seppos = strrpos($class, '/');
        if($seppos !== FALSE) {
            $srcfile = substr($class, 0, $seppos);
            $class = substr($class, $seppos + 1);
            require_once $srcfile;
        }
        if(class_exists($class)) {
            $argsvals = func_get_args();
            array_shift($argsvals);
            $argsvars = array();
            for($i = 0; $i < count($argsvals); $i++) {
                $argsvars[$i] = '$argsvals[' . $i . ']';
            }
            eval(sprintf('$object = new %s(%s);', $class, implode(', ', $argsvars)));
            return $object;
        }
        else {
            $exclass = class_exists('InvalidArgumentException') ? 'InvalidArgumentException' : 'Exception';
            if($seppos !== FALSE) {
                throw new $exclass($class . " may not be defined on " . $srcfile . ".");
            }
            else {
                throw new $exclass($class . " is not defined.");
            }
            return null;
        }
    }
    
    /**
     * Wrapped PDO factory
     * @deprecated
     * @param string $dsn
     * @param string $un
     * @param string $pw
     * @param array $opts
     * @return Pinoco_PDOWrapper
     */
    public static function newPDOWrapper($dsn, $un="", $pw="", $opts=array())
    {
        return self::newObj(
            'Pinoco/PDOWrapper.php/Pinoco_PDOWrapper',
            $dsn, $un, $pw, $opts
        );
    }
    
    // reserved props
    /**
     * Web site root URI.
     * @return string
     */
    public function get_baseuri() { return $this->_baseuri; }
    
    /**
     * Web site root directory in local file system.
     * @return string
     */
    public function get_basedir() { return $this->_basedir; }
    
    /**
     * Application directory.
     * @return string
     */
    public function get_sysdir()  { return $this->_sysdir; }
    
    /**
     * Include search directories.
     * @return Pinoco_List
     */
    public function get_incdir() { return $this->_incdir; }
    
    /**
     * Local resource path under base URI.
     * @return string
     */
    public function get_path()    { return $this->_path; }
    
    /**
     * Current hook script if it running.
     * @return string
     */
    public function get_script()  { return $this->_script; }
    
    /**
     * Hook scripts invocation log.
     * @return Pinoco_List
     */
    public function get_activity(){ return $this->_activity; }
    
    /**
     * Partial path under current script.
     * @return string
     */
    public function get_subpath() { return $this->_subpath; }
    
    /**
     * Path elements resolved by _default folders or files.
     * @return Pinoco_List
     */
    public function get_pathargs() { return $this->_pathargs; }
    
    /**
     * Default files (white space splitted) for directory access.
     * @return string
     */
    public function get_directory_index() { return $this->_directory_index; }
    
    /**
     * File name to override view.
     * @return string
     */
    public function get_page()    { return $this->_page; }
    
    /**
     * Page renderers repository.
     * A renderer object is registerd to file extension as dictionary key.
     * @return Pinoco_Vars
     */
    public function get_renderers(){ return $this->_renderers; }
    
    /**
     * Automatically extracted variables for hooks and pages.
     * @return Pinoco_Vars
     */
    public function get_autolocal(){ return $this->_autolocal; }
        
    /**
     * Special filter for url conversion.
     * @return callback
     */
    public function get_url_modifier(){ return $this->_url_modifier; }
    
    /**
     * Special filter for default view file.
     * @return callback
     */
    public function get_page_modifier(){ return $this->_page_modifier; }
    
    /**
     * Include search directories.
     * @param Pinoco_List $dirs
     * @return void
     */
    public function set_incdir($dirs) { $this->_incdir = $dirs; }
    
    /**
     * File name to override view.
     * Set FALSE if you want an empty output.
     * Set "<default>" if you want to reset to default view.
     * @param string $page
     * @return void
     */
    public function set_page($page) { $this->_page = $page; }
    
    /**
     * Default files (white space splitted) for directory access.
     * @param string $files
     * @return void
     */
    public function set_directory_index($files) { $this->_directory_index = $files; }
    
    /**
     * Special filter for url conversion.
     * @param callback $callable
     * @return void
     */
    public function set_url_modifier($callable) { $this->_url_modifier = $callable; }
    
    /**
     * Special filter for default view file.
     * @param callback $callable
     * @return void
     */
    public function set_page_modifier($callable) { $this->_page_modifier = $callable; }
    
    // flow control
    /**
     * Current hook proccess will be skipped and invoke the next hook script.
     * @return void
     */
    public static function skip()
    {
        throw new Pinoco_FlowControlSkip();
    }
    
    /**
     * Whole hook stage before rendering is treminated and rendering phase is started immediately.
     * @return void
     */
    public static function terminate()
    {
        throw new Pinoco_FlowControlTerminate();
    }
    
    /**
     * It cancels hooks and rendering and respond HTTP error to browser.
     * @param int $code
     * @param string $title
     * @param string $message
     * @return void
     */
    public static function error($code, $title=NULL, $message=NULL)
    {
        throw new Pinoco_FlowControlHttpError($code, $title, $message);
    }
    
    /**
     * Special error to let broeswr change the location to access.
     * @param string $url
     * @param bool $extrenal
     * @return void
     */
    public static function redirect($url, $extrenal=FALSE)
    {
        throw new Pinoco_FlowControlHttpRedirect($url, $extrenal);
    }
    
    /**
     * Error for Not Found.
     * @return void
     */
    public static function notfound()
    {
        self::error(404);
    }
    
    /**
     * Error for Forbidden.
     * @return void
     */
    public static function forbidden()
    {
        self::error(403);
    }
    
    /**
     * Utility to get the parent path.
     * @param string $path
     * @return string
     */
    public static function parentPath($path)
    {
        $dn = dirname($path);
        if($dn == "\\") { $dn = "/"; }
        if($dn == ".") { $dn = ""; }
        return $dn;
    }
    
    /**
     * It makes relative path absolute.
     * @param string $path
     * @param string $base
     * @return string
     */
    public function resolvePath($path, $base=FALSE)
    {
        if(strlen($path) > 0 && $path[0] != '/') {
            // make path absolute if relative
            if($base === FALSE) {
                $thispath = $this->_path;
                $base = $thispath[strlen($thispath) - 1] != "/" ?
                    self::parentPath($thispath) : rtrim($thispath, "/");
            }
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
    
    /**
     * Returns if the path can be rendered by registerd renderers.
     * @param string $path
     * @return bool
     */
    public function isRenderablePath($path)
    {
        $sepp = strpos($path, "?");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $sepp = strpos($path, "#");
        if($sepp !== FALSE) { $path = substr($path, 0, $sepp); }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return $ext && $this->_renderers->has($ext);
    }
    
    /**
     * Returns host based URI from site based or sub-path based relateve one.
     * @param string $path
     * @param bool $pure cancels to call user modifier if TRUE
     * @return string
     */
    public function url($path='', $pure=FALSE)
    {
        if($path != '') {
            $path = $this->resolvePath($path);
        }
        $renderable = $this->isRenderablePath($path) ||
            !is_file($this->_basedir . $path) ||
            is_dir($this->_basedir . $path);

        // guess to use gateway script but not in use mod_rewrite.
        if($this->_dispatcher != "" && $renderable) {
            // join both url params of dispatcher and path if they have "?" commonly.
            $dqpos = strpos($this->_dispatcher, "?");
            $pqpos = strpos($path, "?");
            if($dqpos !== FALSE && $pqpos !== FALSE) {
                $path = substr($path, 0, $pqpos) . "&" . substr($path, $pqpos + 1);
            }
            $url = rtrim($this->_baseuri, "/") . $this->_dispatcher . $path;
        }
        else {
            $url = rtrim($this->_baseuri, "/") . $path;
        }
        return (!$pure && $this->_url_modifier) ? call_user_func($this->_url_modifier, $url, $renderable) : $url;
    }
    
    /**
     * Returns default page file considerd with directory index.
     * @return string|false
     */
    public function _page_from_path_with_directory_index($path)
    {
        $page = "";
        $pes = explode("/", $path);
        array_shift($pes);
        while(count($pes) > 1) {
            $pe = array_shift($pes);
            if(is_dir($this->_basedir . $page . '/' . $pe)) {
                $page .= '/' . $pe;
            }
            else if(is_dir($this->_basedir . $page . '/_default')) {
                $page .= '/_default';
            }
            else {
                return FALSE;
            }
        }
        $page .= '/' . $pes[0];
        
        if($page[strlen($page) - 1] == "/") {
            $di = "";
            foreach(explode(" ", $this->_directory_index) as $idx) {
                if(is_file($this->_basedir . $page . $idx)) {
                    $di = $idx;
                    break;
                }
            }
            if($di == "") {
                foreach(explode(" ", $this->_directory_index) as $idx) {
                    $deffile = "_default." . pathinfo($idx, PATHINFO_EXTENSION);
                    if(is_file($this->_basedir . $page . $deffile)) {
                        $di = $deffile;
                        break;
                    }
                }
            }
            $page .= $di;
        }
        if(is_file($this->_basedir . $page)) {
            return $page;
        }
        else {
            $ext = pathinfo($page, PATHINFO_EXTENSION);
            if($ext && $this->_renderers->has($ext)) {
                $default_page = self::parentPath($page) . "/_default." . $ext;
                if(is_file($this->_basedir . $default_page)) {
                    return $default_page;
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Invokes renderer with page file immediately.
     * Default rendering will be canceled.
     * Pass FALSE if you want an empty response.
     * @param string $page
     * @return void
     */
    public function render($page)
    {
        if(!$page) {
            $this->_manually_rendered = true;
            return;
        }
        $page = $this->resolvePath($page);
        $ext = pathinfo($page, PATHINFO_EXTENSION);
        if($ext && is_file($this->_basedir . '/' . $page) && isset($this->_renderers[$ext])) {
            $renderer = $this->_renderers[$ext];
            $renderer->render($page);
        }
        else {
            $exclass = class_exists('InvalidArgumentException') ? 'InvalidArgumentException' : 'Exception';
            throw new $exclass("File $page is not exists or not renderable.");
        }
        $this->_manually_rendered = true;
    }
        
    /**
     * MIME type of file.
     * @param string $filename
     * @return string
     */
    public static function mimeType($filename)
    {
        /*----------- not work well, fixme ----------
        if(file_exists($filename) && ini_get('mime_magic.magicfile')) {
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
        ----------------------------------- */
        // final fallback process
        include_once dirname(__FILE__) . '/Pinoco/MIMEType.php';
        return Pinoco_MIMEType::fromFileName($filename);
    }
    
    /**
     * Returns currently running Pinoco instance.
     * @return Pinoco
     */
    public static function instance()
    {
        return self::$_current_instance;
    }
    
    public static function __callStatic($name, $arguments)
    {
        // Pinoco::instance()->some_method() can write as Pinoco::some_method()
        // PHP >= 5.3.0
        // Why not? => public static function __getStatic() / public static function __setStatic() ...
        
        $instance = self::instance();
        if($instance) {
            return call_user_func_array(array($instance, $name), $arguments);
        }
        else {
            $exclass = class_exists('BadMethodCallException') ? 'BadMethodCallException' : 'Exception';
            throw new $exclass(__CLASS__ . " method called in invalid state");
        }
    }
    
    // runtime core
    /**
     * Writes Pinoco credit into HTTP header.
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
     * Updates 'include_path' in php.ini by this->incdir.
     */
    public function updateIncdir()
    {
        $sep = substr(PHP_OS, 0, 3) == "WIN" ? ";" : ":";
        $runinc = array();
        
        array_push($runinc, dirname($this->script));
        
        foreach($this->_incdir as $dir) {
            $cwd = getcwd();
            chdir($this->sysdir);
            array_push($runinc, realpath($dir));
            chdir($cwd);
        }
        
        $sysinc = explode($sep, $this->_system_incdir);
        foreach($sysinc as $dir) {
            $cwd = getcwd();
            chdir($this->basedir);
            array_push($runinc, realpath($dir));
            chdir($cwd);
        }
        
        ini_set('include_path', implode($sep, $runinc));
    }
    
    /**
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return void
     * @ignore
     */
    public function _error_handler($errno, $errstr, $errfile, $errline)
    {
        if(function_exists('xdebug_is_enabled') && xdebug_is_enabled()) {
            return FALSE;
        }
        if((error_reporting() & $errno) == 0) {
            return FALSE;
        }
        
        $errno2txt = array(
            E_NOTICE=>"Notice", E_USER_NOTICE=>"Notice",
            E_WARNING=>"Warning", E_USER_WARNING=>"Warning",
            E_ERROR=>"Fatal Error", E_USER_ERROR=>"Fatal Error"
        );
        $errors = isset($errno2txt[$errno]) ? $errno2txt[$errno] : "Unknown";
        
        $trace = debug_backtrace();
        array_shift($trace);
        $stacktrace = array();
        for($i=0; $i < count($trace); $i++) {
            $stacktrace[] = htmlspecialchars(sprintf("#%d %s%s%s called at [%s:%d]",
                $i,
                @$trace[$i]['class'],
                @$trace[$i]['type'],
                @$trace[$i]['function'],
                @$trace[$i]['file'],
                @$trace[$i]['line']
            ));
        }
        
        ob_start();
        if (ini_get("display_errors")) {
            printf("<br />\n<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br />\n", $errors, $errstr, $errfile, $errline);
            echo "Stack trace:<br />\n" . implode("<br />\n", $stacktrace) . "<br /><br />\n";
        }
        if (ini_get('log_errors')) {
            error_log(sprintf("PHP %s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline));
        }
        if($errno & (E_ERROR | E_USER_ERROR)) {
            if (!headers_sent()) {
                header('HTTP/1.0 500 Fatal Error');
                header('Content-Type:text/html');
            }
        }
        ob_end_flush();
        if($errno & (E_ERROR | E_USER_ERROR)) {
            echo str_repeat('    ', 100)."\n"; // IE won't display error pages < 512b
            exit(1);
        }
        return TRUE;
    }
    
    /**
     * @param Exception $e
     * @return void
     * @ignore
     */
    public function _exception_handler($e)
    {
        if (!headers_sent()) {
            header('HTTP/1.0 500 Uncaught Exception');
            header('Content-Type:text/html');
        }

        $line = $e->getFile();
        if ($e->getLine()) {
            $line .= ' line '.$e->getLine();
        }

        if (ini_get('display_errors')) {
            $title = '500 ' . get_class($e);
            $body = "<p><strong>\n ".htmlspecialchars($e->getMessage()).'</strong></p>' .
                    '<p>In '.htmlspecialchars($line)."</p><pre>\n".htmlspecialchars($e->getTraceAsString()).'</pre>';
        } else {
            $title = "500 Uncaught Exception";
            $body = "<p>The server encountered an uncaught exception and was unable to complete your request.</p>";
        }

        if (ini_get('log_errors')) {
            error_log($e->getMessage().' in '.$line);
        }
        
        if(ob_get_level() > 0 && ob_get_length() > 0) {
            $curbuf = ob_get_contents();
            ob_clean();
        }
        if(isset($curbuf) && preg_match('/<html/i', $curbuf)) {
            echo $curbuf;
            echo "<hr />";
            echo "<h1>" . $title . "</h1>\n" . $body . '</body></html>';
        }
        else {
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
            echo "<html><head>\n<title>".$title."</title>\n</head><body>\n";
            if(isset($curbuf)) {
                echo $curbuf;
                echo "<hr />";
            }
            echo "<h1>" . $title . "</h1>\n" . $body . '</body></html>';
        }
        echo str_repeat('    ', 100)."\n"; // IE won't display error pages < 512b
        exit(1);
    }
    
    /**
     * It reads and executes another PHP file with any local variables.
     * It can read already executed file.
     * @param string $script_abs_path must be absolute pathe for local file system.
     * @param array $localvars
     * @return bool
     */
    private function includeWithThis($script_abs_path, $localvars=array())
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
    
    /**
     * Execute an external script in isolated variable scope.
     * @param string $script Script filename absolute path or relative based on current script.
     * @return bool
     */
    public function subscript($script)
    {
        if($script[0] != '/') {
            $script = dirname($this->_script) . '/' . $script;
        }
        $prev_script = $this->_script;
        $this->_script = $script;
        try {
            $this->updateIncdir();
            $this->includeWithThis($this->_script, $this->_autolocal->toArray());
        }
        catch(Pinoco_FlowControlSkip $ex) {
        }
        catch(Pinoco_FlowControl $ex) {
            $this->_activity->push($this->_script);
            $this->_script = $prev_script;
            throw $ex;
        }
        $this->_activity->push($this->_script);
        $this->_script = $prev_script;
    }
    
    /**
     * Runs a hook script.
     * @param string $script
     * @param string $subpath
     * @return bool
     */
    private function _run_hook_if_exists($script, $subpath)
    {
        if(is_file($script)) {
            $this->_subpath = $subpath;
            try {
                $this->subscript($script);
            }
            catch(Pinoco_FlowControl $ex) {
                $this->_subpath = "";
                throw $ex;
            }
            $this->_subpath = "";
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    /**
     * Executes all process of Pinoco engine.
     * @param bool $output_buffering
     * @return void
     */
    public function run($output_buffering=TRUE)
    {
        // insert credit into X-Powered-By header
        $this->_credit_into_x_powerd_by();
        
        self::$_current_instance = $this;
        
        if(!(function_exists('xdebug_is_enabled') && xdebug_is_enabled())
            && PHP_SAPI !== 'cli')
        {
            $special_error_handler_enabled = true;
            set_error_handler(array($this, "_error_handler"));
            set_exception_handler(array($this, "_exception_handler"));
        }
        
        if($output_buffering) {
            ob_start();
        }
        
        $this->_system_incdir = ini_get('include_path');
        
        $this->_manually_rendered = false;
        try {
            $hookbase = $this->_sysdir . "/hooks";
            
            $uris = explode("/", ltrim($this->_path, "/"));
            $process = array();
            $proccessed = false;
            try {
                while(count($uris) > 0) {
                    $dpath = (count($process) == 0 ? "" : "/") . implode('/', $process);
                    
                    $fename_orig = $uris[0];
                    
                    // enter
                    $this->_run_hook_if_exists($hookbase . $dpath . "/_enter.php", implode('/', $uris));
                    
                    array_shift($uris);
                    
                    // For mod_rewrite users: direct access to gateway should be rejected.
                    if($this->_dispatcher == "") { // No dispatcher indicates to force to use mod_rewrite.
                        if(strpos($_SERVER['REQUEST_URI'], "/" . basename($_SERVER['SCRIPT_NAME'])) !== FALSE) {
                            $this->forbidden();
                        }
                    }
                    
                    // NOT IN USE NOW!
                    // preprocess notfound -- if handler or page is not exists
                    //if(!$this->_hook_or_page_exists()){
                    //    $this->notfound();
                    //}
                    
                    // invisible file entry name.
                    if(preg_match('/^_.*$/', $fename_orig)) {
                        $this->notfound();
                    }
                    
                    // default dir
                    if(count($uris) > 0) {
                        if(is_dir($hookbase . $dpath . '/' . $fename_orig)) {
                            $fename = $fename_orig;
                        }
                        else if(is_dir($hookbase . $dpath . '/_default')) {
                            $this->_pathargs->push($fename_orig);
                            $fename = '_default';
                        }
                        else {
                            $fename = $fename_orig;
                        }
                    }
                    else {
                        $fename = $fename_orig;
                    }
                    // resolve index file for directory access(the last element is empty like "/foo/").
                    if(count($uris) == 0 && $fename == "") {
                        foreach(explode(" ", $this->_directory_index) as $idx) {
                            if(
                                is_file($this->_basedir . $dpath . "/" . $idx) ||  //base
                                is_file($hookbase . $dpath . "/" . $idx . ".php")  //sys
                            ) {
                                $fename = $idx;
                                break;
                            }
                        }
                    }
                    array_push($process, $fename);
                    
                    // default script support for the last element(=file)
                    if(count($uris) == 0) {
                        if($fename == "") { // case: no index file found for dir access
                            foreach(explode(" ", $this->_directory_index) as $idx) {
                                $ext = pathinfo($idx, PATHINFO_EXTENSION);
                                if($ext && is_file($hookbase . $dpath . "/_default." . $ext . ".php")) {
                                    $fename = "_default." . $ext;
                                    break;
                                }
                            }
                            if($fename == "") {
                                $fename = "_default";
                            }
                            $this->_pathargs->push($fename_orig);
                        }
                        else if(!is_file($hookbase . $dpath . "/" . $fename . ".php")) {
                            $ext = pathinfo($fename, PATHINFO_EXTENSION);
                            if($ext && is_file($hookbase . $dpath . "/_default." . $ext . ".php")) {
                                $fename = "_default." . $ext;
                            }
                            else {
                                $fename = "_default";
                            }
                            $this->_pathargs->push($fename_orig);
                        }
                    }
                    
                    // main script
                    if(is_file($hookbase . $dpath . "/" . $fename . ".php")) {
                        $proccessed = true;
                        if($this->_run_hook_if_exists($hookbase . $dpath . "/" . $fename . ".php", implode('/', $uris))) {
                            break;
                        }
                    }
                }
                $dummy = 1; // NEVER REMOVE THIS LINE FOR eAccelerator's BUG!!
            }
            catch(Pinoco_FlowControlTerminate $ex) {
            }
            
            //render
            if(!$this->_manually_rendered && $this->_page) {
                
                if($this->_page != "<default>") {
                    $pagepath = $this->resolvePath($this->_page);
                    $page = $this->_page_from_path_with_directory_index($pagepath);
                }
                else {
                    $pagepath = $this->_path;
                    if($this->_page_modifier) {
                        $this->updateIncdir();
                        $pagepath = call_user_func($this->_page_modifier, $pagepath);
                    }
                    if($pagepath) {
                        $page = $this->_page_from_path_with_directory_index($pagepath);
                    }
                    else {
                        $page = FALSE;
                    }
                }
                
                if($page && is_file($this->_basedir . $page)) {
                    // non-html but existing => raw binary with mime-type header
                    if($this->isRenderablePath($this->_basedir . $page)) {
                        $this->render($page);
                    }
                    else {
                        header('Content-Type:' . $this->mimeType($this->_basedir . $page));
                        //$st = stat($this->_basedir . $page);
                        //header('Last-Modified:' . str_replace('+0000', 'GMT', gmdate("r", $st['mtime'])));
                        readfile($this->_basedir . $page);  // TODO: streaming
                    }
                }
                else if(!$proccessed) {
                    // no page and no tarminal hook indicates resource was not found or forbidden
                    if($this->_path[strlen($this->_path) - 1] == "/") {
                        $this->forbidden();
                    }
                    else {
                        $this->notfound();
                    }
                }
                else if($this->_page != "<default>") {
                    // page specified in hook-script but not found it
                    $this->error(500, "Internal Server Error", "File not found: " . $this->_page);
                }
            }
            $dummy = 1; // NEVER REMOVE THIS LINE FOR eAccelerator's BUG!!
        }
        catch(Pinoco_FlowControlHttpError $ex) { // contains Redirect
            $ex->respond($this);
        }
        
        // cleanup process
        do {
            $fename = array_pop($process);
            array_unshift($uris, $fename);
            $dpath = (count($process) == 0 ? "" : "/") . implode('/', $process);
            
            // leave (All flow control exceptions work as skip exception)
            try {
                $this->_run_hook_if_exists($hookbase . $dpath . "/_leave.php", implode('/', $uris));
                $dummy = 1; // NEVER REMOVE THIS LINE FOR eAccelerator's BUG!!
            }
            catch(Pinoco_FlowControl $ex) { }
            
        } while(count($process) > 0);
        
        ini_set('include_path', $this->_system_incdir);
        
        if($output_buffering) {
            ob_end_flush();
        }
        
        if(isset($special_error_handler_enabled)) {
            restore_exception_handler();
            restore_error_handler();
        }
        self::$_current_instance = null;
    }
}

