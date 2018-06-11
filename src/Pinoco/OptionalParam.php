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
 * Optional parameter placeholder.
 *
 * @package Pinoco
 */
class Pinoco_OptionalParam
{
    const UNSPECIFIED =
        'c62c4c3f7fa57c7acfcc93073527c490-OptionalParameterUnspecified-d5210620220db619214dd7421301cbf7';

    public static function trim($params)
    {
        $params = array_reverse($params);
        while (!empty($params) && $params[0] instanceof self) {
            array_shift($params);
        }
        return array_reverse($params);
    }

    public static function isSpecifiedBy($value)
    {
        return $value !== self::UNSPECIFIED;
    }
}
