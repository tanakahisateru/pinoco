<?php
require_once dirname(__FILE__) . '/../../src/Pinoco.php';

class LazyValueProxyTest extends PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = create_function('$owner', 'return "lazy value";');
        $p = new Pinoco_LazyValueProxy($fetcher);
        $this->assertEquals('lazy value', $p->fetch());
    }

    public function testMutableFetcherForDynamic()
    {
        $mutable_fetcher = create_function('$owner', 'global $ccc; return ++$ccc;');
        $p = new Pinoco_LazyValueProxy($mutable_fetcher);
        $this->assertEquals(1, $p->fetch());
        $this->assertEquals(2, $p->fetch());
    }

    public function testMutableFetcherForLazy()
    {
        $mutable_fetcher = create_function('$owner', 'global $ccc2; return ++$ccc2;');
        $p = new Pinoco_LazyValueProxy($mutable_fetcher, true);
        $this->assertEquals(1, $p->fetch());
        $this->assertEquals(1, $p->fetch());
    }

    public function testOwnerReference()
    {
        $o = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $fetcher = create_function('$owner', 'return $owner->a;');
        $p = new Pinoco_LazyValueProxy($fetcher);
        $this->assertEquals(1, $p->fetch($o));
    }

    public function testOwnerReferenceWithContext()
    {
        $o = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $fetcher = create_function('$owner,$a1,$a2', 'return $owner->b+$a1+$a2;');
        $p = new Pinoco_LazyValueProxy($fetcher, false, array(3, 4));
        $this->assertEquals(9, $p->fetch($o));
    }

    public function testWithHostObject()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $fetcher = create_function('$owner', 'return $owner->a;');
        $v->lazyprop = new Pinoco_LazyValueProxy($fetcher);
        $this->assertEquals(3, $v->keys()->count());
        $this->assertEquals(1, $v->lazyprop);
        $this->assertEquals(1, $v->lazyprop);
    }

    public function testBehindVarsClass()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->registerAsDynamic('c', create_function('$owner', 'return $owner->b;'));
        $v->registerAsLazy('d', create_function('$owner', 'global $ccc3; return ++$ccc3;'));
        $this->assertEquals(2, $v->c);
        $this->assertEquals(1, $v->d);
        $this->assertEquals(1, $v->d);
        $this->assertEquals(array('a'=>1, 'b'=>2, 'c'=>2, 'd'=>1), $v->toArray());
    }

    public function testMarkAsDirty()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->registerAsLazy('d', create_function('$owner', 'global $ccc3; return ++$ccc3;'));
        $this->assertEquals(1, $v->d);
        $this->assertEquals(1, $v->d);
        $v->markAsDirty('d');
        $this->assertEquals(2, $v->d);
    }
}
