<?php
require_once '../../src/Pinoco.php';
if(file_exists('_app/vendor/autoload.php')){ require '_app/vendor/autoload.php'; }

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();

