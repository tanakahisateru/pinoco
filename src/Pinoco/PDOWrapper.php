<?php
/**
 * Pinoco web site environment
 * It makes existing static web site dynamic transparently.
 *
 * PHP Version 5
 *
 * @category Framework
 * @package  Pinoco
 * @author   Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  0.1.1
 * @link     http://code.google.com/p/pinoco/
 * @filesource
 */

/**
 * PDO wrapper
 * @package Pinoco
 */
class Pinoco_PDOWrapper {
    private $_dsn;
    private $_un;
    private $_pw;
    private $_opts;
    private $_conn;
    
    function __construct($dsn, $un="", $pw="", $opts=array())
    {
        $this->_dsn = $dsn;
        $this->_un = $un;
        $this->_pw = $pw;
        $this->_opts = $opts;
        $this->_conn = NULL;
    }
    
    function get_connection()
    {
        if($this->_conn === NULL) {
            $this->_conn = new PDO($this->_dsn, $this->_un, $this->_pw, $this->_opts);
            $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->_conn;
    }
    
    function __call($name, $args)
    {
        return call_user_func_array(array($this->get_connection(), $name), $args);
    }
    
    function prepare($sql, $opts=array())
    {
        return new Pinoco_PDOStatementWrapper(
            $this->get_connection()->prepare($sql, $opts)
        );
    }
    
    function execute()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'exec'), $args);
    }
    
    function query($sql)
    {
        return new Pinoco_PDOStatementWrapper(
            $this->get_connection()->query($sql)
        );
    }
}

/**
 * PDO Statement wrapper
 * @package Pinoco
 */
class Pinoco_PDOStatementWrapper {
    private $_stmt;
    
    function __construct($stmt)
    {
        $this->_stmt = $stmt;
        //$this->_stmt->setFetchMode(PDO::FETCH_CLASS, "Pinoco_Vars", array());
    }
    
    function __call($name, $args)
    {
        return call_user_func_array(array($this->_stmt, $name), $args);
    }
    
    function execute()
    {
        if(func_num_args() == 0) {
            $args = array();
        }
        else if(func_num_args() == 1) {
            $a = func_get_arg(0);
            if($a instanceof Pinoco_Vars || $a instanceof Pinoco_List) {
                $args = $a->to_array();
            }
            else if(is_array($a)) {
                $args = $a;
            }
            else {
                $args = array($a);
            }
        }
        else {
            $args = func_get_args();
        }
        $this->_stmt->execute($args);
        return $this->rowCount();
    }
    
    function exec()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'execute'), $args);
    }
    
    function query()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'execute'), $args);
        return $this;
    }
    
    function fetch($orientation=PDO::FETCH_ORI_NEXT, $offset=0)
    {
        //return $this->_stmt->fetch(PDO::FETCH_CLASS, $orientation, $offset);
        $r = $this->_stmt->fetch(PDO::FETCH_ASSOC, $orientation, $offset);
        return $r !== FALSE ? Pinoco::newvars($r) : $r;
    }
    
    function fetchAll()
    {
        //return Pinoco::newlist($this->_stmt->fetchAll(PDO::FETCH_CLASS));
        $rs = Pinoco::newlist();
        $raw = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($raw as $r) {
            $rs->push(Pinoco::newvars($r));
        }
        return $rs;
    }
    
    function fetchOne()
    {
        $r = $this->fetch();
        try { $this->closeCursor(); } catch(PDOException $ex){ }
        return $r;
    }
}

