<?php
$this->delegate = array($this->newobj(
    'Pinoco/ActionDelegate.php/Pinoco_ActionDispatcher',
    $this->sysdir . "/controllers"
), 'run');
// library loading should be earier tham here.

$this->delegate('TestController', $this->pathargs[0]);
