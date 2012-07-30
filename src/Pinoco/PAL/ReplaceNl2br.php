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
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 * @subpackage PAL
 */

if (!class_exists('PHPTAL')) { require_once 'PHPTAL.php'; }
if (!class_exists('PHPTAL_Namespace')) { require_once 'PHPTAL/Namespace.php'; }
if (!class_exists('PHPTAL_Php_Attribute_TAL_Replace')) { require_once 'PHPTAL/Php/Attribute/TAL/Replace.php'; }

/**
 * @package Pinoco
 * @subpackage PAL
 */
class Pinoco_PAL_ReplaceNl2br extends PHPTAL_Php_Attribute_TAL_Replace
{
    protected function doEchoAttribute(PHPTAL_Php_CodeWriter $codewriter, $code)
    {
        if ($code !== "''") {
            if ($this->_echoType === self::ECHO_TEXT) {
                $codewriter->flush();
                $codewriter->pushCode('echo nl2br('.$codewriter->escapeCode($code).')');
            }
            else {
                $codewriter->pushCode('echo nl2br('.$this->stringifyCode($this->interpolateHTML($code)).')');
            }
        }
    }
}


