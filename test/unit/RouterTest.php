<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $testenv Pinoco_TestEnvironment
     */
    public $testenv;

    protected function setUp()
    {
        $project = dirname(dirname(dirname(__FILE__)));
        $this->testenv = Pinoco::testenv(
            $project . '/test/sandbox/www/basic',
            $project . '/test/sandbox/app',
            '/pub'
        );
    }

    public function testPathRouting()
    {
        $p = $this->testenv->create('/a/b/d');

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/b/c', array($h, 'abc'))
            ->on('/a/b/d', array($h, 'abd'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('abd'=>array()), $h->callHistory);
    }

    public function testMethodRouting()
    {
        $p = $this->testenv->create('/a/b/c');
        $p->request->method = 'POST';

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('GET: /a/b/c', array($h, 'get_abc'))
            ->on('POST:/a/b/c', array($h, 'post_abc'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('post_abc'=>array()), $h->callHistory);
    }

    public function testWildCardRouting()
    {
        $p = $this->testenv->create('/a/b/c');

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/b/d', array($h, 'abd'))
            ->on('*', array($h, 'notfound'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('notfound'=>array()), $h->callHistory);

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/b/*', array($h, 'ab_'))
            ->on('*', array($h, 'notfound'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('ab_'=>array()), $h->callHistory);
    }

    public function testPassingParameters()
    {
        $p = $this->testenv->create('/a/b/c');

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/{b}/{c}', array($h, 'a'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('a'=>array('b', 'c')), $h->callHistory);

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/{b}/c', array($h, 'a_c'));
        $hadled = $r->isMatched();

        $this->assertTrue($hadled);
        $this->assertEquals(array('a_c'=>array('b')), $h->callHistory);
    }

    public function testMissingRoute()
    {
        $p = $this->testenv->create('/a/b/d');

        $h = new TestRouteHandler();
        $r = (new Pinoco_Router($p))
            ->on('/a/b/c', array($h, 'abc'));
        $hadled = $r->isMatched();

        $this->assertFalse($hadled);
    }
}

class TestRouteHandler
{
    public $callHistory;

    function __construct()
    {
        $this->callHistory = array();
    }

    function __call($methodName, $args)
    {
        $this->callHistory[$methodName] = $args;
    }
}