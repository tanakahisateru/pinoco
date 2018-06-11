<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * Pagination object designed for PHPTAL.
 * This object is independent from RDBMS. You can use it with any data source.
 *
 * <code>
 * $pagination = new Pinoco_Pagination(
 *     // How many elements?
 *     function($pagination) {
 *         return $pagination->db->prepare(
 *             "SELECT count(id) as c FROM ..."
 *         )->query()->fetchOne()->c;
 *     },
 *     // What to be shown?
 *     function($pagination, $offset, $limit) {
 *         return $pagination->db->prepare(
 *             "SELECT * FROM ... LIMIT $offset, $limit"
 *         )->query()->fetchAll();
 *     },
 *     // How the page number is formatted in navigation?
 *     function($pagination, $page) {
 *         return 'list' . ($page > 1 ? '?page=' . $page : '');
 *     },
 *     array(
 *         'elementsPerPage' => 20,
 *         'db' => $db, // you can pass any custom property to pagination.
 *     )
 * );
 * $pagination->page = 1;
 * if (!$pagination->isValidPage) { $this->notfound(); }
 * </code>
 *
 * PHPTAL example
 * <code>
 * <tal:block tal:repeat="element pagination/data">
 *    ${element/prop}
 * </tal:block>
 * <div class="pagination"
 *     tal:define="prev pagination/prev; pages pagination/pages; next pagination/next">
 *     <!--! prev button -->
 *     <a href="" tal:condition="prev/enabled"
 *        tal:attributes="href url:${prev/href}">PREV</a>
 *     <span class="disabled" tal:condition="not:prev/enabled">PREV</span>
 *     <!--! page link buttons -->
 *     <tal:block tal:repeat="page pages">
 *         <tal:block tal:condition="not:page/padding">
 *             <a href="" tal:condition="not:page/current"
 *                tal:attributes="href url:${page/href}"
 *                tal:content="page/number">1</a>
 *             <span class="current" tal:condition="page/current"
 *                tal:content="page/number">1</span>
 *         </tal:block>
 *         <span tal:condition="page/padding">...</span>
 *     </tal:block>
 *     <!--! next button -->
 *     <a href="" tal:condition="next/enabled"
 *        tal:attributes="href url:${next/href}">NEXT</a>
 *     <span class="disabled" tal:condition="not:next/enabled">NEXT</span>
 * </div>
 * </code>
 *
 * @package Pinoco
 * @property integer $page The page number that starts with 1. (not 0!)
 * @property integer $elementsPerPage Amount of data shown in single page.
 * @property integer $pagesAfterFirst How many buttons after the first page. (-1: hides first page)
 * @property integer $pagesAroundCurrent How many buttons around current page. (-1: expand all pages)
 * @property integer $pagesBeforeLast How many buttons before the last page. (-1: hides last page)
 * @property-read integer $totalCount Total number of elements.
 * @property-read integer $totalPages Total number of pages.
 * @property-read mixed $data Elements in paginated range.
 * @property-read Pinoco_List $pages Navigation information of each page buttons.
 * @property-read Pinoco_Vars $prev Navigation information of the prev button.
 * @property-read Pinoco_Vars $next Navigation information of the prev button.
 */
class Pinoco_Pagination extends Pinoco_DynamicVars
{
    private $totalCountCallable;
    private $dataFetchCallable;
    private $urlFormatCallable;
    private $_currentPage;
    private $_elementsPerPage;
    private $_pagesAfterFirst;
    private $_pagesAroundCurrent;
    private $_pagesBeforeLast;

    private $_totalCount;
    private $_data;

    /**
     * Creates pagination object from user codes.
     *
     * @param callable $totalCountCallable
     * @param callable $dataFetchCallable
     * @param callable $urlFormatCallable
     * @param array $options
     */
    public function __construct(
        $totalCountCallable,
        $dataFetchCallable,
        $urlFormatCallable,
        $options = array()
    ) {
        parent::__construct();
        $this->totalCountCallable = $totalCountCallable;
        $this->dataFetchCallable = $dataFetchCallable;
        $this->urlFormatCallable = $urlFormatCallable;
        $this->_currentPage = 1;
        $this->_elementsPerPage = 10;
        $this->_pagesAfterFirst = 0;
        $this->_pagesAroundCurrent = 1;
        $this->_pagesBeforeLast = 0;
        $this->_totalCount = null;
        $this->_data = null;
        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function get_page()
    {
        return $this->_currentPage;
    }
    public function set_page($value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Invalid number of page:' . $value);
        }
        if ($value != $this->_currentPage) {
            $this->_data = null;
        }
        $this->_currentPage = $value;
    }

    public function get_isValidPage()
    {
        return $this->page <= $this->totalPages;
    }

    public function get_elementsPerPage()
    {
        return $this->_elementsPerPage;
    }
    public function set_elementsPerPage($value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Invalid number of elements:' . $value);
        }
        $this->_elementsPerPage = $value;
        $this->_data = null;
    }

    public function get_pagesAfterFirst()
    {
        return $this->_pagesAfterFirst;
    }
    public function set_pagesAfterFirst($value)
    {
        if ($value < -1) {
            throw new InvalidArgumentException('Invalid number of links:' . $value);
        }
        $this->_pagesAfterFirst = $value;
    }

    public function get_pagesAroundCurrent()
    {
        return $this->_pagesAroundCurrent;
    }
    public function set_pagesAroundCurrent($value)
    {
        if ($value < -1) {
            throw new InvalidArgumentException('Invalid number of links:' . $value);
        }
        $this->_pagesAroundCurrent = $value;
    }

    public function get_pagesBeforeLast()
    {
        return $this->_pagesBeforeLast;
    }
    public function set_pagesBeforeLast($value)
    {
        if ($value < -1) {
            throw new InvalidArgumentException('Invalid number of links:' . $value);
        }
        $this->_pagesBeforeLast = $value;
    }

    /**
     * Total number of elements.
     *
     * @return integer
     */
    public function get_totalCount()
    {
        if (is_null($this->_totalCount)) {
            $this->_totalCount = call_user_func($this->totalCountCallable, $this);
        }
        return $this->_totalCount;
    }

    /**
     * Elements in paginated range.
     * The fetched result is cached before changing current page.
     *
     * @return mixed
     */
    public function get_data()
    {
        if (is_null($this->_data)) {
            $this->_data = call_user_func(
                $this->dataFetchCallable,
                $this,
                ($this->page - 1) * $this->elementsPerPage, // offset
                $this->elementsPerPage // limit
            );
        }
        return $this->_data;
    }

    /**
     * Force to clear cached data.
     *
     * @return void
     */
    public function reset()
    {
        $this->_data = null;
        $this->_totalCount = null;
    }

    /**
     * Total number of pages.
     *
     * @return integer
     */
    public function get_totalPages()
    {
        return max(1, intval(($this->totalCount - 1) / $this->elementsPerPage) + 1);
    }

    /**
     * Navigation information of each page buttons.
     *
     * @return Pinoco_List
     */
    public function get_pages()
    {
        $pages = Pinoco::newList();
        $leftpad = false;
        $rightpad = false;
        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($this->_pagesAroundCurrent >= 0) {
                $skipped = false;
                if (!$leftpad && $i > 1 + $this->_pagesAfterFirst) {
                    $leftpad = true;
                    if (abs($i - $this->_currentPage) > $this->_pagesAroundCurrent) {
                        $skipped = true;
                        $skipto = max($i+1, $this->_currentPage - $this->_pagesAroundCurrent);
                    }
                }
                if ($leftpad && !$rightpad && $i > $this->_currentPage + $this->_pagesAroundCurrent) {
                    $rightpad = true;
                    if ($i < $this->totalPages - $this->_pagesBeforeLast) {
                        $skipped = true;
                        $skipto = max($i, $this->totalPages - $this->_pagesBeforeLast);
                    }
                }
                if ($skipped && isset($skipto)) {
                    $pages->push(Pinoco::newVars(array('padding' => true)));
                    $i = $skipto - 1;
                    continue;
                }
            }
            $pages->push(Pinoco::newVars(array(
                'padding' => false,
                'number'  => $i,
                'href'    => call_user_func($this->urlFormatCallable, $this, $i),
                'current' => $i == $this->_currentPage,
            )));
        }
        return $pages;
    }

    /**
     * Navigation information of the prev button.
     *
     * @return Pinoco_Vars
     */
    public function get_prev()
    {
        if ($this->_currentPage > 1) {
            return Pinoco::newVars(array(
                'enabled' => true,
                'number'  => $this->_currentPage - 1,
                'href'    => call_user_func($this->urlFormatCallable, $this, $this->_currentPage - 1),
            ));
        } else {
            return Pinoco::newVars(array(
                'enabled' => false,
            ));
        }
    }

    /**
     * Navigation information of the next button.
     *
     * @return Pinoco_Vars
     */
    public function get_next()
    {
        if ($this->_currentPage < $this->totalPages) {
            return Pinoco::newVars(array(
                'enabled' => true,
                'number'  => $this->_currentPage + 1,
                'href'    => call_user_func($this->urlFormatCallable, $this, $this->_currentPage + 1),
            ));
        } else {
            return Pinoco::newVars(array(
                'enabled' => false,
            ));
        }
    }
}
