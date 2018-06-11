<?php
require_once dirname(__FILE__) . '/../src/Pinoco/_bootstrap.php';

class HttpRequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $testenv Pinoco_TestEnvironment
     */
    protected $testenv;

    protected function setUp()
    {
        $tests = dirname(__FILE__);
        $this->testenv = Pinoco::testenv(
            $tests . '/sandbox/www',
            $tests . '/sandbox/app',
            '/pub'
        );
    }

    public function testGetParam()
    {
        $_GET['a'] = 1;
        $_GET['b'] = 2;
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertEquals(1, $request->get->a);
        $this->assertEquals(2, $request->get->b);
    }

    public function testPostParam()
    {
        $_POST['a'] = 1;
        $_POST['b'] = 2;
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertEquals(1, $request->post->a);
        $this->assertEquals(2, $request->post->b);
    }

    public function testServerVars()
    {
        $_SERVER['a'] = 1;
        $_SERVER['b'] = 2;
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertEquals(1, $request->server->a);
        $this->assertEquals(2, $request->server->b);
    }

    public function testCookieParam()
    {
        $_COOKIE['a'] = 1;
        $_COOKIE['b'] = 2;
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertEquals(1, $request->cookie->a);
        $this->assertEquals(2, $request->cookie->b);
    }

    public function testSessionVars()
    {
        $_SESSION['a'] = 1;
        $_SESSION['b'] = 2;
        $pinoco = $this->testenv->create('/');
        $request = new Pinoco_HttpRequestVars($pinoco);
        $this->assertEquals(1, $request->session->a);
        $this->assertEquals(2, $request->session->b);
        $this->assertRegExp('/Set-Cookie:/', $pinoco->sent_headers->join("\n"));
    }

    public function testEnvVars()
    {
        $_ENV['a'] = 1;
        $_ENV['b'] = 2;
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertEquals(1, $request->env->a);
        $this->assertEquals(2, $request->env->b);
    }

    public function testRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertTrue($request->isHead());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertTrue($request->isGet());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertTrue($request->isPost());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertTrue($request->isPut());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = new Pinoco_HttpRequestVars($this->testenv->create('/'));
        $this->assertTrue($request->isDelete());
    }
}
