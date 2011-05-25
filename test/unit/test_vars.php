<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco/VarsList.php';

$t = new lime_test();

$v = new Pinoco_Vars();
$v->set('foo', 'bar');
$t->is($v->get('foo'), 'bar', "Vars can get value previously set.");

$v = new Pinoco_Vars();
$v->foo = 'bar';
$t->is($v->get('foo'), 'bar', "Vars can get value set by ->.");

$v = new Pinoco_Vars();
$v->set('foo', 'bar');
$t->is($v->foo, 'bar', "Vars can get value using ->.");

$v = new Pinoco_Vars();
$v['foo'] = 'bar';
$t->is($v->get('foo'), 'bar', "Vars can get value set by [].");

$v = new Pinoco_Vars();
$v->set('foo', 'bar');
$t->is($v['foo'], 'bar', "Vars can get value using [].");

$src = array('a'=>1, 'b'=>2, 'c'=>3);
$v = Pinoco_Vars::fromArray($src);
$t->is($v->a, 1);
$t->is($v->b, 2);
$t->is($v->c, 3);
$src['a'] = 10;
$t->is($v->a, 1, "fromArray of Vars doesn't share values with source.");
$v->b = 20;
$t->is($src['b'], 2, "fromArray of Vars doesn't share values with source.");

$src = array('a'=>1, 'b'=>2, 'c'=>3);
$v = Pinoco_Vars::wrap($src);
$t->is($v->a, 1);
$t->is($v->b, 2);
$t->is($v->c, 3);
$src['a'] = 10;
$t->is($v->a, 10, "wrap of Vars shares values with source.");
$v->b = 20;
$t->is($src['b'], 20, "wrap of Vars shares values with source.");

