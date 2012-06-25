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
if(!class_exists('PHPTAL_Php_Attribute_TAL_Attributes')) { require_once 'PHPTAL/Php/Attribute/TAL/Attributes.php'; }

/**
 * @package Pinoco
 * @subpackage PAL
 */
class Pinoco_PAL_Attr extends PHPTAL_Php_Attribute_TAL_Attributes
{
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
        // prepare
        $codewriter->pushCode('if(isset($ctx->attr)){$_pal_attr_bak=$ctx->attr;}');
        $codewriter->doSetVar('$ctx->attr', 'array()');
        $attrs = $this->phpelement->getAttributeNodes();
        foreach ($attrs as $attr) {
            $qname = $attr->getQualifiedName();
            $default_attr = $attr->getValueEscaped();
            $codewriter->doSetVar('$ctx->attr[\'' . $qname . '\']', '\''. addcslashes($default_attr, "\\$\'\"\\\0\n\r\t") . '\'');
        }
        // main
        parent::before($codewriter);
        // cleanup
        $codewriter->pushCode('unset($ctx->attr)');
        $codewriter->pushCode('if(isset($_pal_attr_bak)){$ctx->attr=$_pal_attr_bak;unset($_pal_attr_bak);}');
    }
}

