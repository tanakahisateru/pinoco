<?php
require dirname(__FILE__) . '/_app/bootstrap.php';

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();
