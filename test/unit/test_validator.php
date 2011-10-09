<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

$t = new lime_test();
$t->diag("Pinoco_Validator Test");

$testee = array(
    'foo' => "",
    'bar' => "123",
);
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('empty');
$v->check('bar')->is('not-empty');
$t->ok($v->valid, 'validate array');

$testee = new stdClass();
$testee->foo = "";
$testee->bar = "123";
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('empty');
$v->check('bar')->is('not-empty');
$t->ok($v->valid, 'validate object');

$testee = array(
    'foo' => 1,
    'bar' => 2,
);
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('pass');
$v->check('bar')->is('fail');
$t->is($v->result->foo->valid, true, 'constant');
$t->is($v->result->bar->valid, false);

$testee = array(
    'foo' => "0",
    'bar' => 0,
    'baz' => false,
);
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('not-empty');
$v->check('bar')->is('not-empty');
$v->check('baz')->is('not-empty');
$t->ok($v->valid, 'zero is not empty');

$testee = array(
    'foo' => 1,
    'bar' => "",
    'baz' => "",
);
$v = new Pinoco_Validator($testee, array(
    'not-empty' => "oops"
));
$v->check('foo')->is('empty');  // default
$v->check('bar')->is('not-empty');  // custom
$v->check('baz')->is('not-empty', "fill baz"); // ad-hoc
$t->is($v->errors->keys()->count(), 3, 'messages');
$t->is($v->result->foo->message, "Leave as empty.");
$t->is($v->result->bar->message, "oops");
$t->is($v->result->baz->message, "fill baz");

$v = new Pinoco_Validator(array('foo'=>""));
$v->check('foo')->is('not-empty');
$v->uncheck('foo');
$t->is($v->result->count(), 0, 'uncheck');
$t->is($v->valid, true);

//////////////////// builtin tests
$v = new Pinoco_Validator(array('foo'=>"1234"));
$t->is($v->check('foo')->is('max-length 3')->valid, false, 'builtins');
$t->is($v->result->foo->message, "In 3 letters.", 'message template');

$v = new Pinoco_Validator(array('foo'=>"1234"));
$t->is($v->check('foo')->is('min-length 5')->valid, false);
$t->is($v->result->foo->message, "At least 5 letters.", 'message template');

$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('in 2,3,4')->valid, false);
$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('in 1,2,3')->valid, true);

$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('not-in 1,2,3')->valid, false);
$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('not-in 2,3,4')->valid, true);

$v = new Pinoco_Validator(array('foo'=>"one"));
$t->is($v->check('foo')->is('numeric')->valid, false);

$v = new Pinoco_Validator(array('foo'=>"1.5"));
$t->is($v->check('foo')->is('integer')->valid, false);

$v = new Pinoco_Validator(array('foo'=>"a123"));
$t->is($v->check('foo')->is('alpha')->valid, false);

$v = new Pinoco_Validator(array('foo'=>"a123-"));
$t->is($v->check('foo')->is('alpha-numeric')->valid, false);

$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('== 2')->valid, false);

$v = new Pinoco_Validator(array('foo'=>1));
$t->is($v->check('foo')->is('!= 1')->valid, false);

$v = new Pinoco_Validator(array('foo'=>2));
$t->is($v->check('foo')->is('> 2')->valid, false);
$v = new Pinoco_Validator(array('foo'=>2));
$t->is($v->check('foo')->is('>= 3')->valid, false);
$v = new Pinoco_Validator(array('foo'=>2));
$t->is($v->check('foo')->is('< 2')->valid, false);
$v = new Pinoco_Validator(array('foo'=>2));
$t->is($v->check('foo')->is('<= 1')->valid, false);

$v = new Pinoco_Validator(array('foo'=>"abc"));
$t->is($v->check('foo')->is('match /cd/')->valid, false);
$v = new Pinoco_Validator(array('foo'=>"abc"));
$t->is($v->check('foo')->is('match /ab/')->valid, true);

$v = new Pinoco_Validator(array('foo'=>"abc"));
$t->is($v->check('foo')->is('not-match /ab/')->valid, false);
$v = new Pinoco_Validator(array('foo'=>"abc"));
$t->is($v->check('foo')->is('not-match /cd/')->valid, true);

$v = new Pinoco_Validator(array('foo'=>"foo@bar"));
$t->is($v->check('foo')->is('email')->valid, true);

$v = new Pinoco_Validator(array('foo'=>"http://foo/bar"));
$t->is($v->check('foo')->is('url')->valid, true);
////////////////////////

$v = new Pinoco_Validator(array('foo'=>"abc"));
$v->check('foo')->is('not-empty')->is('numeric')->is('integer');
$t->is($v->result->foo->valid, false, 'priority');
$t->is($v->result->foo->test, 'numeric');

$v = new Pinoco_Validator(array('foo'=>""));
$v->check('foo')->is('numeric');
$t->is($v->valid, true, 'allowing empty');

$next_number = create_function('$v,$p', 'return $v == $p+1;');
$v = new Pinoco_Validator(array('foo'=>2));
$v->defineValidityTest('next-number', $next_number, 'xxx');
$v->check('foo')->is('next-number 1');
$t->is($v->valid, true, 'custom test');

$v->recheck('foo', 'FOO_FIELD')->is('next-number 3',
    '{label} should be {param}+1 but {value}');
$t->is($v->valid, false, 'custom error message');
$t->is($v->result->foo->message, 'FOO_FIELD should be 3+1 but 2');

$func_msg_tmpl = create_function('$param,$value,$label', 'return $param.$value.$label;');
$v->recheck('foo', 'FOO_FIELD')->is('next-number 3', $func_msg_tmpl);
$t->is($v->valid, false, 'custom error message func');
$t->is($v->result->foo->message, '32FOO_FIELD');

///////////////////////
$testee = array(
    'foo' => " abc ",
    'bar' => "def"
);
$v = new Pinoco_Validator($testee);
$t->is($v->check('foo')->value, " abc ", 'filter test');
$t->is($v->check('foo')->is('max-length 3')->valid, false);
$t->is($v->recheck('foo')->filter('trim')->value, "abc");
$t->is($v->recheck('foo')->filter('trim')->is('max-length 3')->valid, true);
$t->is($v->recheck('foo')->filter('trim')->is('max-length 3')->filter('strtoupper')->value, "ABC");
$t->is_deeply($v->values->toArray(), array('foo' => "ABC"));
$v->check('bar');
$t->is_deeply($v->values->toArray(), array('foo' => "ABC", 'bar'=>"def"));

$add_filter = create_function('$v,$p', 'return $v + $p;');
$testee = array(
    'foo' => 2,
);
$v = new Pinoco_Validator($testee);
$v->defineFilter('add', $add_filter);
$t->is($v->check('foo')->filter('add 1')->value, 3, 'user filter test');

