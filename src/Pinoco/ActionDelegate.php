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
 * @version  0.1.2
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

/**
 * Method Delegation to Pinoco
 * @package Pinoco
 */
class Pinoco_Delegate {
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
    
    public function __isset($name)
    {
        return $this->has($name);
    }
    
    public function __unset($name)
    {
        $this->remove($name);
    }
    
    function __call($name, $args)
    {
        if($name[0] != '_') {
            return call_user_func_array(array(Pinoco::instance(), $name), $args);
        }
        else {
            trigger_error("Function " . $name . "() is not defined.", E_USER_ERROR);
        }
    }
}

/**
 * Action dispatcher to controller class method
 * @package Pinoco
 */
class Pinoco_ActionDispatcher {
    private $_basedir;
    
    public function __construct($basedir)
    {
        $this->_basedir = rtrim($basedir, "/");
    }
    
    public function run($controller, $action)
    {
        Pinoco::instance()->using($this->_basedir . "/" . $controller . ".php");
        eval('$obj = new ' . $controller . '();');
        if($action == ""){ $action = "_empty"; }
        if(!method_exists($obj, $action)) { $action = "_default"; }
        call_user_func(array($obj, $action));
    }
}
