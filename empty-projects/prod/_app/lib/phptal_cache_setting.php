<?php
if(!function_exists('setupTALRenderer')) {
    function setupTALRenderer($renderer) {
        $renderer->cfg->phpCodeDestination = Pinoco::instance()->sysdir . "/cache";
        $renderer->cfg->encoding = "UTF-8";
        //$renderer->cfg->outputMode = 11;  // XHTML=11, XML=22, HTML5=55
    }
}
Pinoco::instance()->renderers->html->before_rendering = 'setupTALRenderer';

