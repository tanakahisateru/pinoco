<?php
require_once dirname(__FILE__) . '/../src/Pinoco/_bootstrap.php';

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidateArray()
    {
        $testee = array(
            'foo' => "",
            'bar' => "123",
        );
        $v = new Pinoco_Validator($testee);
        $v->check('foo')->is('empty');
        $v->check('bar')->is('not-empty');
        $this->assertTrue($v->valid);
    }

    public function testValidateObject()
    {
        $testee = new stdClass();
        $testee->foo = "";
        $testee->bar = "123";
        $v = new Pinoco_Validator($testee);
        $v->check('foo')->is('empty');
        $v->check('bar')->is('not-empty');
        $this->assertTrue($v->valid);
    }

    public function testConstantTest()
    {
        $testee = array(
            'foo' => 1,
            'bar' => 2,
        );
        $v = new Pinoco_Validator($testee);
        $v->check('foo')->is('pass');
        $v->check('bar')->is('fail');
        $this->assertTrue($v->result->foo->valid);
        $this->assertFalse($v->result->bar->valid);
    }

    public function testEmptyTest()
    {
        $testee = array(
            'foo' => "0",
            'bar' => 0,
            'baz' => false,
        );
        $v = new Pinoco_Validator($testee);
        $v->check('foo')->is('not-empty');
        $v->check('bar')->is('not-empty');
        $v->check('baz')->is('not-empty');
        $this->assertTrue($v->valid); // Zero is not empty
    }

    public function testErrorMessage()
    {
        $testee = array(
            'foo' => 1,
            'bar' => "",
            'baz' => "",
        );
        $v = new Pinoco_Validator($testee, array(
            'not-empty' => "oops"
        ));
        $v->check('foo')->is('empty');  // default
        $v->check('bar')->is('not-empty');  // custom
        $v->check('baz')->is('not-empty', "fill baz"); // ad-hoc
        $this->assertEquals(3, $v->errors->keys()->count());
        $this->assertEquals("Leave as empty.", $v->result->foo->message);
        $this->assertEquals("oops", $v->result->bar->message);
        $this->assertEquals("fill baz", $v->result->baz->message);
    }

    public function testUncheck()
    {
        $v = new Pinoco_Validator(array(
            'foo' => ""
        ));
        $v->check('foo')->is('not-empty');
        $this->assertEquals(1, $v->result->count());
        $this->assertFalse($v->valid);
        $v->uncheck('foo');
        $this->assertEquals(0, $v->result->count());
        $this->assertTrue($v->valid);
    }

    public function testBuiltInTests()
    {
        $v = new Pinoco_Validator(array('foo' => "1234"));
        $this->assertFalse($v->check('foo')->is('max-length 3')->valid);
        $this->assertEquals("In 3 letters.", $v->result->foo->message);

        $v = new Pinoco_Validator(array('foo' => "1234"));
        $this->assertFalse($v->check('foo')->is('min-length 5')->valid);
        $this->assertEquals("At least 5 letters.", $v->result->foo->message);

        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertFalse($v->check('foo')->is('in 2,3,4')->valid);
        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertTrue($v->check('foo')->is('in 1,2,3')->valid);

        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertFalse($v->check('foo')->is('not-in 1,2,3')->valid);
        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertTrue($v->check('foo')->is('not-in 2,3,4')->valid);

        $v = new Pinoco_Validator(array('foo' => "one"));
        $this->assertFalse($v->check('foo')->is('numeric')->valid);

        $v = new Pinoco_Validator(array('foo' => "1.5"));
        $this->assertFalse($v->check('foo')->is('integer')->valid);

        $v = new Pinoco_Validator(array('foo' => "a123"));
        $this->assertFalse($v->check('foo')->is('alpha')->valid);

        $v = new Pinoco_Validator(array('foo' => "a123-"));
        $this->assertFalse($v->check('foo')->is('alpha-numeric')->valid);

        $v = new Pinoco_Validator(array('foo' => array()));
        $this->assertTrue($v->check('foo')->is('array')->valid);
        $v = new Pinoco_Validator(array('foo' => new Pinoco_List()));
        $this->assertTrue($v->check('foo')->is('array')->valid);

        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertFalse($v->check('foo')->is('== 2')->valid);

        $v = new Pinoco_Validator(array('foo' => 1));
        $this->assertFalse($v->check('foo')->is('!= 1')->valid);

        $v = new Pinoco_Validator(array('foo' => 2));
        $this->assertFalse($v->check('foo')->is('> 2')->valid);
        $v = new Pinoco_Validator(array('foo' => 2));
        $this->assertFalse($v->check('foo')->is('>= 3')->valid);
        $v = new Pinoco_Validator(array('foo' => 2));
        $this->assertFalse($v->check('foo')->is('< 2')->valid);
        $v = new Pinoco_Validator(array('foo' => 2));
        $this->assertFalse($v->check('foo')->is('<= 1')->valid);

        $v = new Pinoco_Validator(array('foo' => "abc"));
        $this->assertFalse($v->check('foo')->is('match /cd/')->valid);
        $v = new Pinoco_Validator(array('foo' => "abc"));
        $this->assertTrue($v->check('foo')->is('match /ab/')->valid);

        $v = new Pinoco_Validator(array('foo' => "abc"));
        $this->assertFalse($v->check('foo')->is('not-match /ab/')->valid);
        $v = new Pinoco_Validator(array('foo' => "abc"));
        $this->assertTrue($v->check('foo')->is('not-match /cd/')->valid);

        $v = new Pinoco_Validator(array('foo' => "foo@bar"));
        $this->assertTrue($v->check('foo')->is('email')->valid);

        $v = new Pinoco_Validator(array('foo' => "http://foo/bar"));
        $this->assertTrue($v->check('foo')->is('url')->valid);
    }

    public function testTestSequence()
    {
        $v = new Pinoco_Validator(array('foo' => "abc"));
        $v->check('foo')->is('not-empty')->is('numeric')->is('integer');
        $this->assertFalse($v->result->foo->valid);
        $this->assertEquals('numeric', $v->result->foo->test);
    }

    public function testAllowEmpty()
    {
        $v = new Pinoco_Validator(array('foo' => ""));
        $v->check('foo')->is('numeric');
        $this->assertTrue($v->valid);
    }

    public function testExtendingTests()
    {
        $next_number = @create_function('$v,$p', 'return $v == $p+1;');
        $v = new Pinoco_Validator(array('foo' => 2));
        $v->defineValidityTest('next-number', $next_number, 'xxx');
        $v->check('foo')->is('next-number 1');
        $this->assertTrue($v->valid);
    }

    public function testExtendingMessage()
    {
        $next_number = @create_function('$v,$p', 'return $v == $p+1;');
        $v = new Pinoco_Validator(array('foo' => 2));
        $v->defineValidityTest('next-number', $next_number, 'xxx');
        $v->check('foo')->is('next-number 1');

        // TODO extract test for recheck
        $v->recheck('foo', 'FOO_FIELD')->is(
            'next-number 3',
            '{label} should be {param}+1 but {value}'
        );
        $this->assertFalse($v->valid);
        $this->assertEquals(
            'FOO_FIELD should be 3+1 but 2',
            $v->result->foo->message
        );

        $func_msg_tmpl = @create_function(
            '$param,$value,$label',
            'return $param.$value.$label;'
        );
        $v->recheck('foo', 'FOO_FIELD')->is('next-number 3', $func_msg_tmpl);
        $this->assertFalse($v->valid);
        $this->assertEquals('32FOO_FIELD', $v->result->foo->message);
    }

    public function testFilterFunction()
    {
        $testee = array(
            'foo' => " abc ",
            'bar' => "def"
        );
        $v = new Pinoco_Validator($testee);
        $this->assertEquals(" abc ", $v->check('foo')->value);
        $this->assertFalse($v->check('foo')->is('max-length 3')->valid);

        $this->assertEquals("abc", $v->recheck('foo')->filter('trim')->value);
        $this->assertTrue($v->recheck('foo')->filter('trim')->is('max-length 3')->valid);

        $this->assertEquals("abc", $v->recheck('foo')->filter(array($this, 'filterTrim'))->value);
        $this->assertTrue($v->recheck('foo')->filter(array($this, 'filterTrim'))->is('max-length 3')->valid);

        $this->assertEquals("ABC", $v->recheck('foo')->filter('trim')->is('max-length 3')->filter('strtoupper')->value);
        $this->assertEquals(array('foo' => "ABC"), $v->values->toArray());
        $v->check('bar');
        $this->assertEquals(array('foo' => "ABC", 'bar' => "def"), $v->values->toArray());
    }

    public function testExtendingNamedFilter()
    {
        $add_filter = @create_function('$v,$p', 'return $v + $p;');
        $testee = array(
            'foo' => 2,
        );
        $v = new Pinoco_Validator($testee);
        $v->defineFilter('add', $add_filter);
        $this->assertEquals(3, $v->check('foo')->filter('add 1')->value);
    }

    public function testEmptyResult()
    {
        $form = Pinoco_Validator::emptyResult();
        $this->assertTrue($form->foo->valid);
        $this->assertFalse($form->foo->invalid);
        $this->assertSame(null, $form->foo->value);
        $form = Pinoco_Validator::emptyResult(array('foo' => "init"));
        $this->assertEquals('init', $form->foo->value);
        $this->assertEquals(null, $form->bar->value);
    }

    public function testArrayTest()
    {
        $v = new Pinoco_Validator(array('foo' => array("abc", "def")));
        $v->recheck('foo')->all('numeric');
        $this->assertFalse($v->result->foo->valid);
        $v->recheck('foo')->all('integer');
        $this->assertFalse($v->result->foo->valid);
        $v->recheck('foo')->all('alpha');
        $this->assertTrue($v->result->foo->valid);
        $v->recheck('foo')->is('array')->all('not-empty')->any('in abc,ghi');
        $this->assertTrue($v->result->foo->valid);
        $v->recheck('foo')->all(
            array($this, 'isAlphabetical')
        )->any(
            array($this, 'isIn_def_ghi')
        );
        $this->assertTrue($v->result->foo->valid);

        $v = new Pinoco_Validator(array('foo' => Pinoco_List::fromArray(array("abc", "def"))));
        $v->check('foo')->is('array')->all('alpha')->any('in def,ghi');
        $this->assertTrue($v->result->foo->valid);

        $v = new Pinoco_Validator(array('foo' => null));
        $v->recheck('foo')->all('numeric');
        $this->assertFalse($v->result->foo->valid);

        $v = new Pinoco_Validator(array());
        $v->recheck('foo')->all('numeric');
        $this->assertFalse($v->result->foo->valid);

        $v = new Pinoco_Validator(array('foo' => array()));
        $v->recheck('foo')->all('numeric');
        $this->assertTrue($v->result->foo->valid);
    }

    public function testArrayFilter()
    {
        $v = new Pinoco_Validator(array('foo' => array(" abc ", " def ")));
        $v->recheck('foo')->map('trim');
        $this->assertEquals(array("abc","def"), $v->result->foo->value);

        $v->recheck('foo')->map(array($this, 'filterTrim'));
        $this->assertEquals(array("abc","def"), $v->result->foo->value);

        $v = new Pinoco_Validator(array('foo' => Pinoco_List::fromArray(array(" abc ", " def "))));
        $v->recheck('foo')->map('trim');
        $this->assertEquals(Pinoco_List::fromArray(array("abc","def")), $v->result->foo->value);

        $v->recheck('foo')->map(array($this, 'filterTrim'));
        $this->assertEquals(Pinoco_List::fromArray(array("abc","def")), $v->result->foo->value);
    }

    public function filterTrim($str)
    {
        return trim($str);
    }

    public function isAlphabetical($str)
    {
        return preg_match('/^[A-Za-z_]*$/', $str) > 0;
    }

    public function isIn_def_ghi($str)
    {
        return in_array($str, array('def', 'ghi'));
    }
}
