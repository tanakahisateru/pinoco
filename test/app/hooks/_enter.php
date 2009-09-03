<?php
$this->renderers->html->cfg->phpCodeDestination = $this->sysdir . "/tmp";
$this->renderers->html->cfg->encoding = "UTF-8";
//$this->renderers->html->cfg->outputMode = 11;  // XHTML=11, XML=22, HTML5=55

header("Content-Type:text/html;charset=utf-8");

ini_set('display_errors', 1);
error_reporting(E_ALL);

$this->autolocal->title = sprintf("Pinoco Test (%s)", basename(dirname($_SERVER['SCRIPT_NAME'])));

