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
 * Variable model
 * @package Pinoco
 */
class Pinoco_Vars implements IteratorAggregate, ArrayAccess, Countable {
    
    private $_vars;
    private $_default_val;
    private $_loose;
    
    /**
     * Constructor to make an empty instance.
     */
    public function __construct()
    {
        $this->_vars = array();
        $this->_default_val = null;
        $this->_loose = false;
    }
    
    /**
     * Makes a new object from Array.
     * @param mixed $src
     * @return Pinoco_Vars
     */
    public static function fromArray($src)
    {
        $self = new Pinoco_Vars();
        $self->import($src);
        return $self;
    }
    
    /**
     * Wraps an existing Array.
     * @param array &$srcref
     * @return Pinoco_Vars
     */
    public static function wrap(&$srcref)
    {
        if(!is_array($srcref)) {
            throw new InvalidArgumentException("Non array variable was given.");
        }
        $self = new Pinoco_Vars();
        $self->_vars = &$srcref;
        return $self;
    }
    
    /**
     * Returns a value or default by name.
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name /*[, $default]*/)
    {
        if(array_key_exists($name, $this->_vars)) {
            $r = $this->_vars[$name];
            if($r instanceof Pinoco_ValueProxy) {
                $r = $r->fetch();
            }
            return $r;
        }
        else {
            return func_num_args() > 1 ? func_get_arg(1) : $this->_default_val;
        }
    }
    
    /**
     * Returns a value or default by tree expression.
     * @param string $expression
     * @param mixed $default
     * @return mixed
     */
    public function rget($expression /*[, $default]*/)
    {
        $default = func_num_args() > 1 ? func_get_arg(1) : $this->_default_val;
        $es = explode('/', $expression);
        $v = $this;
        while(count($es) > 0) {
            $name = trim(array_shift($es));
            if($name === "") {
                continue;
            }
            if($v instanceof Pinoco_Vars || $v instanceof Pinoco_List) {
                $v = $v->get($name, $default);
            }
            elseif(is_object($v)) {
                if(property_exists($v, $name)) {
                    $v = $v->$name;
                }
                else {
                    return $default;
                }
            }
            elseif(is_array($v)) {
                if(array_key_exists($name, $v)) {
                    $v = $v[$name];
                }
                else {
                    return $default;
                }
            }
            else {
                return $default;
            }
        }
        return $v;
    }
    
    /**
     * Checks if this object has certain property or not.
     * If setloose is set true then it returns true always.
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->_loose || array_key_exists($name, $this->_vars);
    }
    
    /**
     * Returns all property names in this object.
     * @return Pinoco_List
     */
    public function keys()
    {
        return Pinoco_List::fromArray(array_keys($this->_vars));
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Sets a value to this object as given name.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
    }
    
    /**
     * Extends existing instance with any callable object.
     * The instance would be passed to the 1st argument of callback, then
     * trailings are filled as is just like Python's OOP.
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function registerAsMethod($name, $callback)
    {
        $this->_vars[$name] = new Pinoco_MethodProxy($callback, $this);
    }
    
    /**
     * Sets a value to this object as given named dynamic value.
     * The callback evaluted every time when fetched.
     * @param string $name
     * @param callable $callback
     * @param array $context
     * @return void
     */
    public function registerAsDynamic($name, $callback, $context=array())
    {
        $this->_vars[$name] = new Pinoco_ValueProxy($callback, $this, false, $context);
    }
    
    /**
     * Sets a value to this object as given named lazy value.
     * The callback evaluted as oneshot.
     * @param string $name
     * @param callable $callback
     * @param array $context
     * @return void
     */
    public function registerAsLazy($name, $callback, $context=array())
    {
        $this->_vars[$name] = new Pinoco_ValueProxy($callback, $this, true, $context);
    }
    
    /**
     * Clear lazy property's internal cache.
     * It would be regenerated at the next fetching.
     * @param string $name
     * @return void
     */
    public function markAsDirty($name)
    {
        if(array_key_exists($name, $this->_vars) &&
            $this->_vars[$name] instanceof Pinoco_ValueProxy
        ) {
            $this->_vars[$name]->dirty();
        }
    }
    
    /**
     * Sets a default value for non existence property access.
     * @param mixed $value
     * @return void
     */
    public function setDefault($value)
    {
        $this->_default_val = $value;
    }
    
    /**
     * Makes has() result always true.
     * @param bool $flag
     * @return void
     */
    public function setLoose($flag)
    {
        $this->_loose = $flag;
    }
    
    /**
     * Removes a property by name.
     * @param string $name
     * @return void
     */
    public function remove($name)
    {
        unset($this->_vars[$name]);
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
    
    public function __call($name, $arguments)
    {
        if(array_key_exists($name, $this->_vars)) {
            $m = $this->_vars[$name];
            if($m instanceof Pinoco_MethodProxy) {
                return $m->call($arguments);
            }
            elseif(is_callable($m)) {
                return call_user_func_array($m, $arguments);
            }
        }
        throw new BadMethodCallException("The Vars object has no such method: $name.");
    }
    
    /**
     * Returns a number of entries in this object.
     * @return int
     */
    public function count()
    {
        return count($this->_vars);
    }
    
    public function getIterator()
    {
        return new Pinoco_Iterator($this->_vars);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }
    
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    
    /**
     * Exports properties to Array.
     * @param array|false $filter
     * @param mixed $default
     * @param string $modifier
     * @return array
     */
    public function toArray($filter=false, $default=null, $modifier="%s")
    {
        $arr = array();
        $ks = $filter ? $filter : $this->keys();
        foreach($ks as $k) {
            $name = (strpos($modifier, "%") !== FALSE) ? sprintf($modifier, $k) : (
                is_callable($modifier) ? call_user_func($modifier, $k) : ($modifier . $k)
            );
            $arr[$name] = $this->get($k, $default);
        }
        return $arr;
    }
    
    /**
     * Exports properties to Array recursively.
     * @param int $depth
     * @return array
     */
    public function toArrayRecurse($depth=false)
    {
        if($depth !== false && $depth == 0) { return $this; }
        $arr = array();
        foreach($this->keys() as $k) {
            $v = $this->get($k);
            if($v instanceof Pinoco_Vars || $v instanceof Pinoco_List) {
                $v = $v->toArrayRecurse($depth !== false ? $depth - 1 : false);
            }
            $arr[$k] = $v;
        }
        return $arr;
    }
    
    /**
     * Imports properties from an array, object or another Vars
     * @param mixed $src
     * @param array|false $filter
     * @param mixed $default
     * @param string $modifier
     * @return void
     */
    public function import($src, $filter=false, $default=null, $modifier="%s")
    {
        if(is_array($src)){
            $srcarr = $src;
        }
        else if($src instanceof Traversable) {
            $srcarr = array();
            foreach($src as $k=>$v) {
                $srcarr[$k] = $v;
            }
        }
        else if(is_object($src)){
            $srcarr = get_object_vars($src);
        }
        else {
            throw new InvalidArgumentException("Can't import from scalar variable.");
        }
        $ks = $filter ? $filter : array_keys($srcarr);
        foreach($ks as $k) {
            $name = (strpos($modifier, "%") !== FALSE) ? sprintf($modifier, $k) : (
                is_callable($modifier) ? call_user_func($modifier, $k) : ($modifier . $k)
            );
            $this->set($name, array_key_exists($k, $srcarr) ? $srcarr[$k] : $default);
        }
    }
    
    public function __toString() { return __CLASS__; } // TODO: dump vars name/values
}

