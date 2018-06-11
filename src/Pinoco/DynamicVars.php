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
 * Dynamic vars model base
 * @package Pinoco
 * @abstract
 */
class Pinoco_DynamicVars extends Pinoco_Vars
{
    /**
     * Returns a value or default by name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @see src/Pinoco/Pinoco_Vars#get($name)
     */
    public function get($name, $default = Pinoco_OptionalParam::UNSPECIFIED)
    {
        if (method_exists($this, 'get_' . $name)) {
            return call_user_func(array($this, 'get_' . $name));
        } else {
            if (Pinoco_OptionalParam::isSpecifiedBy($default)) {
                return parent::get($name, $default);
            } else {
                return parent::get($name);
            }
        }
    }

    /**
     * Checks if this object has certain property or not.
     * If setLoose is set true then it returns true always.
     *
     * @param string $name
     * @return bool
     * @see src/Pinoco/Pinoco_Vars#has($name)
     */
    public function has($name)
    {
        return method_exists($this, 'get_' . $name) || parent::has($name);
    }

    /**
     * Returns all property names in this object.
     *
     * @return Pinoco_List
     * @see src/Pinoco/Pinoco_Vars#keys()
     */
    public function keys()
    {
        $meths = get_class_methods($this);
        $ks = array();
        $m = array();
        foreach ($meths as $meth) {
            if (preg_match("/^get_(.*)$/", $meth, $m)) {
                array_push($ks, $m[1]);
            }
        }
        $ks = Pinoco_List::fromArray($ks);
        $ks->concat(parent::keys());
        return $ks;
    }

    /**
     * Property setter.
     *
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return void
     * @see src/Pinoco/Pinoco_Vars#set($name, $value)
     */
    public function set($name, $value)
    {
        if (method_exists($this, 'set_' . $name)) {
            call_user_func(array($this, 'set_' . $name), $value);
        } elseif (method_exists($this, 'get_' . $name)) {
            throw new InvalidArgumentException("Cannot reassign to ". $name . ".");
        } else {
            parent::set($name, $value);
        }
    }

    public function getIterator()
    {
        // to include reserved special vars
        $arr = $this->toArray();
        return new Pinoco_ArrayConvertiblesIterator($arr);
    }
}
