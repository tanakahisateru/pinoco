<?php
function setupTALRenderer($renderer) {
    require_once 'PHPTAL.php';
    $renderer->cfg->phpCodeDestination = Pinoco::instance()->sysdir . "/cache";
    $renderer->cfg->encoding = "UTF-8";
    $renderer->cfg->outputMode = PHPTAL::XHTML;  // XHTML, XML or HTML5
}

