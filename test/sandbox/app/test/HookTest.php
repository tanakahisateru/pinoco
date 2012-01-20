<?php
require_once dirname(__FILE__) . '/../../../../src/Pinoco.php';

if(function_exists('xdebug_disable')){ xdebug_disable(); }

class HookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $prefix = realpath(dirname(__FILE__) . '/../../');
        $this->testenv = Pinoco::testenv(
            $prefix . '/www/basic',
            $prefix . '/app'
        )->initBy(array($this, 'init'));
    }
    
    public function init($pinoco)
    {
        $pinoco->config('cfg', 'config/main.ini');
        $pinoco->config('cfg', 'config/override.php');
        $pinoco->config('cfg', array('baz'=>300));
    }
    
    public function testSiteRootGet()
    {
        $p = $this->testenv->create('/');
        $p->run();
        $this->assertEquals("Pinoco Test (/)", $p->autolocal->title);
        $this->assertEquals(300, $p->cfg->baz);
    }
    
    public function testIndexHtmlGet()
    {
        $p = $this->testenv->create('/index.html');
        $p->run();
        $this->assertEquals("Pinoco Test (/index.html)", $p->autolocal->title);
    }
    
    public function testSub2IndexHtmlGet()
    {
        $p = $this->testenv->create('/sub2/index.html');
        $p->run();
    }
    
    public function testSub2IndexPhpGet()
    {
        $p = $this->testenv->create('/sub2/index.php');
        $p->run();
    }
    
    public function testLogoJpgGet()
    {
        $p = $this->testenv->create('/logo.jpg');
        $p->run();
    }
}
