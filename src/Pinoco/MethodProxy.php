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
 * Method proxy
 *
 * @package Pinoco
 * @internal
 */
class Pinoco_MethodProxy
{
    private $callable;
    private $owner;

    /**
     * Constructor to make an lazy value proxy.
     *
     * @param callback $callable
     * @param mixed $owner
     */
    public function __construct($callable, $owner)
    {
        // If closure, PHP 5.4 can bind $this with it.
        // if (is_object($this->callable) && method_exists($this->callable, 'bindTo')) {
        //     $callable = $this->callable->bindTo($this->owner);
        // }
        $this->callable = $callable;
        $this->owner = $owner;
    }

    /**
     * Evaluates itself by arguments and returns result.
     *
     * @param array $args
     * @return mixed
     */
    public function call($args)
    {
        array_unshift($args, $this->owner);
        return call_user_func_array($this->callable, $args);
    }

    /**
     * Closure like behavior (for PHP5.3 or greater)
     *
     * @return mixed
     */
    public function __invoke(/* $arguments */)
    {
        return $this->call(func_get_args());
    }
}

