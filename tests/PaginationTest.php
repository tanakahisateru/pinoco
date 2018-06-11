<?php
require_once dirname(__FILE__) . '/../src/Pinoco/_bootstrap.php';

class PaginationTest extends PHPUnit_Framework_TestCase
{
    private $total;

    public function mockFetch($pagination, $offset, $limit)
    {
        $head = min($this->total, $offset);
        $tail = min($this->total, $offset + $limit);
        return Pinoco::newList(range($head, $tail-1));
    }
    public function mockCount($pagination)
    {
        return $this->total;
    }
    public function mockFormatUrl($pagination, $page)
    {
        return $pagination->baseuri . (strpos($pagination->baseuri, '?') === false ? '?' : '&') . 'page=' . $page;
    }

    public function setUp()
    {
        $this->total = 0;
    }

    public function testPrevNext()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 20,
                'baseuri' => 'test/list',
            )
        );

        $this->assertEquals(6, $pn->totalPages);

        $pn->page = 1;
        $this->assertFalse($pn->prev->enabled);
        $this->assertTrue($pn->next->enabled);
        $this->assertEquals(2, $pn->next->number);
        $this->assertEquals('test/list?page=2', $pn->next->href);
        $this->assertEquals(20, $pn->data->count());
        $this->assertEquals(0, $pn->data[0]);

        $pn->page = 2;
        $this->assertTrue($pn->prev->enabled);
        $this->assertEquals(1, $pn->prev->number);
        $this->assertEquals('test/list?page=1', $pn->prev->href);
        $this->assertTrue($pn->next->enabled);
        $this->assertEquals(3, $pn->next->number);
        $this->assertEquals('test/list?page=3', $pn->next->href);
        $this->assertEquals(20, $pn->data->count());
        $this->assertEquals(20, $pn->data[0]);

        $pn->page = 6;
        $this->assertTrue($pn->prev->enabled);
        $this->assertEquals(5, $pn->prev->number);
        $this->assertEquals('test/list?page=5', $pn->prev->href);
        $this->assertFalse($pn->next->enabled);
        $this->assertEquals(19, $pn->data->count());
        $this->assertEquals(100, $pn->data[0]);
    }

    public function page2num($page)
    {
        return $page->padding ? null : $page->number;
    }

    public function currentNumber($lastval, $page)
    {
        return (!$page->padding && $page->current) ? $page->number : $lastval;
    }

    public function testPagesWithPadding()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 10,
                'pagesAfterFirst' => 1,
                'pagesAroundCurrent' => 1,
                'pagesBeforeLast' => 1,
                'baseuri' => 'test/list',
            )
        );

        $pn->page = 1;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,11,12), $pages);
        $this->assertEquals(1, $curr);

        $pn->page = 2;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,3,null,11,12), $pages);
        $this->assertEquals(2, $curr);

        $pn->page = 3;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,3,4,null,11,12), $pages);
        $this->assertEquals(3, $curr);

        $pn->page = 4;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,3,4,5,null,11,12), $pages);
        $this->assertEquals(4, $curr);

        $pn->page = 5;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,4,5,6,null,11,12), $pages);
        //$this->assertEquals(array(1,2,3,4,5,6,null,11,12), $pages);
        $this->assertEquals(5, $curr);

        $pn->page = 6;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,5,6,7,null,11,12), $pages);
        $this->assertEquals(6, $curr);

        $pn->page = 7;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,6,7,8,null,11,12), $pages);
        $this->assertEquals(7, $curr);

        $pn->page = 8;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,7,8,9,null,11,12), $pages);
        //$this->assertEquals(array(1,2,null,7,8,9,10,11,12), $pages);
        $this->assertEquals(8, $curr);

        $pn->page = 9;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,8,9,10,11,12), $pages);
        $this->assertEquals(9, $curr);

        $pn->page = 10;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,9,10,11,12), $pages);
        $this->assertEquals(10, $curr);

        $pn->page = 11;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,10,11,12), $pages);
        $this->assertEquals(11, $curr);

        $pn->page = 12;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,11,12), $pages);
        $this->assertEquals(12, $curr);
    }

    public function testPagesWithPadding2()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 10,
                'pagesAfterFirst' => 0,
                'pagesAroundCurrent' => 0,
                'pagesBeforeLast' => 0,
                'baseuri' => 'test/list',
            )
        );

        $pn->page = 1;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,12), $pages);
        $this->assertEquals(1, $curr);

        $pn->page = 2;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null,12), $pages);
        $this->assertEquals(2, $curr);

        $pn->page = 3;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,3,null,12), $pages);
        $this->assertEquals(3, $curr);

        $pn->page = 10;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,10,null,12), $pages);
        $this->assertEquals(10, $curr);

        $pn->page = 11;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,11,12), $pages);
        $this->assertEquals(11, $curr);

        $pn->page = 12;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,12), $pages);
        $this->assertEquals(12, $curr);
    }

    public function testPagesWithPadding3()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 10,
                'pagesAfterFirst' => 0,
                'pagesAroundCurrent' => 1,
                'pagesBeforeLast' => -1,
                'baseuri' => 'test/list',
            )
        );

        $pn->page = 1;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,null), $pages);
        $this->assertEquals(1, $curr);

        $pn->page = 2;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,3,null), $pages);
        $this->assertEquals(2, $curr);

        $pn->page = 12;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,null,11,12), $pages);
        $this->assertEquals(12, $curr);
    }

    public function testExpandAllPages()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 20,
                'pagesAroundCurrent' => -1,
                'baseuri' => 'test/list',
            )
        );

        $pn->page = 2;
        $pages = $pn->pages->map(array($this, 'page2num'))->toArray();
        $curr  = $pn->pages->reduce(array($this, 'currentNumber'));
        $this->assertEquals(array(1,2,3,4,5,6), $pages);
        $this->assertEquals(2, $curr);
    }

    public function testPageValidity()
    {
        $this->total = 119;
        $pn = new Pinoco_Pagination(
            array($this, 'mockCount'),
            array($this, 'mockFetch'),
            array($this, 'mockFormatUrl'),
            array(
                'elementsPerPage' => 20,
                'baseuri' => 'test/list',
            )
        );

        $this->assertEquals(6, $pn->totalPages);
        try {
            $pn->page = 0;
            $this->fail();
        } catch (InvalidArgumentException $ex) {
            $this->assertRegExp('/^Invalid number of page/', $ex->getMessage());
        }
        $pn->page = 6;
        $this->assertTrue($pn->isValidPage);
        $pn->page = 7;
        $this->assertFalse($pn->isValidPage);
    }
}
