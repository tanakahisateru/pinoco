<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @category   Framework
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version    0.5.2
 * @link       https://github.com/tanakahisateru/pinoco
 * @filesource
 * @package    Pinoco
 */

/**
 * Iterator for Pinoco Variables or List
 * @package Pinoco
 * @internal
 */
class Pinoco_Iterator implements Iterator {
    private $_ref;
    private $_cur;
    public function __construct(&$ref) { $this->_ref = $ref; $this->rewind(); }
    public function rewind()  { reset($this->_ref); $this->_cur = each($this->_ref); }
    public function current() { return $this->_cur[1]; }
    public function key()     { return $this->_cur[0]; }
    public function next()    { $this->_cur = each($this->_ref); }
    public function valid()   { return $this->_cur !== FALSE; }
}


