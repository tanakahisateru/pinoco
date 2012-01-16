<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class VarsTest extends PHPUnit_Framework_TestCase
{
    public function testBasicIO()
    {
        $v = new Pinoco_Vars();
        $v->set('foo', 'bar');
        $this->assertEquals('bar', $v->get('foo'));
    }
    
    public function testHas()
    {
        $v = new Pinoco_Vars();
        $v->set('foo', 'bar');
        $this->assertTrue($v->has('foo'));
        $this->assertFalse($v->has('xxx'));
    }
    
    public function testCount()
    {
        $v = new Pinoco_Vars();
        $v->set('foo', 'bar');
        $this->assertEquals(1, $v->count());
        $this->assertEquals(1, count($v));
    }

    public function testObjectAccess()
    {
        $v = new Pinoco_Vars();
        $v->foo = 'bar';
        $this->assertEquals('bar', $v->get('foo'));
        
        $v = new Pinoco_Vars();
        $v->set('foo', 'bar');
        $this->assertEquals('bar', $v->foo);
    }
    
    public function testArrayAccess()
    {
        $v = new Pinoco_Vars();
        $v['foo'] = 'bar';
        $this->assertEquals('bar', $v->get('foo'));
        
        $v = new Pinoco_Vars();
        $v->set('foo', 'bar');
        $this->assertEquals('bar', $v['foo']);
    }
    
    public function testCreateFromArray()
    {
        $src = array('a'=>1, 'b'=>2, 'c'=>3);
        $v = Pinoco_Vars::fromArray($src);
        $this->assertEquals(1, $v->a);
        $this->assertEquals(2, $v->b);
        $this->assertEquals(3, $v->c);
        
        $src['a'] = 10;
        $this->assertEquals(1, $v->a);
        
        $v->b = 20;
        $this->assertEquals(2, $src['b']);
        
        $src['d'] = 40;
        $this->assertFalse($v->has('d'));
    }
    
    public function testWrapArray()
    {
        $src = array('a'=>1, 'b'=>2, 'c'=>3);
        $v = Pinoco_Vars::wrap($src);
        $this->assertEquals(1, $v->a);
        $this->assertEquals(2, $v->b);
        $this->assertEquals(3, $v->c);
        
        $src['a'] = 10;
        $this->assertEquals(10, $v->a);
        
        $v->b = 20;
        $this->assertEquals(20, $src['b']);
        
        $src['d'] = 40;
        $this->assertTrue($v->has('d'));
    }
    
    public function testKeys()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2, 'c'=>3));
        $this->assertEquals(array('a','b','c'), $v->keys()->toArray());
    }
    
    public function testDefault()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $this->assertNull($v->get('c'));
        $v->setDefault('EMPTY');
        $this->assertSame('EMPTY', $v->get('c'));
        $this->assertSame(0, $v->get('c', 0), "as-hoc default");
    }
    
    public function testLoose()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->setLoose(true);
        $this->assertTrue($v->has('c'));
        $this->assertNull($v->c);
        $v->setLoose(false);
        $this->assertFalse($v->has('c'));
        $this->assertNull($v->c);
    }
    
    public function testRemove()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->remove('a');
        $this->assertFalse($v->has('a'));
        $this->assertEquals(1, $v->keys()->count());
        unset($v->b);
        $this->assertFalse($v->has('b'));
    }
    
    public function testIteration()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $tmp=array();
        foreach($v as $k=>$e) {
            $tmp[] = $k;
            $tmp[] = $e;
        }
        $this->assertEquals(array('a', 1, 'b', 2), $tmp);
    }
    
    public function testToArray()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $this->assertEquals(array('a'=>1, 'b'=>2),    $v->toArray());
        $this->assertEquals(array('a'=>1, 'c'=>null), $v->toArray(array('a','c')));
        $this->assertEquals(array('a'=>1, 'c'=>-1),   $v->toArray(array('a','c'), -1));
        $name_mod = create_function('$orig', 'return "m_" . $orig;');
        $this->assertEquals(array('m_a'=>1, 'm_b'=>2), $v->toArray(false, null, 'm_'));
        $this->assertEquals(array('m_a'=>1, 'm_b'=>2), $v->toArray(false, null, 'm_%s'));
        $this->assertEquals(array('m_a'=>1, 'm_b'=>2), $v->toArray(false, null, $name_mod));
    }
    
    public function testImport()
    {
        $v = Pinoco_Vars::fromArray(array('a'=>1, 'b'=>2));
        $v->import(array('c'=>3));
        $this->assertEquals(array('a'=>1, 'b'=>2, 'c'=>3), $v->toArray());
        $v->import(array('d'=>4, 'e'=>5), array('e'));
        $this->assertEquals(array('a'=>1, 'b'=>2, 'c'=>3, 'e'=>5), $v->toArray());
        $v->import(array('f'=>6, 'g'=>7), array('g', 'h'), -1);
        $this->assertEquals(array(
            'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1
        ), $v->toArray());
        $v->import(array('i'=>9), false, null, 'm_%s');
        $this->assertEquals(array(
            'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1, 'm_i'=>9
        ), $v->toArray());
        $name_mod = create_function('$orig', 'return "m_" . $orig;');
        $v->import(array('j'=>10), false, null, $name_mod);
        $this->assertEquals(array(
            'a'=>1, 'b'=>2, 'c'=>3, 'e'=>5, 'g'=>7, 'h'=>-1, 'm_i'=>9, 'm_j'=>10
        ), $v->toArray());
    }
    
    public function testToArrayRecurse()
    {
        $v = new Pinoco_Vars();
        $v->a = Pinoco_Vars::fromArray(array('aa'=>1, 'ab'=>2));
        $v->b = Pinoco_Vars::fromArray(array('ba'=>3, 'bb'=>4));
        $this->assertEquals(array(
            'a'=>array('aa'=>1, 'ab'=>2),
            'b'=>array('ba'=>3, 'bb'=>4),
        ), $v->toArrayRecurse());
        $tmp = $v->toArrayRecurse(0);
        $this->assertSame($v, $tmp);
        $tmp = $v->toArrayRecurse(1);
        $this->assertSame($v->a, $tmp['a']);
        $this->assertSame($v->b, $tmp['b']);
    }
    
    public function testNothing()
    {
        $v = Pinoco_NothingVars::instance();
        $this->assertTrue($v->has('a'));
        $this->assertSame($v, $v->get('a'));
        $this->assertSame($v, $v->get('a', 'default'));
        $this->assertSame($v, $v->get('a')->get('b'));
        $v->set('a', 1);
        $this->assertSame($v, $v->get('a'));
        $this->assertEquals(0, count($v));
        $this->assertEquals(0, count($v->keys()));
        $this->assertEquals(array(), $v->toArray());
        $this->assertEquals(array(), $v->toArrayRecurse());
        $this->assertEquals('', strval($v));
    }
    
    public function testExpressionAccess()
    {
        $obj = new stdClass;
        $obj->c = '1';
        $v = Pinoco_Vars::fromArray(array(
            'a'=>array(
                'b'=>Pinoco_List::fromArray(array(
                    0, $obj, 2
                )),
            ),
        ));
        $this->assertEquals(0, $v->rget('a/b/0'));
        $this->assertEquals(1, $v->rget('a/b/1/c'));
        $this->assertEquals('none', $v->rget('a/b/1/d', 'none'));
    }
}

