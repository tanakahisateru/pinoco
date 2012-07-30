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
 * Default HTML page renderer using native PHP.
 * @package Pinoco
 */
class Pinoco_NativeRenderer extends Pinoco_Renderer
{
    /**
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars=array())
    {
        $vars = $this->_sysref->autolocal->toArray();
        foreach ($extravars as $k=>$v) {
            $vars[$k] = $v;
        }
        $orig_dir  = getcwd();
        chdir($this->_sysref->parentPath($this->_sysref->basedir . "/" . $page));
        $this->_sysref->updateIncdir();
        $this->_sysref->_includeWithThis($this->_sysref->basedir . "/" . $page, $vars);
        chdir($orig_dir);
    }
}

