<?php
$this->renderers->html->cfg->phpCodeDestination = $this->sysdir . "/tmp";
$this->renderers->html->cfg->encoding = "UTF-8";
//$this->renderers->html->cfg->outputMode = 11;  // XHTML=11, XML=22, HTML5=55

header("Content-Type:text/html;charset=utf-8");

$this->subscript('_title.php');
//$this->autolocal->title = sprintf("Pinoco Test\n(%s)", basename(dirname($_SERVER['SCRIPT_NAME'])));

//echo "<html>";
//trigger_error("My error", E_USER_WARNING);
//throw new RuntimeException('My exception');
