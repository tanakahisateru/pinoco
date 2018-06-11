<?php
require_once dirname(__FILE__) . '/../lib/setting_functions.php';
$this->renderers->html->before_rendering = 'setupTALRenderer';
// Do something here! 
// $this->header("Content-Type: text/html;charset=utf-8");
