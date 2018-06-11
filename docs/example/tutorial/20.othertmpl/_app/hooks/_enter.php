<?php
require_once "SmartyRenderer.php";

// Below code is added in .htaccess.
// <FilesMatch "\.tpl$">
//    Deny from all
// </FilesMatch>
// Instead, this is alternative way to block directly access to resource.
// if (preg_match('/\.tpl$/', $this->path)) { $this->forbidden(); }

function page_ext_html2tpl($path)
{
    if (preg_match('/\/$/', $path)) {
        return $path . 'index.tpl';
    } else {
        return preg_replace('/(.*)\.html$/', '${1}.tpl', $path);
    }
}
$this->page_modifier = 'page_ext_html2tpl';
// You can use closure if using PHP5.3.
// $this->page_modifier = function($path) {
//    ....
// };
$this->renderers->tpl = new SmartyRenderer($this);

//$this->renderers->html = new SmartyRenderer($this); // easier way
// write your smarty template to *.html files.
