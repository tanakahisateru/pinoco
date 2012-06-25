<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class PinocoTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $project = dirname(dirname(dirname(__FILE__)));
        $this->testenv = Pinoco::testenv(
            $project . '/test/sandbox/www',
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
}
