<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php if (isset($title)) { echo $title; } else { ?>Pinoco Test<?php } ?></title>
    </head>
    <body>
        <h1>Hello World</h1>
        <p>Sub contents2 (using PHP)</p>
        
        <?php include "./_vardump.inc"; ?>
        
        <p><a href="<?php echo $this->url("../"); ?>">Back</a></p>
    </body>
</html>
