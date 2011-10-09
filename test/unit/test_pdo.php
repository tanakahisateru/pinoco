<?php
require_once dirname(__FILE__) . '/../lib/lime.php';
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

$t = new lime_test();
$t->diag("Pinoco_PDOWrapper Test");

$db = new Pinoco_PDOWrapper('sqlite::memory:');
$db->exec("create table foo (
    id integer primary key autoincrement,
    value varchar
)");
$t->pass("create table");
$db->exec("insert into foo (value) values('aaa');");
$rs = $db->query("select * from foo;")->fetchAll();
$t->is($rs->count(), 1);
$t->is($rs[0]->id, $db->lastInsertId());
$t->is($rs[0]->value, 'aaa');

$id_aaa = $rs[0]->id;

$db->exec("insert into foo (value) values('bbb');");
$db->exec("insert into foo (value) values('ccc');");

$r = $db->prepare("select * from foo where id=?;")->query($id_aaa)->fetchOne();
$t->is($r->id, $id_aaa);
$t->is($r->value, 'aaa');

$stmt = $db->query("select * from foo;");
$t->is_deeply($stmt->fetch()->toArray(), array('id'=>'1', 'value'=>'aaa'));
$t->is_deeply($stmt->fetch()->toArray(), array('id'=>'2', 'value'=>'bbb'));
$t->is_deeply($stmt->fetch()->toArray(), array('id'=>'3', 'value'=>'ccc'));
$t->is($stmt->fetch(), false);

$db->exec("create table bar (
    id integer primary key autoincrement,
    foo_id integer references foo(id),
    value varchar
)");
$ps = $db->prepare("insert into bar (foo_id, value) values(?, ?);");
$ps->exec($id_aaa, 'a1');
$ps->exec($id_aaa, 'a2');
$ps->exec($id_aaa, 'a3');

$rs = $db->prepare("select * from foo order by value;")->query()->fetchAll();

// ugly :( fuck php5.2
class ClosureLike {
    function __construct($db) {
        $this->db = $db;
        $this->cc = 0;
    }
    function lazyBarFetcher($owner) {
        $this->cc++;
        return $this->db->prepare(
            "select * from bar where foo_id=? order by value;"
        )->query($owner->id)->fetchAll();
    }
    function lazyBarFetcherforAll($r) {
        return $r->registerAsLazy('childlen', array($this, 'lazyBarFetcher'));
    }
}
$util = new ClosureLike($db);
$cc = &$util->cc;
$rs->map(array($util, 'lazyBarFetcherforAll'));

/*
// smart! I love php5.3.
$cc = 0;
$rs->map(function($r) use($db, &$cc) {
    $r->registerAsLazy('childlen', function($owner) use($db, &$cc) {
        $cc++;
        return $db->prepare(
            "select * from bar where foo_id=? order by value;"
        )->query($owner->id)->fetchAll();
    });
});
*/

$t->is($rs[0]->childlen->count(), 3, "Lazy fetching");
$t->is($rs[0]->childlen[0]->value, 'a1');
$t->is($rs[0]->childlen[1]->value, 'a2');
$t->is($rs[0]->childlen[2]->value, 'a3');
$t->is($rs[1]->childlen->count(), 0);
$t->is($rs[2]->childlen->count(), 0);

$t->is($cc, 3, "Lazy fetcher was called once for each");

