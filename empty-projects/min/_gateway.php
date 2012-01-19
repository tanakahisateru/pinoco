<?php
require_once '_app/lib/Pinoco.php';

Pinoco::creditIntoHeader();
Pinoco::create("_app")->run();

