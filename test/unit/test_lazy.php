<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

$t = new lime_test();
$t->diag("Pinoco_LazyValueProxy Test");

$fetcher = create_function('$owner', 'return "lazy value";');
$p = new Pinoco_LazyValueProxy($fetcher);
$t->is($p->fetch(), 'lazy value');

$mutable_fetcher = create_function('$owner', 'global $ccc; return ++$ccc;');
$p = new Pinoco_LazyValueProxy($mutable_fetcher);
$t->is($p->fetch(), 1);
$t->is($p->fetch(), 2);

$mutable_fetcher = create_function('$owner', 'global $ccc2; return ++$ccc2;');
$p = new Pinoco_LazyValueProxy($mutable_fetcher, true);
$t->is($p->fetch(), 1);
$t->is($p->fetch(), 1);

$o = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$fetcher = create_function('$owner', 'return $owner->a;');
$p = new Pinoco_LazyValueProxy($fetcher);
$t->is($p->fetch($o), 1);

$fetcher = create_function('$owner,$a1,$a2', 'return $owner->b+$a1+$a2;');
$p = new Pinoco_LazyValueProxy($fetcher, false, array(3, 4));
$t->is($p->fetch($o), 9);

$t->diag("Pinoco_LazyValueProxy Host Test");
$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$fetcher = create_function('$owner', 'return $owner->a;');
$v->lazyprop = new Pinoco_LazyValueProxy($fetcher);
$t->is($v->keys()->count(), 3);
$t->is($v->lazyprop, 1);

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$v->registerAsDynamic('c', create_function('$owner', 'return $owner->b;'));
$t->is($v->c, 2);
$v->registerAsLazy('d', create_function('$owner', 'global $ccc3; return ++$ccc3;'));
$t->is($v->d, 1);
$t->is_deeply($v->toArray(), array('a'=>1, 'b'=>2, 'c'=>2, 'd'=>1));

$t->diag("Marking Dirty");
$v->markAsDirty('d');
$t->is($v->d, 2);

