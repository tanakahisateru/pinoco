<?php
function setupTALRenderer($renderer) {
    $pinoco = Pinoco::instance();
    $renderer->cfg->phpCodeDestination = $pinoco->sysdir . "/tmp";
    $renderer->cfg->encoding = "UTF-8";
    //$renderer->cfg->outputMode = 11;  // XHTML=11, XML=22, HTML5=55
}
$this->renderers->html->before_rendering = 'setupTALRenderer';

header("Content-Type:text/html;charset=utf-8");

$this->subscript('_title.php');
//$this->autolocal->title = sprintf("Pinoco Test\n(%s)", basename(dirname($_SERVER['SCRIPT_NAME'])));

//echo "<html>";
//trigger_error("My error", E_USER_WARNING);
//throw new RuntimeException('My exception');

$this->nothing = Pinoco::newNothing();

