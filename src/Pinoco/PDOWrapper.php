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
 * @version    0.5.0
 * @link       https://github.com/tanakahisateru/pinoco
 * @filesource
 * @package    Pinoco
 */

/**
 */
require_once dirname(__FILE__) . '/VarsList.php';

/**
 * PDOWrapper provides extra methods to PDO.
 * Of course you can use also PDO functions.
 * @package Pinoco
 * @property-read PDO $connection
 * @property mixed $afterConnection
 * @method bool beginTransaction()
 * @method bool commit()
 * @method mixed errorCode()
 * @method array errorInfo()
 * @method int exec() exec(string $statement)
 * @method mixed getAttribute() getAttribute(int $attribute)
 * @method array getAvailableDrivers()
 * @method bool inTransaction()
 * @method string lastInsertId() lastInsertId([ string $name = NULL ])
 * @method string quote() quote( string $string [, int $parameter_type = PDO::PARAM_STR ] )
 * @method bool rollBack()
 * @method bool setAttribute() setAttribute( int $attribute , mixed $value )
 */
class Pinoco_PDOWrapper {
    private $_dsn;
    private $_un;
    private $_pw;
    private $_opts;
    private $_conn;
    private $_after_connection;
    
    /**
     * Wrapped PDO factory
     * @param string $dsn
     * @param string $un
     * @param string $pw
     * @param array $opts
     * @return Pinoco_PDOWrapper
     */
    public static function newInstance($dsn, $un="", $pw="", $opts=array())
    {
        return new Pinoco_PDOWrapper($dsn, $un, $pw, $opts);
    }
    
    /**
     * @param string $dsn
     * @param string $un
     * @param string $pw
     * @param array $opts
     */
    public function __construct($dsn, $un="", $pw="", $opts=array())
    {
        $this->_dsn = $dsn;
        $this->_un = $un;
        $this->_pw = $pw;
        $this->_opts = $opts;
        $this->_conn = NULL;
        $this->_after_connection = false;
    }
    
    /**
     * @return mixed
     */
    public function getAfterConnection()
    {
        return $this->_after_connection;
    }
    
    /**
     * @param mixed $after_connection
     */
    public function setAfterConnection($after_connection)
    {
        $this->_after_connection = $after_connection;
    }
    
    /**
     * @return PDO
     */
    public function getConnection()
    {
        if($this->_conn === NULL) {
            $this->_conn = new PDO($this->_dsn, $this->_un, $this->_pw, $this->_opts);
            $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if($this->_after_connection) {
                if(is_callable($this->_after_connection)) {
                    call_user_func($this->_after_connection, $this);
                }
                else {
                    $this->execute($this->_after_connection);
                }
            }
        }
        return $this->_conn;
    }
    
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->getConnection(), $name), $args);
    }
    
    /**
     * This method provides wrapped prepared statement.
     * @param string $sql
     * @param array $opts
     * @return Pinoco_PDOStatementWrapper
     */
    public function prepare($sql, $opts=array())
    {
        return new Pinoco_PDOStatementWrapper(
            $this->getConnection()->prepare($sql, $opts)
        );
    }
    
    /**
     * Alias to exec
     * @param mixed $args...
     * @return int
     */
    public function execute(/*[$args[, ...]]*/)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'exec'), $args);
    }
    
    /**
     * This method provides wrapped statement already query sent.
     * @param string $sql
     * @return Pinoco_PDOStatementWrapper
     */
    public function query($sql)
    {
        return new Pinoco_PDOStatementWrapper(
            $this->getConnection()->query($sql)
        );
    }
}

/**
 * PDO Statement wrapper overrides PDO statement object.
 * You can use also native functions.
 * @package Pinoco
 * @property-read string $queryString
 * @method bool bindColumn() bindColumn( mixed $column , mixed &$param [, int $type [, int $maxlen [, mixed $driverdata ]]] )
 * @method bool bindParam() bindParam( mixed $parameter , mixed &$variable [, int $data_type = PDO::PARAM_STR [, int $length [, mixed $driver_options ]]] )
 * @method bool bindValue() bindValue( mixed $parameter , mixed $value [, int $data_type = PDO::PARAM_STR ] )
 * @method bool closeCursor()
 * @method int columnCount()
 * @method bool debugDumpParams()
 * @method string errorCode()
 * @method array errorInfo()
 * @method string fetchColumn() fetchColumn([ int $column_number = 0 ] )
 * @method mixed fetchObject() fetchObject([ string $class_name = "stdClass" [, array $ctor_args ]] )
 * @method mixed getAttribute() getAttribute( int $attribute )
 * @method array getColumnMeta() getColumnMeta( int $column )
 * @method bool nextRowset()
 * @method int rowCount()
 * @method bool setAttribute() setAttribute( int $attribute , mixed $value )
 * @method bool setFetchMode() setFetchMode( int $mode )
 */
class Pinoco_PDOStatementWrapper {
    private $_stmt;
    
    /**
     * @param PDOStatement $stmt
     */
    public function __construct($stmt)
    {
        $this->_stmt = $stmt;
        //$this->_stmt->setFetchMode(PDO::FETCH_CLASS, "Pinoco_Vars", array());
    }
    
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->_stmt, $name), $args);
    }
    
    /**
     * Executes prepared query with parameters.
     *   No arguments:          no-params.
     *   Single argument:
     *     array or array like: expanded as params. (both of map and seq)
     *     array incompatible:  applied as sigle argument.
     *   Multiple arguments:    applied to params as is. (only sequencial)
     *
     * @param mixed $args...
     * @return int
     */
    public function execute(/*[$args[, ...]]*/)
    {
        if(func_num_args() == 0) {
            $args = array();
        }
        else if(func_num_args() == 1) {
            $a = func_get_arg(0);
            if($a instanceof Pinoco_Vars || $a instanceof Pinoco_List) {
                $args = $a->toArray();
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
    
    /**
     * Alias to execute
     * @param mixed $args...
     * @return int
     */
    public function exec(/*[$args[, ...]]*/)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'execute'), $args);
    }
    
    /**
     * Calls execute and returns self
     * @param mixed $args...
     * @return Pinoco_PDOStatementWrapper
     */
    public function query(/*[$args[, ...]]*/)
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'execute'), $args);
        return $this;
    }
    
    /**
     * Fetches the next row in result set.
     * If FALSE returned, you should close cursor using closeCursor().
     * @return Pinoco_Vars
     */
    public function fetch($orientation=PDO::FETCH_ORI_NEXT, $offset=0)
    {
        //return $this->_stmt->fetch(PDO::FETCH_CLASS, $orientation, $offset);
        $r = $this->_stmt->fetch(PDO::FETCH_ASSOC, $orientation, $offset);
        return $r !== FALSE ? Pinoco_Vars::fromArray($r) : $r;
    }
    
    /**
     * Fetches all results
     * @return Pinoco_List
     */
    public function fetchAll()
    {
        //return Pinoco::newList($this->_stmt->fetchAll(PDO::FETCH_CLASS));
        $rs = new Pinoco_List();
        $rows = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r) {
            $rs->push(Pinoco_Vars::fromArray($r));
        }
        return $rs;
    }
    
    /**
     * Fetches single result
     * @return Pinoco_Vars
     */
    public function fetchOne()
    {
        $r = $this->fetch();
        try { $this->closeCursor(); } catch(PDOException $ex){ }
        return $r;
    }
}

