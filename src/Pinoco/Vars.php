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
 * @link     
 */

/**
 * Variable model
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
        return new Pinoco_VarsIterator($this);
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
 * Iterator for Pinoco Variables
 * @internal
 */
class Pinoco_VarsIterator implements Iterator {
    private $_ref, $_ks;
    public function __construct(&$ref) { $this->_ref = $ref; $this->rewind(); }
    public function rewind()  {  $this->_ks = $this->_ref->keys(); }
    public function current() { return $this->_ref->get($this->_ks[0]); }
    public function key()     { return $this->_ks[0]; }
    public function next()    { $this->_ks->shift(); }
    public function valid()   { return $this->_ks->count() > 0; }
}
