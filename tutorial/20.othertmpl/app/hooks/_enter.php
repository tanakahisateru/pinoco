<?php
require_once "SmartyRenderer.php";
$this->renderers->html = new SmartyRenderer($this);

/*
// How to retain PHPTAL renderer.
$this->renderers->tpl = new SmartyRenderer($this);
function page_ext_html2tpl($page, $path) {
    return $page ? preg_replace('/(.*)\.html$/', '${1}.tpl', $page) : $path . "/index.tpl";
}
$this->page_modifier = 'page_ext_html2tpl';
*/

