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

