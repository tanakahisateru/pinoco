<?php
/**
 * Custom renderer example for Pinoco implemented by Smarty
 */
class SmartyRenderer extends Pinoco_Renderer
{
    public function render($page, $extravars=array())
    {
		if (!class_exists('Smarty')) {
	        include_once 'Smarty/Smarty.class.php';
		}
        if (!class_exists('Smarty')) {
            throw new RuntimeException("Smarty is not installed.");
        }

        $smarty = new Smarty();

        //config
        $smarty->template_dir = $this->_sysref->basedir;
        if (function_exists('sys_get_temp_dir')) {
            $smarty->compile_dir = sys_get_temp_dir();
        } elseif (substr(PHP_OS, 0, 3) == 'WIN') {
            $smarty->compile_dir = file_exists('c:\\WINNT\\Temp\\') ? 'c:\\WINNT\\Temp' : 'c:\\WINDOWS\\Temp\\';
        } else {
            $smarty->compile_dir = '/tmp/';
        }

        // add URL modifier
        if (is_callable(array($smarty, 'registerPlugin'))) {
			// Smarty3
			$smarty->registerPlugin('modifier', 'url', array($this, 'pinoco_url'));
        }
        else {
			// Smarty2
			$smarty->register_modifier('url', array($this, 'pinoco_url'));
        }

        // custom conofig
        foreach ($this->cfg as $k => $v) {
            $smarty->$k = $v;
        }

        //extract vars
        foreach ($this->_sysref->autolocal as $name=>$value) {
            $smarty->assign($name, $value);
        }
        foreach ($extravars as $name=>$value) {
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
