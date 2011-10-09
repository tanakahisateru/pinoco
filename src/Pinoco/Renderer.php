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

