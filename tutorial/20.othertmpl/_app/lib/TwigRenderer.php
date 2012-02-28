<?php
/**
 * Custom renderer example for Pinoco implemented by Twig
 */
class TwigRenderer extends Pinoco_Renderer {
    public function render($page, $extravars=array())
    {
        include_once 'Twig/Autoloader.php';
        if(!class_exists('Twig_Autoloader')){
            throw new RuntimeException("Twig is not installed.");
        }
        Twig_Autoloader::register();

        if (function_exists('sys_get_temp_dir')) {
            $cachedir = sys_get_temp_dir();
        } elseif (substr(PHP_OS, 0, 3) == 'WIN') {
            $cachedir = file_exists('c:\\WINNT\\Temp\\') ? 'c:\\WINNT\\Temp' : 'c:\\WINDOWS\\Temp\\';
        } else {
            $cachedir = '/tmp/';
        }
        
        $options = array(
          'cache' => $cachedir,
          'auto_reload'=> true,
        );
        foreach($this->cfg as $k => $v) {
            $options[$k] = $v;
        }
        
        $loader = new Twig_Loader_Filesystem($this->_sysref->basedir);
        $twig = new Twig_Environment($loader, $options);
        
        // add URL modifier
        if(!function_exists('twig_url_format_filter')) {
            function twig_url_format_filter($p) {
                return Pinoco::instance()->url($p);
            }
        }
        $twig->addFilter('url', new Twig_Filter_Function('twig_url_format_filter'));
        
        // custom conofig
        /* TODO how can i implement this? */
        
        //extract vars
        $ctx = array_merge(
            $this->_sysref->autolocal->toArray(),
            $extravars,
            array('this'=>$this->_sysref)
        );
        
        //exec
        $template = $twig->loadTemplate($page);
        echo $template->render($ctx);
    }
}
