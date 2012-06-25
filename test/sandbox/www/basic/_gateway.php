<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//TEST:
if(function_exists('xdebug_disable')){ xdebug_disable(); }

require_once '../../../../src/Pinoco.php';

if(file_exists('../../../../vendor/autoload.php')) {
    require '../../../../vendor/autoload.php';
    //uncomment to disable PHPTAL class loader.
    //if(class_exists('PHPTAL')) {
    //    spl_autoload_unregister(array('PHPTAL','autoload'));
    //}
}

Pinoco::creditIntoHeader();
$pinoco = Pinoco::create("../../app", array(
//    'use_mod_rewrite'  => true,  // true or false default true
//    'use_path_info'    => true,  // true or false default true
//    'custom_path_info' => false, // false(auto) or string default false
//    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
))
->config('cfg', 'config/main.ini')
->config('cfg', 'config/override.php')
->config('cfg', array('baz'=>300))
->run();

