<?php
// How to extend Pinoco page renderer.
$this->renderers->tpl = $this->newobj('smarty_renderer.php/Pinoco_SmartyRenderer', $this);
$this->renderers->tpl->cfg->compile_dir = $this->sysdir . "/tmp";
session_start(); // avoid bug of Smarty under 2.6.25

// Trick: The extension html is switched to tpl.
$this->page = preg_match('/\/$/', $this->path) ?
    ($this->path . 'index.tpl') : preg_replace('/(.*)\.html$/', '${1}.tpl', $this->path);

