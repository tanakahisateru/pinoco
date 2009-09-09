<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php if(isset($title)){ echo $title; }else{ ?>Pinoco Test<?php } ?></title>
    </head>
    <body>
        <h1>Hello World</h1>
        <p>Sub contents2 (using PHP)</p>
        
        <h2>URL conversion</h2>
        <ul>
            <li>url: subpage.html = <?php echo $this->url("subpage.html"); ?></li>
            <li>url: subdir/index.html = <?php echo $this->url("subdir/index.html"); ?></li>
            <li>url: /abspath/index.html = <?php echo $this->url("/abspath/index.html"); ?></li>
        </ul>
        
        <h2>Available variables of $this</h2>
        <ul>
        <?php foreach($this as $name=>$value): ?>
            <li>$this-&gt;<?php echo $name; ?> = <?php echo $value; ?></li>
        <?php endforeach; ?>
        </ul>
        
        <p><a href="<?php echo $this->url("../index.html"); ?>">Back</a></p>
    </body>
</html>
