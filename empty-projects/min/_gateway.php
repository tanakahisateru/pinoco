<?php
require_once '../../src/Pinoco.php';
//require '_app/vendor/.composer/autoload.php';

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();

