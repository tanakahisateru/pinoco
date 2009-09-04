<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../../src/Pinoco.php';
$pinoco = Pinoco::create("../../app", array(
//    'use_mod_rewrite'  => TRUE,  // TRUE or FALSE default TRUE
//    'use_path_info'    => TRUE,  // TRUE or FALSE default TRUE
//    'custom_path_info' => FALSE, // FALSE(auto) or string default FALSE
//    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
));
$pinoco->run();

