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
 * @package Pinoco
 * @internal
 */
class Pinoco_MethodProxy
{
    private $callback;
    private $owner;

    /**
     * Constructor to make an lazy value proxy.
     *
     * @param callable $callback
     * @param mixed $owner
     */
    public function __construct($callback, $owner)
    {
        // If closure, PHP 5.4 can bind $this with it.
        // if (is_object($this->callback) && method_exists($this->callback, 'bindTo')) {
        //     $callback = $this->callback->bindTo($this->owner);
        // }
        $this->callback = $callback;
        $this->owner = $owner;
    }

    /**
     * Evalute return value.
     *
     * @param array $args
     * @return mixed
     */
    public function call($args)
    {
        array_unshift($args, $this->owner);
        return call_user_func_array($this->callback, $args);
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

