<?php
/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * PHP Version 5
 *
 * @category Pinoco
 * @package  Pinoco
 * @author   Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  0.1.0-beta1
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
     * 
     */
    public function __construct()
    {
        $this->_vars = array();
        $this->_default_val = null;
        $this->_loose = false;
    }
    
    /**
     * 
     * @param mixed $src
     * @return Pinoco_Vars
     */
    public static function from_array($src)
    {
        $self = new Pinoco_Vars();
        $self->import($src);
        return $self;
    }
    
    /**
     * 
     * @param array &$srcref
     * @return Pinoco_Vars
     */
    public static function wrap(&$srcref)
    {
        $self = new Pinoco_Vars();
        $self->_vars = $srcref;
        return $self;
    }    
    
    /**
     * 
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
     * 
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->_loose || array_key_exists($name, $this->_vars);
    }
    
    /**
     * 
     * @return Pinoco_List
     */
    public function keys()
    {
        return Pinoco_List::from_array(array_keys($this->_vars));
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
    }
    
    /**
     * 
     * @param mixed $value
     * @return void
     */
    public function setdefault($value)
    {
        $this->_default_val = $value;
    }
    
    /**
     * 
     * @param bool $flag
     * @return void
     */
    public function setloose($flag)
    {
        $this->_loose = $flag;
    }
    
    /**
     * 
     * @param string $name
     * @return void
     */
    public function remove($name)
    {
        unset($this->_vars[$name]);
    }
    
    /**
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
    
    /**
     * 
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
    
    /**
     * 
     * @param string $name
     * @return void
     */
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
     * 
     * @param array|false $filter
     * @param mixed $default
     * @param string $modifier
     * @return array
     */
    public function to_array($filter=false, $default=null, $modifier="%s")
    {
        $arr = array();
        $ks = $filter ? $filter : $this->keys();
        foreach($ks as $k) {
            $arr[sprintf($modifier, $k)] = $this->get($k, $default);
        }
        return $arr;
    }
    
    /**
     * 
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
            trigger_error("Cannot to import from scalar variable.", E_USER_NOTICE);
            return;
        }
        $ks = $filter ? $filter : array_keys($srcarr);
        foreach($ks as $k) {
            $name = (strpos($modifier, "%") != -1) ? sprintf($modifier, $k) : (
                is_callable($modifier) ? call_user_func($modifier, $k) : ($modifier . $k)
            );
            $this->set($name, array_key_exists($k, $srcarr) ? $srcarr[$k] : $default);
        }
    }
    
    /**
     * 
     * @return string
     */
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
     * 
     */
    public function __construct()
    {
        $this->_arr = array();
        $this->_default_val = null;
    }
    
    /**
     * 
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
     * 
     * @param mixed $src
     * @return Pinoco_List
     */
    public static function from_array($src)
    {
        $self = new Pinoco_List();
        $self->concat($src);
        return $self;
    }
    
    /**
     * 
     * @param mixed $value
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
     * 
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->_arr);
    }
    
    /**
     * 
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->_arr);
    }
    
    /**
     * 
     * @param $value
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
     * 
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
     * 
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
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_arr);
    }
    
    /**
     * 
     * @param string $sep
     * @return string
     */
    public function join($sep=",")
    {
        return implode($sep, $this->_arr);
    }
    
    /**
     * 
     * @return void
     */
    public function reverse()
    {
        return self::from_array(array_reverse($this->_arr));
    }
    
    /**
     * 
     * @param int $offset
     * @param int $length
     * @return Pinoco_List
     */
    public function slice($offset) { // $length
        if(func_num_args() >= 2) {
            return self::from_array(array_slice($this->_arr, $offset, func_get_arg(1)));
        }
        else {
            return self::from_array(array_slice($this->_arr, $offset));
        }
    }
    
    /**
     * 
     * @param int $offset
     * @param int $length
     * @param array $replacement
     * @return Pinoco_List;
     */
    public function splice($offset, $length=0) { // $replacement
        if(func_num_args() >= 3) {
            return self::from_array(array_splice($this->_arr, $offset, $length, func_get_arg(2)));
        }
        else {
            return self::from_array(array_splice($this->_arr, $offset, $length));
        }
    }
    
    /**
     * 
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
     * 
     * @param int $offset
     * @param int $length
     * @return void
     */
    public function remove($offset, $length=1)
    {
        array_splice($this->_arr, $offset, $length);
    }
    
    /**
     * 
     * @param mixed $value
     * @return int
     */
    public function index($value)
    {
        $r = array_search($value, $this->_arr);
        return $r===FALSE ? -1 : $r;
    }
    
    /**
     * 
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
     * 
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
     * 
     * @param mixed $value
     * @return void
     */
    public function setdefault($value)
    {
        $this->_default_val = $value;
    }
    
    /**
     * 
     * @param array|null $modifier
     * @return array
     */
    public function to_array($modifier=null)
    {
        $arr = array();
        foreach($this->_arr as $i=>$v) {
            $arr[$modifier ? sprintf($modifier, $i) : $i] = $v;
        }
        return $arr;
    }
    
    /**
     * 
     * @param callback $callable
     * @param mixed $initial
     * @return mixed
     */
    public function reduce($callable, $initial=null)
    {
        return array_reduce($this->_arr, $callable, $initial);
    }
    
    /**
     * 
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
     * 
     * @param callback $callable
     * @return Pinoco_List
     */
    public function map($callable)
    {
        return self::from_array(array_map($callable, $this->_arr));
    }
    
    /**
     * 
     * @param callback $callable
     * @return Pinoco_List
     */
    public function filter($callable)
    {
        return self::from_array(array_filter($this->_arr, $callable));
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
