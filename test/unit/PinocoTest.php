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
    
    public function testPathesUnderSiteRoot()
    {
        $p = $this->testenv->create('/page.html');
        $this->assertEquals('/pub',       $p->baseuri);
        $this->assertEquals('/page.html', $p->path);
        $this->assertEquals('/pub/',                $p->url('/'));
        $this->assertEquals('/pub/',                $p->url('./'));
        $this->assertEquals('/pub/foo.html',        $p->url('/foo.html'));
        $this->assertEquals('/pub/foo.html',        $p->url('foo.html'));
        $this->assertEquals('/pub/foo.html',        $p->url('./foo.html'));
        $this->assertEquals('/pub/foo/bar.html',    $p->url('/foo/bar.html'));
        $this->assertEquals('/pub/foo/bar?a=1&b=2', $p->url('foo/bar?a=1&b=2'));
        $this->assertEquals('/pub/foo/bar?a=1&b=2', $p->url('./foo/bar?a=1&b=2'));
    }
    
    public function testPathesUnderSubDirectory()
    {
        $p = $this->testenv->create('/sub/page.html');
        $this->assertEquals('/pub/',             $p->url('/'));
        $this->assertEquals('/pub/sub/',         $p->url('./'));
        $this->assertEquals('/pub/',             $p->url('../'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('foo.html'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('./foo.html'));
        $this->assertEquals('/pub/foo.html',     $p->url('../foo.html'));
    }
}
