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
 * Special Variable model as Nothing object.
 *
 * @package Pinoco
 */
class Pinoco_NothingVars extends Pinoco_Vars
{
    private static $_instance = null;

    /**
     * Provides the unique Nothing instance.
     *
     * @return Pinoco_NothingVars
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Returns itself as globally unique NothingVars.
     *
     * @param string $name
     * @param mixed $default
     * @return Pinoco_NothingVars
     */
    public function get($name, $default=Pinoco_OptionalParam::UNSPECIFIED)
    {
        return $this;
    }

    /**
     * Nothing can respond to any names.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return true;
    }

    /**
     * Every values passed to Nothing would be lost.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
    }

    /**
     * Every values passed to Nothing would be lost.
     *
     * @param string $name
     * @param callback $callable
     * @return void
     */
    public function registerAsMethod($name, $callable)
    {
        //$this->set[$name] = new Pinoco_MethodProxy($callable, $this);
    }

    /**
     * Every values passed to Nothing would be lost.
     *
     * @param string $name
     * @param callback $callable
     * @param array $context
     * @return void
     */
    public function registerAsDynamic($name, $callable, $context=array())
    {
    }

    /**
     * Every values passed to Nothing would be lost.
     *
     * @param string $name
     * @param callback $callable
     * @param array $context
     * @return void
     */
    public function registerAsLazy($name, $callable, $context=array())
    {
    }

    /**
     * Every values passed to Nothing would be lost.
     *
     * @param mixed $src
     * @param bool $filter
     * @param null $default
     * @param string $modifier
     * @return void
     */
    public function import($src, $filter=false, $default=null, $modifier="%s")
    {
    }

    /**
     * Nothing as Array is empty array.
     *
     * @param bool $filter
     * @param null $default
     * @param string $modifier
     * @return array
     */
    public function toArray($filter=false, $default=null, $modifier="%s")
    {
        return array();
    }

    /**
     * Nothing as Array is empty array.
     *
     * @param bool $depth
     * @return array
     */
    public function toArrayRecurse($depth=false)
    {
        return array();
    }

    /**
     * Nothing as String is empty string.
     *
     * @return string
     */
    public function __toString()
    {
        return "";
    }
}
