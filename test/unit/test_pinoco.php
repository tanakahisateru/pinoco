<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

function pinoco($base, $path, $dispatcher='') {
    $project = dirname(dirname(dirname(__FILE__)));
    $pinoco = new Pinoco(
        $base,
        $dispatcher,
        $path,
        $project . '/sandbox/www',
        $project . '/sandbox/app'
    );
    return $pinoco;
}

$t = new lime_test();
$t->diag("Pinoco URL Test");

$p = pinoco('/pub', '/page.html');
$t->is($p->baseuri, '/pub');
$t->is($p->path, '/page.html');
$t->is($p->url('/'), '/pub/');
$t->is($p->url('./'), '/pub/');
$t->is($p->url('/foo.html'), '/pub/foo.html');
$t->is($p->url('foo.html'), '/pub/foo.html');
$t->is($p->url('./foo.html'), '/pub/foo.html');
$t->is($p->url('/foo/bar.html'), '/pub/foo/bar.html');
$t->is($p->url('foo/bar?a=1&b=2'), '/pub/foo/bar?a=1&b=2');
$t->is($p->url('./foo/bar?a=1&b=2'), '/pub/foo/bar?a=1&b=2');

$p = pinoco('/pub', '/sub/page.html');
$t->is($p->url('./'), '/pub/sub/');
$t->is($p->url('../'), '/pub/');
$t->is($p->url('foo.html'), '/pub/sub/foo.html');
$t->is($p->url('./foo.html'), '/pub/sub/foo.html');
$t->is($p->url('../foo.html'), '/pub/foo.html');

