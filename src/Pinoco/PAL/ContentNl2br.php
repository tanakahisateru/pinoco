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
if(!class_exists('PHPTAL_Namespace')) { require_once 'PHPTAL/Namespace.php'; }
if(!class_exists('PHPTAL_Php_Attribute_TAL_Content')) { require_once 'PHPTAL/Php/Attribute/TAL/Content.php'; }

/**
 * @package Pinoco
 * @subpackage PAL
 */
class Pinoco_PAL_ContentNl2br extends PHPTAL_Php_Attribute_TAL_Content
{
    protected function doEchoAttribute(PHPTAL_Php_CodeWriter $codewriter, $code)
    {
        if ($code !== "''") {
            if ($this->_echoType === self::ECHO_TEXT) {
                $codewriter->flush();
                $codewriter->pushCode('echo nl2br('.$codewriter->escapeCode($code).')');
            }
            else {
                $codewriter->pushCode('echo nl2br('.$codewriter->stringifyCode($codewriter->interpolateHTML($code)).')');
            }
        }
    }
}

