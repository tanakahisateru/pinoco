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
 * Array wrapper interface
 *
 * @package Pinoco
 */
interface Pinoco_ArrayConvertible
{
    /**
     * Makes a new object from Array.
     *
     * @param array $src
     * @return Pinoco_ArrayConvertible
     */
    public static function fromArray($src);

    /**
     * Wraps an existing Array.
     *
     * @param array &$srcref
     * @return Pinoco_ArrayConvertible
     */
    public static function wrap(&$srcref);

    /**
     * Exports elements to Array.
     *
     * @param array|null $modifier
     * @return array
     */
    public function toArray($modifier=null);

    /**
     * Exports properties to Array recursively.
     *
     * @param int|bool $depth
     * @return array
     */
    public function toArrayRecurse($depth=false);

    /**
     * Returns value or default by key.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default=Pinoco_OptionalParam::UNSPECIFIED);

    /**
     * Returns a value or default by tree expression.
     *
     * @param string $expression
     * @param mixed $default
     * @return mixed
     */
    public function rget($expression, $default=Pinoco_OptionalParam::UNSPECIFIED);
}