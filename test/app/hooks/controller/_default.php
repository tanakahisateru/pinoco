<?php
$this->delegate = $this->newobj(
    'Pinoco/ActionDelegate.php/Pinoco_ActionDispatcher',
    $this->sysdir . "/controllers"
); // library loading should be earier tham here.

$this->delegate->run('TestController', $this->pathargs[0]);
