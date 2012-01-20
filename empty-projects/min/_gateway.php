<?php
require_once '../../src/Pinoco.php';

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();

