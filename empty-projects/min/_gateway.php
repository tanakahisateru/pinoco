<?php
require_once '../../src/Pinoco.php';
if (file_exists('_app/vendor/autoload.php')) { require '_app/vendor/autoload.php'; }
if (!class_exists('Pinoco')) { require_once '_app/lib/Pinoco.php'; }

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();

