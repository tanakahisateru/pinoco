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
 * Pagination object designed for PHPTAL
 *
 * <code>
 * $this->autolocal->pagination = new Pinoco_Pagination(
 *     function() {
 *         return Pinoco::instance()->db->query("SELECT count(id) as c FROM ...")->fetchOne()->c;
 *     },
 *     function($offset, $limit) {
 *         return Pinoco::instance()->db->query("SELECT * FROM ... LIMIT $offset, $limit")->fetchAll();
 *     },
 *     function($page) {
 *         return Pinoco::instance()->url('list?page=' . $page);
 *     },
 *     array(
 *         'elementsPerPage' => 20,
 *     )
 * );
 * $this->autolocal->pagination->page = 1;
 * </code>
 *
 * <code>
 * <tal:block tal:repeat="element pagination/data">
 *    ${element/prop}
 * </tal:block>
 * <div class="pagination"
 *     tal:define="prev pagination/prev; pages pagination/pages; next pagination/next">
 *     <!--! prev button -->
 *     <a href="" tal:condition="not:prev/current"
 *        tal:attributes="href prev/href">PREV</a>
 *     <span class="disabled" tal:condition="prev/current">PREV</span>
 *     <!--! page link buttons -->
 *     <tal:block tal:repeat="page pages">
 *         <tal:block tal:condition="not:page/padding">
 *             <a href="" tal:condition="not:page/current"
 *                tal:attributes="href page/href"
 *                tal:content="page/number">1</a>
 *             <span class="current" tal:condition="page/current"
 *                tal:content="page/number">1</span>
 *         </tal:block>
 *         <span tal:condition="page/padding">...</span>
 *     </tal:block>
 *     <!--! next button -->
 *     <a href="" tal:condition="next/enabled"
 *        tal:attributes="href next/href">NEXT</a>
 *     <span class="disabled" tal:condition="not:next/enabled">NEXT</span>
 * </div>
 * </code>
 *
 * @package Pinoco
 * @property integer $page
 * @property integer $elementsPerPage
 * @property integer $pagesAfterFirst
 * @property integer $pagesAroundCurrent
 * @property integer $pagesBeforeLast
 * @property-read integer $totalCount
 * @property-read integer $totalPages
 * @property-read mixed $data
 * @property-read Pinoco_List $pages
 * @property-read Pinoco_Vars $prev
 * @property-read Pinoco_Vars $next
 */
class Pinoco_Pagination extends Pinoco_DynamicVars {

    private $totalCountCallback;
    private $dataFetchCallback;
    private $urlFormatCallback;
    private $_currentPage;
    private $_elementsPerPage;
    private $_pagesAfterFirst;
    private $_pagesAroundCurrent;
    private $_pagesBeforeLast;
    
    private $_totalCount;
    private $_data;
    
    /**
     *
     */
    public function __construct($totalCountCallback, $dataFetchCallback,
        $urlFormatCallback, $options=array())
    {
        $this->totalCountCallback = $totalCountCallback;;
        $this->dataFetchCallback = $dataFetchCallback;
        $this->urlFormatCallback = $urlFormatCallback;
        $this->_currentPage = 1;
        $this->_elementsPerPage = 10;
        $this->_pagesAfterFirst = 0;
        $this->_pagesAroundCurrent = 1;
        $this->_pagesBeforeLast = 0;
        $this->_totalCount = null;
        $this->_data = null;
        foreach($options as $name=>$value) {
            if($this->has($name)) {
                $this->set($name, $value);
            }
        }
    }
    
    /**
     *
     */
    public function get_page()
    {
        return $this->_curentPage;
    }
    
    /**
     *
     */
    public function set_page($value)
    {
        $this->_curentPage = $value;
        $this->_data = null;
    }
    
    /**
     *
     */
    public function get_elementsPerPage()
    {
        return $this->_elementsPerPage;
    }
    
    /**
     *
     */
    public function set_elementsPerPage($value)
    {
        $this->_elementsPerPage = $value;
        $this->_data = null;
    }
    
    /**
     *
     */
    public function get_pagesAfterFirst()
    {
        return $this->_pagesAfterFirst;
    }
    
    /**
     *
     */
    public function set_pagesAfterFirst($value)
    {
        $this->_pagesAfterFirst = $value;
    }
    
    /**
     *
     */
    public function get_pagesAroundCurrent()
    {
        return $this->_pagesAroundCurrent;
    }
    
    /**
     *
     */
    public function set_pagesAroundCurrent($value)
    {
        $this->_pagesAroundCurrent = $value;
    }
    
    /**
     *
     */
    public function get_pagesBeforeLast()
    {
        return $this->_pagesBeforeLast;
    }
    
    /**
     *
     */
    public function set_pagesBeforeLast($value)
    {
        $this->_pagesBeforeLast = $value;
    }
    
    /**
     *
     */
    public function get_data()
    {
        if(is_null($this->_data)) {
            $this->_data = call_user_func(
                $this->dataFetchCallback,
                ($this->page - 1) * $this->elementsPerPage, // offset
                $this->elementsPerPage // limit
            );
        }
        return $this->_data;
    }
    
    /**
     *
     */
    public function get_totalCount()
    {
        if(is_null($this->_totalCount)) {
            $this->_totalCount = call_user_func($this->totalCountCallback);
        }
        return $this->_totalCount;
    }
    
    /**
     *
     */
    public function reset()
    {
        $this->_data = null;
        $this->_totalCount = null;
    }
    
    /**
     *
     */
    public function get_totalPages()
    {
        return max(1, ($this->totalCount - 1) / $this->elementsPerPage + 1);
    }
    
    /**
     *
     */
    public function get_pages()
    {
        // FIXME use pagesAfterFirst, pagesAroundCurrent and pagesBeforeLast
        $pages = Pinoco::newList();
        for($i = 1; $i <= $this->totalPages; $i++) {
            $pages->push(Pinoco::newVars(array(
                'padding' => false,
                'number'  => $i,
                'href'    => call_user_func($this->urlFormatCallback, $i),
                'current' => $i == $this->_currentPage,
            )));
        }
        return $pages;
    }
    
    /**
     *
     */
    public function get_prev()
    {
        if($this->_currentPage > 1) {
            return Pinoco::newVars(array(
                'number'  => $this->_currentPage - 1,
                'href'    => call_user_func($this->urlFormatCallback, $this->_currentPage - 1),
                'enabled' => true,
            ));
        }
        else {
            return Pinoco::newVars(array(
                'enabled' => false,
            ));
        }
    }
    
    /**
     *
     */
    public function get_next()
    {
        if($this->_currentPage < $this->totalPages) {
            return Pinoco::newVars(array(
                'number'  => $this->_currentPage + 1,
                'href'    => call_user_func($this->urlFormatCallback, $this->_currentPage + 1),
                'enabled' => true,
            ));
        }
        else {
            return Pinoco::newVars(array(
                'enabled' => false,
            ));
        }
    }
}
