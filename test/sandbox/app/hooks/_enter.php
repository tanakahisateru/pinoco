<?php
if(!function_exists('setupTALRenderer')) {
    function setupTALRenderer($renderer) {
        $pinoco = Pinoco::instance();
        $renderer->cfg->phpCodeDestination = $pinoco->sysdir . "/tmp";
        $renderer->cfg->encoding = "UTF-8";
        //$renderer->cfg->outputMode = 11;  // XHTML=11, XML=22, HTML5=55
    }
}
$this->renderers->html->before_rendering = 'setupTALRenderer';

$this->header("Content-Type:text/html;charset=utf-8");

$this->subscript('_title.php');

//echo "<html>";
//trigger_error("My error", E_USER_WARNING);
//throw new RuntimeException('My exception');

$this->nothing = Pinoco::newNothing();

$this->form = Pinoco_Validator::emptyResult(array('foo'=>1234));
