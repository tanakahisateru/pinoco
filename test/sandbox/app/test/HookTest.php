<?php
require_once dirname(__FILE__) . '/../../../../src/Pinoco.php';

if(function_exists('xdebug_disable')){ xdebug_disable(); }

class HookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $prefix = realpath(dirname(__FILE__) . '/../../');
        $this->pinoco = Pinoco::testenv(
            $prefix . '/www/basic',
            $prefix . '/app'
        )->config('cfg', 'config/main.ini')
         ->config('cfg', 'config/override.php')
         ->config('cfg', array('baz'=>300));
    }
    
    public function testSiteRootGet()
    {
        $p = $this->pinoco;
        $p->testrun('/');
        $this->assertEquals("Pinoco Test (/)", $p->autolocal->title);
    }
    
    public function testIndexHtmlGet()
    {
        $p = $this->pinoco;
        $p->testrun('/index.html');
        $this->assertEquals("Pinoco Test (/index.html)", $p->autolocal->title);
    }
    
    public function testSub2IndexHtmlGet()
    {
        $p = $this->pinoco;
        ob_start();
        $p->testrun('/sub2/index.html');
        ob_end_clean();
    }
    
    public function testSub2IndexPhpGet()
    {
        $p = $this->pinoco;
        ob_start();
        $p->testrun('/sub2/index.php');
        ob_end_clean();
    }
}
