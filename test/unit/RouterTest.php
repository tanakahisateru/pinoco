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

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/b/c', array($handler, 'abc'))
            ->on('/a/b/d', array($handler, 'abd'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('abd'=>array()), $handler->callHistory);
    }

    public function testMethodRouting()
    {
        $p = $this->testenv->create('/a/b/c');
        $p->request->server->set('REQUEST_METHOD', 'POST');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('GET: /a/b/c', array($handler, 'get_abc'))
            ->on('POST:/a/b/c', array($handler, 'post_abc'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('post_abc'=>array()), $handler->callHistory);
    }

    public function testWildCardRouting()
    {
        $p = $this->testenv->create('/a/b/c');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/b/d', array($handler, 'abd'))
            ->on('*', array($handler, 'notfound'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('notfound'=>array()), $handler->callHistory);

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/b/*', array($handler, 'ab_'))
            ->on('*', array($handler, 'notfound'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('ab_'=>array()), $handler->callHistory);
    }

    public function testPassingParameters()
    {
        $p = $this->testenv->create('/a/b/c');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/{b}/{c}', array($handler, 'a'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('a'=>array('b', 'c')), $handler->callHistory);

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/{b}/c', array($handler, 'a_c'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('a_c'=>array('b')), $handler->callHistory);
    }

    public function testMissingRoute()
    {
        $p = $this->testenv->create('/a/b/d');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on('/a/b/c', array($handler, 'abc'))
            ->wasMatched();

        $this->assertFalse($handled);
    }

    public function testIgnoreingPattern()
    {
        $p = $this->testenv->create('/a/b/c');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->pass('/a/b/c', array($handler, 'abc'))
            ->on('/a/b/d', array($handler, 'abd'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array(), $handler->callHistory);
    }

    public function testMultiRoutes()
    {
        $p = $this->testenv->create('/a/b/c');

        $handler = new TestRouteHandler();
        $router = new Pinoco_Router($p);
        $handled = $router
            ->on(array('/a/{b}/b', '/a/{b}/c', '/a/{b}/d'), array($handler, 'a_c'))
            ->wasMatched();

        $this->assertTrue($handled);
        $this->assertEquals(array('a_c'=>array('b')), $handler->callHistory);
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