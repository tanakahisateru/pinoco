<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco/VarsList.php';

$t = new lime_test();
$t->diag("Pinoco_Vars Test");

$v = new Pinoco_Vars();
$v->set('foo', 'bar');
$t->is($v->has('foo'), true, "Vars can check value previously set.");
$t->is($v->has('xxx'), false, "Vars can check value previously set.");
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
$src['d'] = 40;
$t->is($v->has('d'), false, "fromArray of Vars doesn't share values with source.");

$src = array('a'=>1, 'b'=>2, 'c'=>3);
$v = Pinoco_Vars::wrap($src);
$t->is($v->a, 1);
$t->is($v->b, 2);
$t->is($v->c, 3);
$src['a'] = 10;
$t->is($v->a, 10, "wrap of Vars shares values with source.");
$v->b = 20;
$t->is($src['b'], 20, "wrap of Vars shares values with source.");
$src['d'] = 40;
$t->is($v->has('d'), true, "wrap of Vars shares values with source.");

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2, 'c'=>3));
$t->is_deeply($v->keys()->toArray(), array('a','b','c'), "keys of Vars");

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$t->cmp_ok($v->get('c'), '===', null, "default default value");
$v->setDefault('EMPTY');
$t->is($v->get('c'), 'EMPTY', "user default value");
$t->cmp_ok($v->get('c', 0), '===', 0, "ad-hoc default value");

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$v->setLoose(true);
$t->is($v->has('c'), true, "has() answers always true if loose");
$t->is($v->c, null);
$v->setLoose(false);
$t->is($v->has('c'), false, "has() answers strictly if not loose");
$t->is($v->c, null);

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$v->remove('a');
$t->is($v->keys()->count(), 1, "removing property");
$t->is($v->has('a'), false, "removing property");
unset($v->b);
$t->is($v->has('b'), false, "removing property using unset");

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$tmp=array();
foreach($v as $k=>$e) {
    $tmp[] = $k;
    $tmp[] = $e;
}
$t->is_deeply($tmp, array('a', 1, 'b', 2), "Vars is iterable");

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$t->is_deeply($v->toArray(), array('a'=>1, 'b'=>2));
$t->is_deeply($v->toArray(array('a','c')), array('a'=>1, 'c'=>null));
$t->is_deeply($v->toArray(array('a','c'), -1), array('a'=>1, 'c'=>-1));
$t->is_deeply($v->toArray(false, null, 'm_%s'), array('m_a'=>1, 'm_b'=>2));

$v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
$v->import(array('c'=>3));
$t->is_deeply($v->toArray(), array('a'=>1, 'b'=>2, 'c'=>3));
$v->import(array('d'=>4, 'e'=>5), array('e'));
$t->is_deeply($v->toArray(), array('a'=>1, 'b'=>2, 'c'=>3, 'e'=>5));
$v->import(array('f'=>6, 'g'=>7), array('g', 'h'), -1);
$t->is_deeply($v->toArray(), array(
    'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1
));
$v->import(array('i'=>9), false, null, 'm_%s');
$t->is_deeply($v->toArray(), array(
    'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1, 'm_i'=>9
));
function name_mod($orig) {
    return 'm_' . $orig;
}
$v->import(array('j'=>10), false, null, 'name_mod');
$t->is_deeply($v->toArray(), array(
    'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1, 'm_i'=>9, 'm_j'=>10
));


