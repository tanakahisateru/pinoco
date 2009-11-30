<?php
$this->incdir->push("controllers");
$this->update_incdir();

require_once "TestController.php";

switch($this->pathargs[0]) {
case "":
case "index":
    $this->newobj('TestController')->index($this);
    break;
case "show":
    $this->newobj('TestController')->show($this);
    break;
default:
    $this->notfound($this);
}

