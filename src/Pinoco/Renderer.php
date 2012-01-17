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
 * Abstract HTML page renderer
 * @package Pinoco
 * @property Pinoco_Vars $cfg
 * @property callback $before_rendering
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
     * @var callback
     */
    protected $_before_rendering;
    
    /**
     * Constructor
     * @param Pinoco $sys
     */
    public function __construct(&$sys)
    {
        $this->_sysref = &$sys;
        $this->_cfg = new Pinoco_Vars();
        $this->_before_rendering = null;
    }
    
    public function __toString() { return __CLASS__; }
    
    /**
     * Properties reader.
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if($name == 'cfg'){ return $this->_cfg; }
        if($name == 'before_rendering'){ return $this->_before_rendering; }
        return NULL;
    }
    
    /**
     * Properties writer.
     * This protects read only property "cfg".
     * @param string $name
     * @return mixed
     */
    public function __set($name, $value)
    {
        if($name == 'before_rendering'){ $this->_before_rendering = $value; }
    }
    
    /**
     * HTML page renderring implementation.
     * @param string $page
     * @param array $extravars
     * @return void
     */
     protected function render($page, $extravars=array()){
         // implement rendering process
     }
     
    /**
     * Executes rendering with calling before_rendering handler.
     * @param string $page
     * @param array $extravars
     * @return void
     */
     public function prepareAndRender($page, $extravars=array()){
         if($this->_before_rendering) {
             call_user_func($this->_before_rendering, $this);
         }
         $this->render($page, $extravars);
     }
}

