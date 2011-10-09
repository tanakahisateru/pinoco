<?php
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

class PinocoTest extends PHPUnit_Framework_TestCase
{
    private function pinoco($base, $path, $dispatcher='')
    {
        $project = dirname(dirname(dirname(__FILE__)));
        $pinoco = new Pinoco(
            $base,
            $dispatcher,
            $path,
            $project . '/sandbox/www',
            $project . '/sandbox/app'
        );
        return $pinoco;
    }
    
    public function testPathesUnderSiteRoot()
    {
        $p = $this->pinoco('/pub', '/page.html');
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
        $p = $this->pinoco('/pub', '/sub/page.html');
        $this->assertEquals('/pub/',             $p->url('/'));
        $this->assertEquals('/pub/sub/',         $p->url('./'));
        $this->assertEquals('/pub/',             $p->url('../'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('foo.html'));
        $this->assertEquals('/pub/sub/foo.html', $p->url('./foo.html'));
        $this->assertEquals('/pub/foo.html',     $p->url('../foo.html'));
    }
}
