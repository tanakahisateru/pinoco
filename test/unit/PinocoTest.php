<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class PinocoTest extends PHPUnit_Framework_TestCase
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

    public function testUrl()
    {
        $p = $this->testenv->create('/page.html');
        $this->assertEquals('/pub',                 $p->baseuri);
        $this->assertEquals('/page.html',           $p->path);
        $this->assertEquals('/pub/',                $p->url('/'));
        $this->assertEquals('/pub/',                $p->url('./'));
        $this->assertEquals('/pub/foo.html',        $p->url('/foo.html'));
        $this->assertEquals('/pub/foo.html',        $p->url('foo.html'));
        $this->assertEquals('/pub/foo.html',        $p->url('./foo.html'));
        $this->assertEquals('/pub/foo/bar.html',    $p->url('/foo/bar.html'));
        $this->assertEquals('/pub/foo/bar?a=1&b=2', $p->url('foo/bar?a=1&b=2'));
        $this->assertEquals('/pub/foo/bar?a=1&b=2', $p->url('./foo/bar?a=1&b=2'));
        $p = $this->testenv->create('/sub/page.html');
        $this->assertEquals('/pub',              $p->baseuri);
        $this->assertEquals('/sub/page.html',    $p->path);
        $this->assertEquals('/pub/',             $p->url('/'));
        $this->assertEquals('/pub/sub/',         $p->url('./'));
        $this->assertEquals('/pub/',             $p->url('../'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('foo.html'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('./foo.html'));
        $this->assertEquals('/pub/foo.html',     $p->url('../foo.html'));
    }

    public function testHeader()
    {
        $p = $this->testenv->create('/');
        $p->header('Content-type:text/plain');
        $this->assertEquals(
            array('Content-type:text/plain'),
            $p->sent_headers->toArray()
        );
        $p->header('Content-type:appliction/octet-stream');
        $this->assertEquals(
            array('Content-type:appliction/octet-stream'),
            $p->sent_headers->toArray()
        );
        $p->header('Content-type:text/html', false);
        $this->assertEquals(
            array('Content-type:appliction/octet-stream', 'Content-type:text/html'),
            $p->sent_headers->toArray()
        );
        $p->header('HTTP/1.0 404 Not Found');
        $p->header('HTTP/1.0 403 Forbidden', false);
        $this->assertEquals(
            array('Content-type:appliction/octet-stream', 'Content-type:text/html', 'HTTP/1.0 403 Forbidden'),
            $p->sent_headers->toArray()
        );
    }

    public function testCookie()
    {
        $p = $this->testenv->create('/');
        $p->setcookie('foo', 'bar');
        $this->assertEquals(
            array('Set-Cookie: foo=bar; path=/pub/'),
            $p->sent_headers->toArray()
        );

        $p = $this->testenv->create('/');
        $p->setcookie('foo', null);
        $this->assertRegExp(
            '/^Set-Cookie: foo=; expires=/',
            $p->sent_headers->get(0)
        );

        $p = $this->testenv->create('/');
        $p->request->session->foo = 'bar';
        $this->assertEquals('bar', $p->request->session->foo);
        $this->assertRegExp(
            '/^Set-Cookie: ' . session_name() . '=/',
            $p->sent_headers->get(0)
        );
    }

    public function _urlModifier($path, $renderable) {
        return '/modified' . $path . ($renderable ? '/renderable' : '/binary');
    }

    public function testUrlModifier()
    {
        $p = $this->testenv->create('/path/to/page.html');
        $p->url_modifier = array($this, '_urlModifier');
        $this->assertEquals(
            '/modified/pub/path/to/foo.html/renderable',
            $p->url('foo.html')
        );
        $this->assertEquals(
            '/modified/pub/media/images/existing.jpg/binary', // depends on sandbox dir structure
            $p->url('/media/images/existing.jpg')
        );
    }

    public function testRedirect()
    {
        // host header exists
        $p = $this->testenv->create('/data/post');
        $p->request->server->set('HTTP_HOST', 'localhost:8080');
        try {
            $p->redirect('view');
            $this->fail();
        }
        catch (Pinoco_FlowControlHttpRedirect $ex) {
            $ex->respond($p);
            $this->assertEquals('Location: http://localhost:8080/pub/data/view',
                $p->sent_headers->get(0));
        }
        // no host header
        $p = $this->testenv->create('/data/post');
        $p->request->server->set('SERVER_NAME', 'localhost');
        $p->request->server->set('SERVER_PORT', '8080');
        try {
            $p->redirect('view');
            $this->fail();
        }
        catch (Pinoco_FlowControlHttpRedirect $ex) {
            $ex->respond($p);
            $this->assertEquals('Location: http://localhost:8080/pub/data/view',
                $p->sent_headers->get(0));
        }
        // vrious path
        $this->assertEquals('Location: http://localhost:8080/pub/data/view',
            $this->_redirectTestHelper('view'));
        $this->assertEquals('Location: http://localhost:8080/pub/view',
            $this->_redirectTestHelper('/view'));
        $this->assertEquals('Location: http://localhost:8080/view',
            $this->_redirectTestHelper('/view', true));
        $this->assertEquals('Location: http://other-host/view',
            $this->_redirectTestHelper('http://other-host/view'));
        $this->assertEquals('Location: http://other-host/view',
            $this->_redirectTestHelper('//other-host/view'));
    }

    private function _redirectTestHelper($to, $external=false)
    {
        $p = $this->testenv->create('/data/post');
        $p->request->server->set('HTTP_HOST', 'localhost:8080');
        try {
            $p->redirect($to, $external);
        }
        catch (Pinoco_FlowControlHttpRedirect $ex) {
            $ex->respond($p);
            return $p->sent_headers->get(0);
        }
        $this->fail();
    }

    public function _urlModifier2($path, $renderable) {
        return 'http://other-host' . $path;
    }

    public function testRedirectWithUrlModifier()
    {
        $p = $this->testenv->create('/data/post');
        $p->request->server->set('HTTP_HOST', 'localhost:8080');
        $p->url_modifier = array($this, '_urlModifier2');
        try {
            $p->redirect('view');
            $this->fail();
        }
        catch (Pinoco_FlowControlHttpRedirect $ex) {
            $ex->respond($p);
            $this->assertEquals('Location: http://other-host/pub/data/view',
                $p->sent_headers->get(0));
        }
    }
}
