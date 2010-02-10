<?php
$this->testvar1 = "initial value";
$this->testvar2 = "initial value";

if(1) {
    $this->skip();  // It works like return syntax.
}

$this->testvar1 = "changed in _enter";

