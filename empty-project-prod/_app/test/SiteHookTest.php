<?php
require_once dirname(dirname(__FILE__)) . '/lib/Pinoco.php';

class SiteHookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $prefix = dirname(dirname(dirname(__FILE__)));
        $this->pinoco = Pinoco::testenv(
            $prefix, // basedir
            $prefix . '/_app' // sysdir
        )->config('config', 'config.ini');
    }
    
    public function testSiteRootGet()
    {
        $this->pinoco->testrun('/');
        // Do your assertions to: $this->pinoco
    }
}
