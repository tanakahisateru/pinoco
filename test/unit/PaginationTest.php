<?php
require_once dirname(__FILE__) . '/../../src/Pinoco/_bootstrap.php';

class PaginationTest extends PHPUnit_Framework_TestCase
{
    private $total;
    
    public function mockFetch($offset, $limit)
    {
        $head = min($this->total, $offset);
        $tail = min($this->total, $offset + $limit);
        return Pinoco::newList(range($head, $tail-1));
    }
    public function mockCount()
    {
        return $this->total;
    }
    public function mockFormatUrl($page)
    {
        return 'list?page=' . $page;
    }
    
    public function setUp()
    {
        $this->total = 0;
    }
    
    public function testPagination()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 20
            )
        );
        $pn->page = 1;
        $this->assertFalse($pn->prev->enabled);
        $this->assertTrue($pn->next->enabled);
        $this->assertEquals(2, $pn->next->number);
        $this->assertEquals(6, $pn->pages->count());
        $this->assertEquals(20, $pn->data->count());
        $this->assertEquals(1, $pn->pages[0]->number);
        $this->assertTrue($pn->pages[0]->current);
        $this->assertEquals(2, $pn->pages[1]->number);
        $this->assertFalse($pn->pages[1]->current);
    }

}
