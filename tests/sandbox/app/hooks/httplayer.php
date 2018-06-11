<?php
var_dump($this->request->cookie->toArray());

$this->setcookie('pinoco-test', 'foobar' . rand()); //, 0, null, null, true, true);

var_dump("before", $this->request->session->toArrayRecurse());
$this->request->session->pinocotest = 'foobar' . rand();
var_dump("after", $this->request->session->toArrayRecurse());

var_dump($this->sent_headers->toArray());
