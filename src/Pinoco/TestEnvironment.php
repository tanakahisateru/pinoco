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
 * Testable Pinoco instance factory for unit test framework.
 * @package Pinoco
 */
class Pinoco_TestEnvironment extends Pinoco_Vars
{
    private $_basedir;
    private $_sysdir;
    private $_baseuri;
    private $_dispatcher;

    private $_preprocess;

    /**
     * Initialize testable Pinoco factory mainly by base directory and app directory.
     * @param string $sysdir
     * @param string $basedir
     * @param string $baseuri
     * @param string $dispatcher
     */
    public function __construct($basedir, $sysdir, $baseuri="/", $dispatcher="")
    {
        $this->_basedir = $basedir;
        $this->_sysdir = $sysdir;
        $this->_baseuri = $baseuri;
        $this->_dispatcher = $dispatcher;
        $this->_preprocess = false;
    }

    /**
     * Use this to define Pinoco's instance initialize process.
     * @param callback $callable
     * @param mixed $context
     * @return Pinoco_TestEnvironment
     */
    public function initBy($callable, $context=null)
    {
        $this->_preprocess = array($callable, $context);
        return $this;
    }

    /**
     * Provides an initialized Pinoco instance.
     * @param string $path
     * @return Pinoco
     */
    public function create($path)
    {
        $pinoco = new Pinoco(
            $this->_baseuri, $this->_dispatcher, $path,
            $this->_basedir, $this->_sysdir, true
        );
        if ($this->_preprocess) {
            call_user_func($this->_preprocess[0], $pinoco, $this->_preprocess[1]);
        }
        return $pinoco;
    }
}

