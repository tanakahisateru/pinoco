<?php
// How to extend Pinoco page renderer.
$this->renderers->tpl = $this->newObj('SmartyRenderer.php/SmartyRenderer', $this);
$this->renderers->tpl->cfg->compile_dir = $this->sysdir . "/tmp";
session_start(); // avoid bug of Smarty under 2.6.25

// Trick: Switching extension html to tpl.
function page_ext_html2tpl($page, $path)
{
    return $page ? preg_replace('/(.*)\.html$/', '${1}.tpl', $page) : $path . "/index.tpl";
}
$this->page_modifier = 'page_ext_html2tpl';

// Session in URL
function inject_lang_to_url($url, $renderable)
{
    if ($renderable) {
        $sep = (strpos($url, "?") === false) ? "?" : "&";
        return $url . $sep . htmlspecialchars("SESSID=1234567890");
    }
    else {
        return $url;
    }
}
$this->url_modifier = 'inject_lang_to_url';

