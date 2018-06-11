<?php
require dirname(__FILE__) . '/../bootstrap.php';

class SiteHookTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Pinoco_TestEnvironment
     */
    private $testenv;

    public function setUp()
    {
        $basedir = dirname(dirname(dirname(__FILE__)));
        $this->testenv = Pinoco::testenv(
            $basedir,
            $basedir . '/_app'
        )->initBy(array($this, 'init'));
    }

    /**
     * @param Pinoco $pinoco
     */
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
