<?php
require_once dirname(__FILE__) . '/../src/Pinoco/_bootstrap.php';

class MethodProxyTest extends PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $callback = @create_function('$owner,$a,$b', 'return array($owner,$a,$b);');
        $p = new Pinoco_MethodProxy($callback, 0);
        $this->assertEquals(array(0, 1, 2), $p->call(array(1, 2)));
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertEquals(array(0, 1, 2), $p(1, 2));
        }
    }

    public function testBehindVarsClass()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->registerAsMethod('m', @create_function(
            '$owner,$a,$b', 'return array($owner->a,$owner->b,$a,$b);'));
        $this->assertEquals(array(1, 2, 3, 4), $v->m(3, 4));
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $m = $v->m;
            $this->assertEquals(array(1, 2, 3, 4), call_user_func($m, 3, 4));
        }
    }

    public function testUndefinedMethod()
    {
        $this->setExpectedException(
          'BadMethodCallException', 'The Vars object has no such method:'
        );
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->registerAsMethod('m', @create_function(
            '$owner,$a,$b', 'return array($owner->a,$owner->b,$a,$b);'));
        $v->a();
    }

    public function testUndefinedMethod2()
    {
        $this->setExpectedException(
          'BadMethodCallException', 'The Vars object has no such method:'
        );
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->registerAsMethod('m', @create_function(
            '$owner,$a,$b', 'return array($owner->a,$owner->b,$a,$b);'));
        $v->undefinedField();
    }

    public function testNoProxy()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        // This way can't pass owner object to method.
        $v->m = @create_function(
            '$owner,$a,$b', 'return array($owner,$a,$b);');
        $this->assertEquals(array(0, 1, 2), $v->m(0, 1, 2));
    }
}
