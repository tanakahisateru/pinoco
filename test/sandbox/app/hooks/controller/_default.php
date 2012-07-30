<?php
$this->incdir->push("controllers");
$this->updateIncdir();

require_once "TestController.php";

switch ($this->pathargs[0]) {
case "":
case "index":
    $this->newObj('TestController')->index($this);
    break;
case "show":
    $this->newObj('TestController')->show($this);
    break;
default:
    $this->notfound($this);
}

