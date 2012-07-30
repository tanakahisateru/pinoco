<?php
$this->testvar1 = "initial value";
$this->testvar2 = "initial value";

if (1) {
    $this->terminate();  // Go renderign phase immediately.
}

$this->testvar1 = "changed in _enter";

