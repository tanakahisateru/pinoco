<?php
/**
 * Custom renderer example for Pinoco implemented by Smarty
 */
class Pinoco_SmartyRenderer extends Pinoco_Renderer {
    public function render($page, $extravars=array())
    {
        $smarty = $this->_sysref->newobj('Smarty/Smarty.class.php/Smarty');
        
        //config
        $smarty->template_dir = $this->_sysref->basedir;
        if (function_exists('sys_get_temp_dir')) {
            $smarty->compile_dir = sys_get_temp_dir();
        } elseif (substr(PHP_OS, 0, 3) == 'WIN') {
            $smarty->compile_dir = file_exists('c:\\WINNT\\Temp\\') ? 'c:\\WINNT\\Temp' : 'c:\\WINDOWS\\Temp\\';
        } else {
            $smarty->compile_dir = '/tmp/';
        }
        $smarty->register_modifier('url', array($this, 'pinoco_url'));
        
        // custom conofig
        foreach($this->cfg as $k => $v) {
            $smarty->$k = $v;
        }
        
        //extract vars
        foreach($this->_sysref->autolocal as $name=>$value) {
            $smarty->assign($name, $value);
        }
        foreach($extravars as $name=>$value) {
            $smarty->assign($name, $value);
        }
        $smarty->assign('this', $this->_sysref);
        
        //exec
        $smarty->display($this->_sysref->basedir . $page);
    }
    
    public function pinoco_url($url)
    {
        return Pinoco::instance()->url($url);
    }
}
