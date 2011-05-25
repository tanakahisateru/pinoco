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
 * @version  0.3.0
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

/**
 * Variable model
 * @package Pinoco
 */
class Pinoco_Vars implements IteratorAggregate, ArrayAccess {
    
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
    public function get($name)
    {
        if(array_key_exists($name, $this->_vars)) {
            return $this->_vars[$name];
        }
        else {
            return func_num_args() > 1 ? func_get_arg(1) : $this->_default_val;
        }
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
            $arr[sprintf($modifier, $k)] = $this->get($k, $default);
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
            $exclass = class_exists('InvalidArgumentException') ? 'InvalidArgumentException' : 'Exception';
            throw new $exclass("Can't import from scalar variable.");
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

/**
 * List model
 * @package Pinoco
 */
class Pinoco_List implements IteratorAggregate, ArrayAccess, Countable {
    
    private $_arr;
    private $_default_val;

    /**
     * Constructor to make an empty instance.
     */
    public function __construct()
    {
        $this->_arr = array();
        $this->_default_val = null;
    }
    
    /**
     * Makes a new object from Array.
     * @param mixed $src
     * @return Pinoco_List
     */
    public static function fromArray($src)
    {
        $self = new Pinoco_List();
        $self->concat($src);
        return $self;
    }
    
    /**
     * Wraps an existing Array.
     * @param array &$srcref
     * @return Pinoco_List
     */
    public static function wrap(&$srcref)
    {
        $self = new Pinoco_List();
        $self->_arr = $srcref;
        return $self;
    }
    
    /**
     * Appends a value to tail.
     * @param mixed $value,...
     * @return void
     */
    public function push($value)
    {
        $n = func_num_args();
        for($i = 0; $i < $n; $i++) {
            $a = func_get_arg($i);
            array_push($this->_arr, $a);
        }
    }
    
    /**
     * Removes and return a value from tail.
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->_arr);
    }
    
    /**
     * Inserts a value to head.
     * @param mixed $value,...
     * @return void
     */
    public function unshift($value)
    {
        $n = func_num_args();
        for($i = 0; $i < $n; $i++) {
            $a = func_get_arg($i);
            array_unshift($this->_arr, $a);
        }
    }
    
    /**
     * Removes and return a value from head.
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->_arr);
    }
    
    /**
     * Concatinates another iteratable object.
     * @param mixed $source
     * @return void
     */
    public function concat($source)
    {
        $n = func_num_args();
        for($i = 0; $i < $n; $i++) {
            $arg = func_get_arg($i);
            foreach($arg as $e) {
                array_push($this->_arr, $e);
            }
        }
    }
    
    /**
     * Sorts this list.
     * @param callback|false $callable
     * @return void
     */
    public function sort($callable=FALSE)
    {
        if($callable) {
            sort($this->_arr);
        }
        else {
            usort($this->_arr, $callable);
        }
    }
    
    /**
     * Returns a number of element of this list.
     * @return int
     */
    public function count()
    {
        return count($this->_arr);
    }
    
    /**
     * Converts this list to string.
     * @param string $sep
     * @return string
     */
    public function join($sep=",")
    {
        return implode($sep, $this->_arr);
    }
    
    /**
     * Returns a reversed list.
     * @return void
     */
    public function reverse()
    {
        return self::fromArray(array_reverse($this->_arr));
    }
    
    /**
     * Returns a slice.
     * @param int $offset
     * @param int $length
     * @return Pinoco_List
     */
    public function slice($offset) { // $length
        if(func_num_args() >= 2) {
            $a1 = func_get_arg(1);
            return self::fromArray(array_slice($this->_arr, $offset, $a1));
        }
        else {
            return self::fromArray(array_slice($this->_arr, $offset));
        }
    }
    
    /**
     * Removes elements by range and inserts another.
     * @param int $offset
     * @param int $length
     * @param array $replacement
     * @return Pinoco_List;
     */
    public function splice($offset, $length=0) { // $replacement
        if(func_num_args() >= 3) {
            $a2 = func_get_arg(2);
            return self::fromArray(array_splice($this->_arr, $offset, $length, $a2));
        }
        else {
            return self::fromArray(array_splice($this->_arr, $offset, $length));
        }
    }
    
    /**
     * Inserts another.
     * @param int $offset
     * @param mixed $value
     * @return void
     */
    public function insert($offset, $value)
    {
        $args = func_get_args();
        array_shift($args);
        array_splice($this->_arr, $offset, 0, $args);
    }
    
    /**
     * Removes by range.
     * @param int $offset
     * @param int $length
     * @return void
     */
    public function remove($offset, $length=1)
    {
        array_splice($this->_arr, $offset, $length);
    }
    
    /**
     * Returns the first position where value found in this list.
     * @param mixed $value
     * @return int
     */
    public function index($value)
    {
        $r = array_search($value, $this->_arr);
        return $r===FALSE ? -1 : $r;
    }
    
    /**
     * Returns value by position.
     * @param int $idx
     * @param mixed $default
     * @return unknown_type
     */
    public function get($idx)
    {
        if($idx < $this->count()) {
            return $this->_arr[$idx];
        }
        else {
            return func_num_args() > 1 ? func_get_arg(1) : $this->_default_val;
        }
    }
    
    /**
     * Stes value by position.
     * @param int $idx
     * @param mixed $value
     * @param mixed $default
     * @return void
     */
    public function set($idx, $value)
    {
        for($i = count($this->_arr); $i < $idx; $i++) {
            $this->_arr[$idx] = func_num_args() > 2 ? func_get_arg(2) : $this->_default_val; //default??
        }
        $this->_arr[$idx] = $value;
    }
    
    /**
     * Sets a default value for overflow access.
     * @param mixed $value
     * @return void
     */
    public function setDefault($value)
    {
        $this->_default_val = $value;
    }
    
    /**
     * Exports elements to Array.
     * @param array|null $modifier
     * @return array
     */
    public function toArray($modifier=null)
    {
        $arr = array();
        foreach($this->_arr as $i=>$v) {
            $arr[$modifier ? sprintf($modifier, $i) : $i] = $v;
        }
        return $arr;
    }
    
    /**
     * Fold operation for each elements.
     * @param callback $callable
     * @param mixed $initial
     * @return mixed
     */
    public function reduce($callable, $initial=null)
    {
        return array_reduce($this->_arr, $callable, $initial);
    }
    
    /**
     * Some operation for each elements.
     * @param callback $callable
     * @return void
     */
    public function each($callable)
    {
        foreach($this->_arr as $e){
            call_user_func($callable, $e);
        }
    }
    
    /**
     * Regenerates list from this list which elements are applied given fnction.
     * @param callback $callable
     * @return Pinoco_List
     */
    public function map($callable)
    {
        return self::fromArray(array_map($callable, $this->_arr));
    }
    
    /**
     * Regenerates list from this list which elements are filterd by given fnction.
     * @param callback $callable
     * @return Pinoco_List
     */
    public function filter($callable)
    {
        return self::fromArray(array_filter($this->_arr, $callable));
    }
    /*
    public function any($callable)
    {
    }
    public function all($callable)
    {
    }
    */
    
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    public function offsetExists($offset)
    {
        return $offset < count($this->_arr);
    }
    public function offsetUnset($offset)
    {
        array_splice($this->_arr, $offset, 1);
    }
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    
    public function getIterator()
    {
        return new Pinoco_Iterator($this->_arr);
    }
    
    public function __toString() { return __CLASS__; } // TODO: dump vars name/values
}

/**
 * Iterator for Pinoco Variables or List
 * @package Pinoco
 * @internal
 */
class Pinoco_Iterator implements Iterator {
    private $_ref;
    private $_cur;
    public function __construct(&$ref) { $this->_ref = $ref; $this->rewind(); }
    public function rewind()  { reset($this->_ref); $this->_cur = each($this->_ref); }
    public function current() { return $this->_cur[1]; }
    public function key()     { return $this->_cur[0]; }
    public function next()    { $this->_cur = each($this->_ref); }
    public function valid()   { return $this->_cur !== FALSE; }
}

/**
 */
class Pinoco_DynamicVars extends Pinoco_Vars {
    
    /**
     * Returns a value or default by name.
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @see src/Pinoco/Pinoco_Vars#get($name)
     */
    public function get($name)
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
    
    public function _x_call($name, $args) // diabled temporaly
    {
        if(!$this->has($name)) {
            return;
            // throw new BadMethodCallException();
        }
        $func = $this->get($name);
        if(!is_callable($func)) {
            return;
            // throw new BadMethodCallException();
        }
        
        array_unshift($args, $this);
        return call_user_func_array($func, $args);
    }
}
