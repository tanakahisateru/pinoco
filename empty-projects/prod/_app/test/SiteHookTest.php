<?php
// Use _bootstrap.php in Pinoco package to run PHPUnit.
// $ phpunit --bootstrap _app/lib/Pinoco/_bootstrap.php _app/test/

class SiteHookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $basedir = dirname(dirname(dirname(__FILE__)));
        $this->testenv = Pinoco::testenv(
            $basedir,
            $basedir . '/_app'
        )->initBy(array($this, 'init'));
    }
    
    public function init($pinoco)
    {
        $pinoco->config('config', 'config.ini');
    }
    
    public function testSiteRootGet()
    {
        $pinoco = $this->testenv->create('/');
        $pinoco->run();
        // Do your assertions to: $pinoco
    }
}
