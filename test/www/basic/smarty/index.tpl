<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{$title}</title>
    </head>
    <body>
        <h1>Hello World</h1>
        <p>Sub contents using Smarty</p>
        
        <h2>URL conversion</h2>
        <ul>
            <li>url: subpage.html = <span>{'subpage.html'|url}</span></li>
            <li>url: subdir/index.html = <span>{'subdir/index.html'|url}</span></li>
            <li>url: /abspath/index.html = <span>{'/abspath/index.html'|url}</span></li>
            <li>url: /media/images/existing.jpg = <span>{'/media/images/existing.jpg'|url}</span></li>
            <li>url: /media/images/missing.jpg = <span>{'/media/images/missing.jpg'|url}</span></li>
            <li>url: action.php?param=will_be_alive = <span>{'action.php?param=will_be_alive'|url|escape}</span></li>
        </ul>
        
        <h2>Available variables of $this</h2>
        <ul>
            {foreach from=$this key=k item=v}
            <li>$this-&gt;<span>{$k}</span> = <span>{$v}</span></li>
            {/foreach}
        </ul>
        
        <p><a href="{'../'|url}">Back</a></p>
    </body>
</html>
