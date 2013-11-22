<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class PDOWrapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $db Pinoco_PDOWrapper
     */
    protected $db;

    public function setUp() {
        $this->db = new Pinoco_PDOWrapper('sqlite::memory:');
        $this->db->exec("create table foo (
            id integer primary key autoincrement,
            value varchar
        )");
        $this->db->exec("create table bar (
            id integer primary key autoincrement,
            foo_id integer references foo(id),
            value varchar
        )");
    }

    public function tearDown() {
        $this->db->exec("drop table bar");
        $this->db->exec("drop table foo");
    }

    public function testExecQuery()
    {
        $this->db->exec("insert into foo (value) values('aaa');");
        $last_id = $this->db->lastInsertId();
        $rs = $this->db->query("select * from foo;")->fetchAll();
        $this->assertEquals(1, $rs->count());
        $this->assertEquals($last_id, $rs[0]->id);
        $this->assertEquals('aaa', $rs[0]->value);
    }

    public function testPrepareQuery()
    {
        $this->db->exec("insert into foo (value) values('aaa');");
        $a_id = $this->db->lastInsertId();
        $this->db->exec("insert into foo (value) values('bbb');");
        $b_id = $this->db->lastInsertId();
        $this->db->exec("insert into foo (value) values('ccc');");

        $r = $this->db->prepare(
            "select * from foo where id=?;"
        )->query($a_id)->fetchOne();
        $this->assertEquals($a_id, $r->id);
        $this->assertEquals('aaa', $r->value);

        $rs = $this->db->prepare(
            "select * from foo where id=:aid OR id=:bid ORDER BY id;"
        )->query(array('aid'=>$a_id, 'bid'=>$b_id))->fetchAll();
        $this->assertEquals($a_id, $rs[0]->id);
        $this->assertEquals('aaa', $rs[0]->value);
        $this->assertEquals($b_id, $rs[1]->id);
        $this->assertEquals('bbb', $rs[1]->value);

        $rs = $this->db->prepare(
            "select * from foo where id=? OR id=? ORDER BY id;"
        )->query($a_id, $b_id)->fetchAll();
        $this->assertEquals($a_id, $rs[0]->id);
        $this->assertEquals('aaa', $rs[0]->value);
        $this->assertEquals($b_id, $rs[1]->id);
        $this->assertEquals('bbb', $rs[1]->value);
    }

    public function testStatement()
    {
        $this->db->exec("insert into foo (value) values('aaa');");
        $this->db->exec("insert into foo (value) values('bbb');");
        $this->db->exec("insert into foo (value) values('ccc');");
        $stmt = $this->db->query("select * from foo;");
        $this->assertEquals(array('id'=>'1', 'value'=>'aaa'), $stmt->fetch()->toArray());
        $this->assertEquals(array('id'=>'2', 'value'=>'bbb'), $stmt->fetch()->toArray());
        $this->assertEquals(array('id'=>'3', 'value'=>'ccc'), $stmt->fetch()->toArray());
        $this->assertFalse($stmt->fetch());
    }

    public function testLazy()
    {
        $this->db->exec("insert into foo (value) values('aaa');");
        $last_id = $this->db->lastInsertId();
        $this->db->exec("insert into foo (value) values('bbb');");
        $this->db->exec("insert into foo (value) values('ccc');");

        $stmt = $this->db->prepare("insert into bar (foo_id, value) values(?, ?);");
        $stmt->exec($last_id, 'a1');
        $stmt->exec($last_id, 'a2');
        $stmt->exec($last_id, 'a3');

        $rs = $this->db->query("select * from foo order by value;")->fetchAll();

        $lf = new LazyFetcher($this->db);
        $cc = &$lf->cc;
        $rs->map(array($lf, 'lazyBarFetcherForAll'));

        /*
        // smart! I love php5.3.
        $cc = 0;
        $rs->map(function($r) use($db, &$cc) {
            $r->registerAsLazy('children', function($owner) use($db, &$cc) {
                $cc++;
                return $db->prepare(
                    "select * from bar where foo_id=? order by value;"
                )->query($owner->id)->fetchAll();
            });
        });
        */

        $this->assertEquals(0, $cc);
        $this->assertEquals(3, $rs[0]->children->count());
        $this->assertEquals(1, $cc);
        $this->assertEquals('a1', $rs[0]->children[0]->value);
        $this->assertEquals(1, $cc);
        $this->assertEquals('a2', $rs[0]->children[1]->value);
        $this->assertEquals(1, $cc);
        $this->assertEquals('a3', $rs[0]->children[2]->value);
        $this->assertEquals(1, $cc);
        $this->assertEquals(0, $rs[1]->children->count(), 0);
        $this->assertEquals(2, $cc);
        $this->assertEquals(0, $rs[2]->children->count(), 0);
        $this->assertEquals(3, $cc);
    }
}

// ugly :( fuck php5.2
class LazyFetcher {
    /**
     * @var $db Pinoco_PDOWrapper
     */
    protected $db;

    /**
     * @param Pinoco_PDOWrapper $db
     */
    function __construct($db) {
        $this->db = $db;
        $this->cc = 0;
    }

    /**
     * @param Pinoco_Vars $owner
     * @return Pinoco_List
     */
    function lazyBarFetcher($owner) {
        $this->cc++;
        return $this->db->prepare(
            "select * from bar where foo_id=? order by value;"
        )->query($owner->id)->fetchAll();
    }

    /**
     * @param Pinoco_Vars $r
     * @return Pinoco_Vars
     */
    function lazyBarFetcherForAll($r) {
        $r->registerAsLazy('children', array($this, 'lazyBarFetcher'));
        return $r;
    }
}

