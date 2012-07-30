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
 * Value proxy
 * @package Pinoco
 * @internal
 */
class Pinoco_ValueProxy
{
    private $callback;
    private $owner;
    private $context;
    private $oneshot;
    private $freeze;
    private $value;

    /**
     * Constructor to make a value proxy.
     *
     * @param callable $callback
     * @param mixed $owner
     * @param boolean $oneshot
     * @param array $context
     */
    public function __construct($callback, $owner, $oneshot=false, $context=array())
    {
        $this->callback = $callback;
        $this->owner = $owner;
        $this->oneshot = $oneshot;
        $this->context = !empty($context) ? $context : array();
        $this->freeze = false;
        $this->value = null;
    }

    /**
     * Evalute real value.
     *
     * @return mixed
     */
    public function fetch()
    {
        if ($this->oneshot && $this->freeze) {
            return $this->value;
        }
        $args = $this->context;
        array_unshift($args, $this->owner);
        $result = call_user_func_array($this->callback, $args);
        if ($this->oneshot) {
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

