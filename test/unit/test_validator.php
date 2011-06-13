<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco/VarsList.php';
require_once dirname(__FILE__) . '/../../src/Pinoco/Validator.php';

$t = new lime_test();
$t->diag("Pinoco_Validator Test");

$testee = array(
    'foo' => "",
    'bar' => "123",
);
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('empty');
$v->check('bar')->is('not-empty');
$t->ok($v->succeeded, 'validate array');

$testee = new stdClass();
$testee->foo = "";
$testee->bar = "123";
$v = new Pinoco_Validator($testee);
$v->check('foo')->is('empty');
$v->check('bar')->is('not-empty');
$t->ok($v->succeeded, 'validate object');

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
$t->ok($v->succeeded, 'zero is not empty');

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

$v = new Pinoco_Validator(array('foo'=>""));
$v->check('foo')->is('not-empty')->is('numeric')->is('integer');
$t->is($v->result->foo->valid, false, 'priprity');
$t->is($v->result->foo->test, 'not-empty');


