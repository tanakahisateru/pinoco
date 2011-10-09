<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

$t = new lime_test();
$t->diag("Pinoco_List Test");

$l = new Pinoco_List();
$l->push(1);
$t->is($l->count(), 1, "List can push");
$t->is($l->get(0), 1, "List can access by position");
$t->is($l->get(1), null, "Safe against overflow access");

$l = new Pinoco_List();
$l[1] = 1;
$t->is($l[1], 1, "List can assess by []");
$t->is($l[0], null, "Filled lessor indices automatically");
$t->is($l->count(), 2, "Filled lessor indices automatically");

$src = array(1, 2, 3);
$l = Pinoco_List::fromArray($src);
$src[0] = 10;
$t->is($l[0], 1, "fromArray doesn't share values with source.");
$l[1] = 20;
$t->is($src[1], 2, "fromArray doesn't share values with source.");
$src[] = 4;
$t->is($l->count(), 3, "fromArray of Vars doesn't share values with source.");

$src = array(1, 2, 3);
$l = Pinoco_List::wrap($src);
$src[0] = 10;
$t->is($l[0], 10, "wrap shares values with source.");
$l[1] = 20;
$t->is($src[1], 20, "wrap shares values with source.");
$src[] = 4;
$t->is($l->count(), 4, "wrap shares values with source.");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is(count($l), 3, "List is countable");

$l = new Pinoco_List();
$l->push(1);
$t->is($l->get(1), null, "default default value");
$l->setDefault(-1);
$t->is($l->get(1), -1, "user default value");
$t->is($l->get(1, -2), -2, "Ad-hoc default value");

$t->comment("push/pop");
$l = Pinoco_List::fromArray(array(1, 2));
$t->is($l->pop(), 2);
$t->is($l->count(), 1);
$t->is_deeply($l->toArray(), array(1));
$l->push(3);
$t->is($l->count(), 2);
$t->is_deeply($l->toArray(), array(1, 3));
$l->push(4, 5, 6);
$t->is_deeply($l->toArray(), array(1, 3, 4, 5, 6), "push many");

$t->comment("shift/unshift");
$l = Pinoco_List::fromArray(array(1, 2));
$t->is($l->shift(), 1);
$t->is($l->count(), 1);
$t->is_deeply($l->toArray(), array(2));
$l->unshift(3);
$t->is($l->count(), 2);
$t->is_deeply($l->toArray(), array(3, 2));
$l->unshift(4, 5, 6);
$t->is_deeply($l->toArray(), array(6, 5, 4, 3, 2), "unshift many");

$l = Pinoco_List::fromArray(array(1, 2));
$l->concat(array(3, 4));
$t->is_deeply($l->toArray(), array(1, 2, 3, 4), "concat with Array");
$l->concat(Pinoco_List::fromArray(array(5, 6)));
$t->is_deeply($l->toArray(), array(1, 2, 3, 4, 5, 6), "concat with another List");
$l = Pinoco_List::fromArray(array(1, 2));
$l->concat(array(3, 4), array(5, 6));
$t->is_deeply($l->toArray(), array(1, 2, 3, 4, 5, 6), "concat many");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is($l->join(), '1,2,3', "join");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is_deeply($l->reverse()->toArray(), array(3, 2, 1), "reverse");
$t->is_deeply($l->toArray(), array(1, 2, 3), "reverse is not mutator");

$l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
$t->is_deeply($l->slice(1)->toArray(), array(2, 3, 4, 5), "slice");
$t->is_deeply($l->slice(1, 3)->toArray(), array(2, 3, 4), "slice");
$t->is_deeply($l->toArray(), array(1, 2, 3, 4, 5), "slice is not mutator");

$l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
$t->is_deeply($l->splice(1, 1)->toArray(), array(2), "splice");
$t->is_deeply($l->toArray(), array(1, 3, 4, 5), "splice is mutator");
$l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
$t->is_deeply($l->splice(1, 2, array(-1, -2, -3))->toArray(), array(2, 3), "splice");
$t->is_deeply($l->toArray(), array(1, -1, -2, -3, 4, 5), "splice is mutator");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$l->insert(1, -1);
$t->is_deeply($l->toArray(), array(1, -1, 2, 3), "insert");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$l->remove(1);
$t->is_deeply($l->toArray(), array(1, 3), "remove");
$l = Pinoco_List::fromArray(array(1, 2, 3, 4));
$l->remove(1, 2);
$t->is_deeply($l->toArray(), array(1, 4), "remove range");

$l = Pinoco_List::fromArray(array(0, 1, 2, 3));
$t->is($l->index(2), 2, "index");

$name_mod = create_function('$orig', 'return sprintf("m_%03d", $orig);');
$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is_deeply($l->toArray('m_'), array('m_0'=>1, 'm_1'=>2, 'm_2'=>3), "toArray special");
$t->is_deeply($l->toArray('m_%d'), array('m_0'=>1, 'm_1'=>2, 'm_2'=>3), "toArray special");
$t->is_deeply($l->toArray($name_mod), array('m_000'=>1, 'm_001'=>2, 'm_002'=>3), "toArray special");

$reducer = create_function('$a,$b', 'return $a + $b;');
$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is($l->reduce($reducer), 6, "reduce");
$t->is($l->reduce($reducer, 4), 10, "reduce");

$mapper = create_function('$a', 'return $a + 1;');
$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is_deeply($l->map($mapper)->toArray(), array(2, 3, 4), "map");

$filter = create_function('$a', 'return $a >= 2;');
$l = Pinoco_List::fromArray(array(1, 2, 3));
$t->is_deeply($l->filter($filter)->toArray(), array(2, 3), "filter");

ob_start();
$l = Pinoco_List::fromArray(array(1, 2, 3));
$l->each('printf');
$ob = ob_get_clean();
$t->is($ob, '123', "each");

$t->todo("all");
$t->todo("any");

$l = Pinoco_List::fromArray(array(1, 2, 3));
$tmp = array();
foreach($l as $e) {
    $tmp[] = $e;
}
$t->is_deeply($tmp, array(1, 2, 3), "List is iterable");

$l = new Pinoco_List();
$l->push(Pinoco_List::fromArray(array(1, 2)));
$l->push(Pinoco_List::fromArray(array(3, 4)));
$t->is_deeply($l->toArrayRecurse(), array(
    array(1, 2),
    array(3, 4),
), "toArrayRecurse");
$tmp = $l->toArrayRecurse(0);
$t->cmp_ok($tmp, '===', $l, "toArrayRecurse 0");
$tmp = $l->toArrayRecurse(1);
$t->cmp_ok($tmp[0], '===', $l[0], "toArrayRecurse 1");
$t->cmp_ok($tmp[1], '===', $l[1], "toArrayRecurse 1");


