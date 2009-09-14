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
 * @version  0.1.0
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

/**
 * Abstract HTML page renderer
 * @package Pinoco
 * @property Pinoco_Vars $cfg
 */
abstract class Pinoco_Renderer {
    /**
     * @var Pinoco
     */
    protected $_sysref;
    
    /**
     * @var Pinoco_Vars
     */
    protected $_cfg;
    
    /**
     * Constructor
     * @param Pinoco $sys
     */
    public function __construct(&$sys)
    {
        $this->_sysref = &$sys;
        $this->_cfg = new Pinoco_Vars();
    }
    
    public function __toString() { return __CLASS__; }
    
    /**
     * Protects read only property "cfg".
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if($name == 'cfg'){ return $this->_cfg; }
        return NULL;
    }
    
    /**
     * HTML page renderring implementation.
     * @param string $page
     * @param array $extravars
     * @return void
     */
     public function render($page, $extravars=array()){}
}

/**
 * Preferred HTML page renderer using PHPTAL
 * @package Pinoco
 */
class Pinoco_TALRenderer extends Pinoco_Renderer {
    
    /**
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars=array())
    {
        if(!$this->_sysref->using('PHPTAL.php')){
            trigger_error("PHPTAL is not installed.");
            return;
        }
        
        $template = new PHPTAL($page);
        
        //config
        $template->setTemplateRepository($this->_sysref->basedir);
        foreach($this->cfg as $k => $v) {
            $meth = 'set' . strtoupper($k[0]) . substr($k, 1);
            if(method_exists($template, $meth)) {
                call_user_func(array($template, $meth), $v);
            }
        }
        
        //extra TALES definition
        if(!function_exists("phptal_tales_url")) {
            function phptal_tales_url($src, $nothrow)
            {
                $src = trim($src);
                $src = preg_match('/^[A-Za-z0-9_]+:/', $src) ? phptal_tales($src, $nothrow) : "'" . $src . "'";
                return '$ctx->this->url(' . $src . ')';
            }
        }
        
        //extract vars
        foreach($this->_sysref->autolocal as $name=>$value) {
            $template->set($name, $value);
        }
        foreach($extravars as $name=>$value) {
            $template->set($name, $value);
        }
        $template->set('this', $this->_sysref);
        
        //exec
        //ob_start();
        try {
            echo $template->execute();
        }
        catch (Exception $e) {
            //$dump = ob_get_clean();
            if(!$e->getFile()) {
                $msg = $e->getMessage();
            }
            if(!$e->getLine()) {
                $msg = sprintf("%s(%s)", $e->getMessage(), $e->getFile());
            }
            else {
                $msg = sprintf("%s(%s:%d)", $e->getMessage(), $e->getFile(), $e->getLine());
            }
            trigger_error($msg, E_USER_WARNING);
            //throw $e;
            //return;
        }
        //ob_end_flush();
    }
}

/**
 * Default HTML page renderer using native PHP.
 * @package Pinoco
 */
class Pinoco_NativeRenderer extends Pinoco_Renderer {
    
    /**
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars=array())
    {
        $vars = $this->_sysref->autolocal->to_array();
        foreach($extravars as $k=>$v) {
            $vars[$k] = $v;
        }
        $orig_dir  = getcwd();
        chdir($this->_sysref->parent_path($this->_sysref->basedir . "/" . $page));
        $this->_sysref->include_with_this($this->_sysref->basedir . "/" . $page, $vars);
        chdir($orig_dir);
    }
}

/**
 * Null renderer.
 * @package Pinoco
 */
class Pinoco_NullRenderer extends Pinoco_Renderer {
    /**
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars=array())
    {
    }
}

