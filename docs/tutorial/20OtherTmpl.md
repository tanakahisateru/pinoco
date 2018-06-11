Pinoco is using PHPTAL as standard template engine but, if you want, you can use other template engines to render Pinoco's context via implementing compatible renderer object.

This example is Smarty renderer for Pinoco:

### _app/lib/SmartyRenderer.php
```php
<?php
/**
 * Custom renderer example for Pinoco implemented by Smarty
 */
class SmartyRenderer extends Pinoco_Renderer {
    public function render($page, $extravars = array())
    {
        include_once 'Smarty/Smarty.class.php';
        if(!class_exists('Smarty')){
            $exclass = class_exists('RuntimeException') ? 'RuntimeException' : 'Exception';
            throw new $exclass("Smarty is not installed.");
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
        if(preg_match('/^Smarty-([0-9]+)\./', $smarty->_version, $mo) && $mo[1] >= 3) {
            function smarty_modifier_url($url) {
                return Pinoco::instance()->url($url);
            }
        }
        else {
            $smarty->register_modifier('url', array($this, 'pinoco_url'));
        }
        
        // custom conofig
        foreach($this->cfg as $k => $v) {
            $smarty->$k = $v;
        }
        
        //extract vars
        foreach($this->_sysref->autolocal as $name => $value) {
            $smarty->assign($name, $value);
        }
        foreach($extravars as $name => $value) {
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
```

Then replace the default renderer with it.

### _app/hooks/_enter.php
```php
<?php
require_once "SmartyRenderer.php";
$this->renderers->html = new SmartyRenderer($this);
```

### index.html
```smarty
<p>Hello {$this->message}.</p>
```

Of course, you should install Smarty into your lib folder.

In advance, "I want to keep URL as *.html but *.tpl is better name as Smarty file for my editors...". OK, this is the time to introduce `page_modifier`.

### _app/hooks/_enter.php
```php
<?php
function page_ext_html2tpl($path) {
    if(preg_match('/\/$/', $path)) {
        return $path . 'index.tpl';
    } else {
        return preg_replace('/(.*)\.html$/', '${1}.tpl', $path);
    }
}
$this->page_modifier = 'page_ext_html2tpl';
$this->renderers->tpl = new SmartyRenderer($this);
```

`page_modifier` is a hook point to customize page file resolver's behavior. It works before selecting template file for request URI. In this way, Pinoco switches to "yourpage.tpl" automatically when "yourpage.html" would be accessed.
