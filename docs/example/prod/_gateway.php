<?php
require dirname(__FILE__) . '/_app/bootstrap.php';

Pinoco::creditIntoHeader();
Pinoco::create("_app", array(
//    'use_mod_rewrite'  => true,  // true or false default true
//    'use_path_info'    => true,  // true or false default true
//    'custom_path_info' => false, // false(auto) or string default false
//    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
))->config('config', 'config.ini')->run();
