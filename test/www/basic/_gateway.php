<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
try{
    require_once '../../../src/Pinoco.php';
    $pinoco = Pinoco::create("../../app", array(
    //    'use_mod_rewrite'  => TRUE,  // TRUE or FALSE default TRUE
    //    'use_path_info'    => TRUE,  // TRUE or FALSE default TRUE
    //    'custom_path_info' => FALSE, // FALSE(auto) or string default FALSE
    //    'directory_index'  => "index.html index.php", // string like DirectoryIndex directive default "index.html index.php"
    ));
    $pinoco->run();
}
catch(Exception $ex) {
    header("Content-Type:text/plain;charset=utf-8;");
    printf("%s: %s\n", get_class($ex), $ex->getMessage());
    printf("   %s(%d)\n", $ex->getFile(), $ex->getLine());
    echo $ex->getTraceAsString();
}
