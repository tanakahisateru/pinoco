<?php
require_once '../../src/Pinoco.php';
if(file_exists('_app/vendor/autoload.php')){ require '_app/vendor/autoload.php'; }

Pinoco::creditIntoHeader();
Pinoco::create("_app", array(
//    'use_mod_rewrite'  => TRUE,  // TRUE or FALSE default TRUE
//    'use_path_info'    => TRUE,  // TRUE or FALSE default TRUE
//    'custom_path_info' => FALSE, // FALSE(auto) or string default FALSE
//    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
))->config('config', 'config.ini')->run();

