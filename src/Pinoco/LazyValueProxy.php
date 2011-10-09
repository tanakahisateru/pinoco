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
 * Lazy value proxy
 * @package Pinoco
 * @internal
 */
class Pinoco_LazyValueProxy {
    
    private $callback;
    private $context;
    private $oneshot;
    private $freeze;
    private $value;
    
    /**
     * Constructor to make an lazy value proxy.
     *
     * @param callable $callback
     * @param boolean $oneshot
     * @param array $context
     */
    public function __construct($callback, $oneshot=false, $context=array())
    {
        if(is_callable($callback)) {
            $this->callback = $callback;
            $this->oneshot = $oneshot;
            $this->context = !empty($context) ? $context : array();
            $this->freeze = false;
            $this->value = null;
        }
        else {
            $this->freeze = true;
            $this->value = $callback;
        }
    }
    
    /**
     * Evalute real value.
     *
     * @param mixed $ovner
     * @return mixed
     */
    public function fetch($owner=null)
    {
        if($this->oneshot && $this->freeze) {
            return $this->value;
        }
        $args = $this->context;
        array_unshift($args, $owner);
        $result = call_user_func_array($this->callback, $args);
        if($result instanceof Pinoco_LazyValueProxy) {
            $result = $result->fetch($owner);
        }
        if($this->oneshot) {
            $this->freeze = true;
            $this->value = $result;
        }
        return $result;
    }
    
    /**
     * Mark it as dirty.
     *
     * @return void
     */
    public function dirty()
    {
        $this->freeze = false;
    }
}

