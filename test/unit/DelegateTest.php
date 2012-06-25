<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class DelegateTest extends PHPUnit_Framework_TestCase
{
    public function testPlainDelegator()
    {
        $delegatee = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $delegator = new Pinoco_Delegate($delegatee);
        $this->assertTrue($delegator->has('a'));
        $this->assertTrue(isset($delegator->a));
        $this->assertEquals(1, $delegator->a);
        $this->assertEquals(2, $delegator->b);
        $this->assertFalse($delegator->has('c'));
        $this->assertFalse(isset($delegator->c));
    }

    public function testNoDelegatee()
    {
        $project = dirname(dirname(dirname(__FILE__)));
        Pinoco::testenv(
            $project . '/test/sandbox/www',
            $project . '/test/sandbox/app',
            '/pub'
        )->create('/foo')->run();
        $delegator = new Pinoco_Delegate();
        $this->assertEquals('/foo', $delegator->path);
    }

    public function testInheritedDelegator()
    {
        $delegatee = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $delegator = new DelegateMock($delegatee);
        $this->assertEquals(100, $delegator->a);
        $this->assertEquals(100, $delegator->getA());
        $this->assertEquals(2, $delegator->b);
        $this->assertEquals(2, $delegator->getB());
        $this->assertTrue(isset($delegator->b));
        $this->assertTrue(isset($delegator->c));
        $delegator->b = 20;
        $this->assertEquals(20, $delegatee->b);
    }
}

class DelegateMock extends Pinoco_Delegate
{
    public $a = 100;
    public $c = 300;

    public function __construct($delegatee)
    {
        parent::__construct($delegatee);
    }

    public function getA() {
        return $this->a;
    }

    public function getB() {
        return $this->b;
    }
}
