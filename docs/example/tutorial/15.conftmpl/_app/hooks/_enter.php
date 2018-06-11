<?php
if (!class_exists('PHPTAL')) {
	require_once "PHPTAL.php";
}
$this->renderers->html->cfg->outputMode = PHPTAL::HTML5;
$this->renderers->html->cfg->forceReparse = true;
// ... You can create any entry in cfg variable.

// If you want to use PHPTAL::SetAbcXyz(...) then you should write as html->cfg->abcXyz=...
// This way will be commonly implemented in other renderer plugin.
