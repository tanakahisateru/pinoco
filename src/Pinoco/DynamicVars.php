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
 * Dynamic vars model base
 * @package Pinoco
 * @abstract
 */
class Pinoco_DynamicVars extends Pinoco_Vars {
    
    /**
     * Returns a value or default by name.
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @see src/Pinoco/Pinoco_Vars#get($name)
     */
    public function get($name /*[, $default]*/)
    {
        if(method_exists($this, 'get_' . $name)) {
            return call_user_func(array($this, 'get_' . $name));
        }
        else {
            if(func_num_args() > 1) {
                $a1 = func_get_arg(1);
                return parent::get($name, $a1);
            }
            else {
                return parent::get($name);
            }
        }
    }
    
    /**
     * Checks if this object has certain property or not.
     * If setLoose is set true then it returns true always.
     * @param stirng $name
     * @return bool
     * @see src/Pinoco/Pinoco_Vars#has($name)
     */
    public function has($name)
    {
        return method_exists($this, 'get_' . $name) || parent::has($name);
    }
    
    /**
     * Returns all property names in this object.
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
        $ks = Pinoco_List::fromArray($ks);
        $ks->concat(parent::keys());
        return $ks;
    }
    
    /**
     * Propertry setter.
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
            $exclass = class_exists('RuntimeException') ? 'RuntimeException' : 'Exception';
            throw new $exclass("Cannot reassign to ". $name . ".");
        }
        else {
            parent::set($name, $value);
        }
    }
    
    public function getIterator()
    {
        // to include reserved special vars
        $arr = $this->toArray();
        return new Pinoco_Iterator($arr);
    }
}

