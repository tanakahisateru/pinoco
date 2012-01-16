<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class ListTest extends PHPUnit_Framework_TestCase
{
    public function testBasicIO()
    {
        $l = new Pinoco_List();
        $l->push(1);
        $this->assertEquals(1, $l->get(0));
        $this->assertNull($l->get(1));
    }
    
    public function testCount()
    {
        $l = new Pinoco_List();
        $l->push(1);
        $l->push(2);
        $l->push(3);
        $this->assertEquals(3, $l->count());
        $this->assertEquals(3, count($l));
    }
    
    public function testArrayAccess()
    {
        $l = new Pinoco_List();
        $l[0] = 0;
        $l[1] = 1;
        $this->assertEquals(0, $l[0]);
        $this->assertEquals(1, $l[1]);
        $this->assertEquals(2, $l->count());
    }
    
    public function testPadding()
    {
        $l = new Pinoco_List();
        $l[1] = 1;
        $this->assertNull($l[0]);
        $this->assertEquals(1, $l[1]);
        $this->assertEquals(2, $l->count());
    }
    
    public function testCreateFromArray()
    {
        $src = array(1, 2, 3);
        $l = Pinoco_List::fromArray($src);
        $src[0] = 10;
        $this->assertEquals(1, $l[0]);
        $l[1] = 20;
        $this->assertEquals(2, $src[1]);
        $src[] = 4;
        $this->assertEquals(3, $l->count());
    }
    
    public function testWrapArray()
    {
        $src = array(1, 2, 3);
        $l = Pinoco_List::wrap($src);
        $src[0] = 10;
        $this->assertEquals(10, $l[0]);
        $l[1] = 20;
        $this->assertEquals(20, $src[1]);
        $src[] = 4;
        $this->assertEquals(4, $l->count());
    }
    
    public function testDefault()
    {
        $l = new Pinoco_List();
        $l->push(1);
        $this->assertNull($l->get(1));
        $l->setDefault(-1);
        $this->assertSame(-1, $l->get(1));
        $this->assertSame(-2, $l->get(1, -2));
    }
    
    public function testPushPop()
    {
        $l = Pinoco_List::fromArray(array(1, 2));
        $this->assertEquals(2, $l->pop());
        $this->assertEquals(1, $l->count());
        $this->assertEquals(array(1), $l->toArray());
        $l->push(3);
        $this->assertEquals(2, $l->count());
        $this->assertEquals(array(1, 3), $l->toArray());
        $l->push(4, 5, 6);
        $this->assertEquals(array(1, 3, 4, 5, 6), $l->toArray());
    }
    
    public function testShiftUnshift()
    {
        $l = Pinoco_List::fromArray(array(1, 2));
        $this->assertEquals(1, $l->shift());
        $this->assertEquals(1, $l->count());
        $this->assertEquals(array(2), $l->toArray());
        $l->unshift(3);
        $this->assertEquals(2, $l->count());
        $this->assertEquals(array(3, 2), $l->toArray());
        $l->unshift(4, 5, 6);
        $this->assertEquals(array(6, 5, 4, 3, 2), $l->toArray());
    }
    
    public function testConcat()
    {
        $l = Pinoco_List::fromArray(array(1, 2));
        $l->concat(array(3, 4));
        $this->assertEquals(array(1, 2, 3, 4), $l->toArray());
        $l->concat(Pinoco_List::fromArray(array(5, 6)));
        $this->assertEquals(array(1, 2, 3, 4, 5, 6), $l->toArray());
        
        $l = Pinoco_List::fromArray(array(1, 2));
        $l->concat(array(3, 4), array(5, 6));
        $this->assertEquals(array(1, 2, 3, 4, 5, 6), $l->toArray());
    }
    
    public function testJoin()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals('1,2,3', $l->join());
    }
    
    public function testReverse()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals(array(3, 2, 1), $l->reverse()->toArray());
        $this->assertEquals(array(1, 2, 3), $l->toArray());
    }
    
    public function testSlice()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
        $this->assertEquals(array(2, 3, 4, 5), $l->slice(1)->toArray());
        $this->assertEquals(array(2, 3, 4), $l->slice(1, 3)->toArray());
        $this->assertEquals(array(1, 2, 3, 4, 5), $l->toArray());
    }
    
    public function testSplice()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
        $this->assertEquals(array(2), $l->splice(1, 1)->toArray());
        $this->assertEquals(array(1, 3, 4, 5), $l->toArray());
        
        $l = Pinoco_List::fromArray(array(1, 2, 3, 4, 5));
        $this->assertEquals(array(2, 3), $l->splice(1, 2, array(-1, -2, -3))->toArray());
        $this->assertEquals(array(1, -1, -2, -3, 4, 5), $l->toArray());
    }
    
    public function testInsert()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $l->insert(1, -1);
        $this->assertEquals(array(1, -1, 2, 3), $l->toArray());
    }
    
    public function testRemove()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $l->remove(1);
        $this->assertEquals(array(1, 3), $l->toArray());
        
        $l = Pinoco_List::fromArray(array(1, 2, 3, 4));
        $l->remove(1, 2);
        $this->assertEquals(array(1, 4), $l->toArray());
    }
    
    public function testIndex()
    {
        $l = Pinoco_List::fromArray(array(0, 1, 2, 3));
        $this->assertEquals(2, $l->index(2));
    }
    
    public function testToArray()
    {
        $name_mod = create_function('$orig', 'return sprintf("m_%03d", $orig);');
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals(array('m_0'=>1, 'm_1'=>2, 'm_2'=>3), $l->toArray('m_'));
        $this->assertEquals(array('m_0'=>1, 'm_1'=>2, 'm_2'=>3), $l->toArray('m_%d'));
        $this->assertEquals(array('m_000'=>1, 'm_001'=>2, 'm_002'=>3), $l->toArray($name_mod));
    }
    
    public function testReduce()
    {
        $reducer = create_function('$a,$b', 'return $a + $b;');
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals(6, $l->reduce($reducer));
        $this->assertEquals(10, $l->reduce($reducer, 4));
    }
    
    public function testMap()
    {
        $mapper = create_function('$a', 'return $a + 1;');
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals(array(2, 3, 4), $l->map($mapper)->toArray());
    }
    
    public function testFilter()
    {
        $filter = create_function('$a', 'return $a >= 2;');
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $this->assertEquals(array(2, 3), $l->filter($filter)->toArray());
    }
    
    public function testEach()
    {
        ob_start();
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $l->each('printf');
        $ob = ob_get_clean();
        $this->assertEquals('123', $ob);
    }
    
    public function testIteration()
    {
        $l = Pinoco_List::fromArray(array(1, 2, 3));
        $tmp = array();
        foreach($l as $e) {
            $tmp[] = $e;
        }
        $this->assertEquals(array(1, 2, 3), $tmp);
    }
    
    public function testToArrayRecurse()
    {
        $l = new Pinoco_List();
        $l->push(Pinoco_List::fromArray(array(1, 2)));
        $l->push(Pinoco_List::fromArray(array(3, 4)));
        $this->assertEquals(array(
            array(1, 2),
            array(3, 4),
        ), $l->toArrayRecurse());
        $tmp = $l->toArrayRecurse(0);
        $this->assertSame($l, $tmp);
        $tmp = $l->toArrayRecurse(1);
        $this->assertSame($l[0], $tmp[0]);
        $this->assertSame($l[1], $tmp[1]);
    }
    
    public function testExpressionAccess()
    {
        $obj = new stdClass;
        $obj->c = '1';
        $v = Pinoco_List::fromArray(array(
            0,
            array(
                'a'=>Pinoco_Vars::fromArray(array(
                    'b'=>$obj,
                )),
            ),
            2
        ));
        $this->assertEquals(0, $v->rget('0'));
        $this->assertEquals(1, $v->rget('1/a/b/c'));
        $this->assertEquals('none', $v->rget('1/a/b/d', 'none'));
        $this->assertEquals('none', $v->rget('2/a/b/c', 'none'));
    }
}
