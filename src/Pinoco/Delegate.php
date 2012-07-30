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
 * Delegate class base
 * @package Pinoco
 * @abstract
 */
class Pinoco_Delegate
{
    protected $__delegatee__;

    /**
     * Creates an object with delegation target. This method
     * should be called in constructor in inherited class.
     * <code>
     * public function __construct(..., $delegatee, ...)
     * {
     *     parent::__construct($delegatee);
     * }
     * </code>
     * @param object $delegatee;
     */
    public function __construct($delegatee=null)
    {
        if (is_null($delegatee)) {
            $delegatee = Pinoco::instance();
        }
        $this->__delegatee__ = $delegatee;
    }

    public function __get($name)
    {
        return $this->__delegatee__->$name;
    }

    public function __set($name, $value)
    {
        $this->__delegatee__->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->__delegatee__->$name);
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->__delegatee__, $name), $args);
    }
}

