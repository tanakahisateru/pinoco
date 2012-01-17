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

/**
 * Preferred HTML page renderer using PHPTAL
 * @package Pinoco
 */
class Pinoco_TALRenderer extends Pinoco_Renderer {
    
    /**
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars=array())
    {
        include_once 'PHPTAL.php';
        if(!class_exists('PHPTAL')) {
            throw new RuntimeException("PHPTAL is not installed.");
        }
        
        $template = new PHPTAL($page);
        
        //pal namespace loading
        include_once dirname(__FILE__) . '/PAL/NamespaceLoader.php';
        $template->addPreFilter(new Pinoco_PAL_NamespaceLoader());

        //config
        $template->setTemplateRepository($this->_sysref->basedir);
        foreach($this->cfg as $k => $v) {
            $meth = 'set' . strtoupper($k[0]) . substr($k, 1);
            if(method_exists($template, $meth)) {
                call_user_func(array($template, $meth), $v);
            }
        }
        
        //extra TALES definition
        if(!function_exists("phptal_tales_url")) {
            function phptal_tales_url($src, $nothrow)
            {
                $src = phptal_tales('string:' . trim($src), $nothrow);
                return '$ctx->this->url(' . $src . ')';
            }
        }
        
        //extract vars
        foreach($this->_sysref->autolocal as $name=>$value) {
            $template->set($name, $value);
        }
        foreach($extravars as $name=>$value) {
            $template->set($name, $value);
        }
        $template->set('this', $this->_sysref);
        
        //exec
        //ob_start();
        echo $template->execute();
        //ob_end_flush();
    }
}


