<?php
var_dump($this->request->cookie->toArray());

$this->setcookie('pinoco-test', 'foobar' . rand()); //, 0, null, null, true, true);

$this->request->session->pinocotest = 'foobar';
var_dump($this->request->session->pinocotest);

var_dump($this->sent_headers->toArray());

