<?php
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require dirname(__FILE__) . '/vendor/autoload.php';
} else {
    require_once dirname(__FILE__) . '/lib/Pinoco.php';
}
