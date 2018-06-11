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
 * List model
 *
 * @package Pinoco
 */
class Pinoco_List implements IteratorAggregate, ArrayAccess, Countable, Pinoco_ArrayConvertible
{
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
     *
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
     *
     * @param array $srcref
     * @throws InvalidArgumentException
     * @return Pinoco_List
     */
    public static function wrap(&$srcref)
    {
        if (!is_array($srcref)) {
            throw new InvalidArgumentException("Non array variable was given.");
        }
        $self = new Pinoco_List();
        $self->_arr = &$srcref;
        return $self;
    }

    /**
     * Appends a value to tail.
     *
     * @param mixed $value,...
     * @return void
     */
    public function push($value)
    {
        $args = func_get_args();
        foreach ($args as $a) {
            array_push($this->_arr, $a);
        }
    }

    /**
     * Removes and return a value from tail.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->_arr);
    }

    /**
     * Inserts a value to head.
     *
     * @param mixed $value,...
     * @return void
     */
    public function unshift($value)
    {
        $args = func_get_args();
        foreach ($args as $a) {
            array_unshift($this->_arr, $a);
        }
    }

    /**
     * Removes and return a value from head.
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->_arr);
    }

    /**
     * Concatenates another iterative object.
     *
     * @param mixed $source,...
     * @return void
     */
    public function concat($source)
    {
        $args = func_get_args();
        foreach ($args as $a) {
            foreach ($a as $e) {
                array_push($this->_arr, $e);
            }
        }
    }

    /**
     * Sorts this list.
     *
     * @param callable $callable
     * @return void
     */
    public function sort($callable = null)
    {
        if (!$callable) {
            sort($this->_arr);
        } else {
            usort($this->_arr, $callable);
        }
    }

    /**
     * Returns sorted list.
     *
     * @param callable $callable
     * @return Pinoco_List
     */
    public function sorted($callable = null)
    {
        $tmp = clone($this);
        $tmp->sort($callable);
        return $tmp;
    }

    /**
     * Returns a number of element of this list.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_arr);
    }

    /**
     * Converts this list to string.
     *
     * @param string $sep
     * @return string
     */
    public function join($sep = ",")
    {
        return implode($sep, $this->_arr);
    }

    /**
     * Returns a reversed list.
     *
     * @return Pinoco_List
     */
    public function reverse()
    {
        return self::fromArray(array_reverse($this->_arr));
    }

    /**
     * Returns a slice.
     *
     * @param int $offset
     * @param int $length
     * @return Pinoco_List
     */
    public function slice($offset, $length = null)
    {
        if (!is_null($length)) {
            return self::fromArray(array_slice($this->_arr, $offset, $length));
        } else {
            return self::fromArray(array_slice($this->_arr, $offset));
        }
    }

    /**
     * Removes elements by range and inserts another.
     *
     * @param int $offset
     * @param int $length
     * @param array $replacement
     * @return Pinoco_List;
     */
    public function splice($offset, $length = null, $replacement = null)
    {
        return self::fromArray(array_splice($this->_arr, $offset, $length, $replacement));
    }

    /**
     * Inserts another.
     *
     * @param int $offset
     * @param mixed $value,...
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
     *
     * @param int $offset
     * @param int $length
     * @return void
     */
    public function remove($offset, $length = 1)
    {
        array_splice($this->_arr, $offset, $length);
    }

    /**
     * Returns the first position where value found in this list.
     *
     * @param mixed $value
     * @return int
     */
    public function index($value)
    {
        $r = array_search($value, $this->_arr);
        return $r===false ? -1 : $r;
    }

    /**
     * Returns value or default by position.
     *
     * @param int $idx
     * @param mixed $default
     * @return mixed
     */
    public function get($idx, $default = Pinoco_OptionalParam::UNSPECIFIED)
    {
        if (isset($this->_arr[$idx])) {
            return $this->_arr[$idx];
        } else {
            return Pinoco_OptionalParam::isSpecifiedBy($default) ? $default : $this->_default_val;
        }
    }

    /**
     * Returns a value or default by tree expression.
     *
     * @param string $expression
     * @param mixed $default
     * @return mixed
     */
    public function rget($expression, $default = Pinoco_OptionalParam::UNSPECIFIED)
    {
        $default = Pinoco_OptionalParam::isSpecifiedBy($default) ? $default : $this->_default_val;
        $es = explode('/', $expression);
        $v = $this;
        while (count($es) > 0) {
            $name = trim(array_shift($es));
            if ($name === "") {
                continue;
            }
            if ($v instanceof Pinoco_ArrayConvertible) {
                $v = $v->get($name, $default);
            } elseif (is_object($v)) {
                if (property_exists($v, $name)) {
                    $v = $v->$name;
                } else {
                    return $default;
                }
            } elseif (is_array($v)) {
                if (array_key_exists($name, $v)) {
                    $v = $v[$name];
                } else {
                    return $default;
                }
            } else {
                return $default;
            }
        }
        return $v;
    }

    /**
     * Sets value by position.
     *
     * @param int $idx
     * @param mixed $value
     * @param mixed $default
     * @return void
     */
    public function set($idx, $value, $default = Pinoco_OptionalParam::UNSPECIFIED)
    {
        $default = Pinoco_OptionalParam::isSpecifiedBy($default) ? $default : $this->_default_val;
        for ($i = count($this->_arr); $i < $idx; $i++) {
            $this->_arr[$i] = $default;
        }
        $this->_arr[$idx] = $value;
    }

    /**
     * Sets a default value for overflow access.
     *
     * @param mixed $value
     * @return void
     */
    public function setDefault($value)
    {
        $this->_default_val = $value;
    }

    /**
     * Exports elements to Array.
     *
     * @param string|callable $modifier
     * @return array
     */
    public function toArray($modifier = null)
    {
        $arr = array();
        if ($modifier) {
            foreach ($this->_arr as $i => $v) {
                $name = (strpos($modifier, "%") !== false) ? sprintf($modifier, $i) : (
                    is_callable($modifier) ? call_user_func($modifier, $i) : ($modifier . $i)
                );
                $arr[$name] = $v;
            }
        } else {
            foreach ($this->_arr as $i => $v) {
                $arr[$i] = $v;
            }
        }
        return $arr;
    }

    /**
     * Exports properties to Array recursively.
     *
     * @param int $depth
     * @return array|Pinoco_List
     */
    public function toArrayRecurse($depth = null)
    {
        if ($depth !== null && $depth == 0) {
            return $this;
        }
        $arr = array();
        foreach ($this->_arr as $i => $v) {
            if ($v instanceof Pinoco_ArrayConvertible) {
                $v = $v->toArrayRecurse($depth !== null ? $depth - 1 : null);
            }
            $arr[$i] = $v;
        }
        return $arr;
    }

    /**
     * Fold operation for each elements.
     *
     * @param callable $callable
     * @param mixed $initial
     * @return mixed
     */
    public function reduce($callable, $initial = null)
    {
        return array_reduce($this->_arr, $callable, $initial);
    }

    /**
     * Some operation for each elements.
     *
     * @param callable $callable
     * @return void
     */
    public function each($callable)
    {
        foreach ($this->_arr as $e) {
            call_user_func($callable, $e);
        }
    }

    /**
     * Regenerates list from this list which elements are applied given function.
     *
     * @param callable $callable
     * @return Pinoco_List
     */
    public function map($callable)
    {
        return self::fromArray(array_map($callable, $this->_arr));
    }

    /**
     * Regenerates list from this list which elements are filtered by given function.
     *
     * @param callable $callable
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
        return new Pinoco_ArrayConvertiblesIterator($this->_arr);
    }

    public function __toString()
    {
        return __CLASS__;
    } // TODO: dump vars name/values
}
