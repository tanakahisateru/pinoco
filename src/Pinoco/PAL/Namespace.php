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

require_once 'PHPTAL.php';
require_once 'PHPTAL/Namespace.php';
require_once 'PHPTAL/Dom/Defs.php';

/**
 * PHPTAL extensions
 *
 * <code>
 *   <p pal:content-nl2br="this/var">foo</p> <!-- nl2br text escaped -->
 *   <p pal:content-nl2br="structure this/var">foo</p> <!-- like markdown -->
 *   <p>
 *       <span pal:replace-nl2br="this/var">foo</span>
 *   </p>
 *   <a href="prev.html" pal:attr="href url:/page/${attr/href}">prev</a>
 *   <a href="next.html" pal:attr="href url:/page/${attr/href}">next</a>
 * </code>
 *
 * @package Pinoco
 * @subpackage PAL
 */
class Pinoco_PAL_Namespace extends PHPTAL_Namespace
{
    public function __construct()
    {
        // namespace
        parent::__construct('pal', 'http://pinoco.org/ns/pal');
        // attributes in namescape
        $this->addAttribute(new PHPTAL_NamespaceAttributeContent('content-nl2br', 11));
        $this->addAttribute(new PHPTAL_NamespaceAttributeReplace('replace-nl2br', 9));
        $this->addAttribute(new PHPTAL_NamespaceAttributeSurround('attr', 9));
    }

    public function createAttributeHandler(PHPTAL_NamespaceAttribute $att, PHPTAL_Dom_Element $tag, $expression)
    {
        $attrNames = array(
            'content-nl2br' => 'Pinoco_PAL_ContentNl2br',
            'replace-nl2br' => 'Pinoco_PAL_ReplaceNl2br',
            'attr'          => 'Pinoco_PAL_Attr',
        );
        $class = $attrNames[$att->getLocalName()];
        return new $class($tag, $expression);
    }
}

PHPTAL_Dom_Defs::getInstance()->registerNamespace(new Pinoco_PAL_Namespace());

