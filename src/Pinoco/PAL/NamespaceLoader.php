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
 * @subpackage PAL
 */

if(!class_exists('PHPTAL')) { require_once 'PHPTAL.php'; }
if(!class_exists('PHPTAL_PreFilter')) { require_once 'PHPTAL/PreFilter.php'; }

/**
 * @package Pinoco
 * @subpackage PAL
 */
class Pinoco_PAL_NamespaceLoader extends PHPTAL_PreFilter
{
    public function filter($data) {
        require_once dirname(__FILE__) . '/Namespace.php';
        return $data;
    }
}

