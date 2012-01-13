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

if(!function_exists('__pinoco_autoload_impl')) {
    /**
     * SPL autoload implementation for Pinoco libs
     * @param string $class
     * @return void
     */
    function __pinoco_autoload_impl($class)
    {
        if($class === 'Pinoco') {
            require_once dirname(dirname(__FILE__)) . '/Pinoco.php';
        }
        elseif(substr($class, 0, 7) === 'Pinoco_') {
            require_once dirname(__FILE__) . '/' . strtr(substr($class, 7), "_", "/") . '.php';
        }
    }
    if(!@in_array('__pinoco_autoload_impl', spl_autoload_functions())) {
        spl_autoload_register('__pinoco_autoload_impl');
    }
}

