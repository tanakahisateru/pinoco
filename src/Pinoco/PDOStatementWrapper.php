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
 *
 * @package Pinoco
 * @property-read string $queryString
 * @method bool bindColumn() bool bindColumn(mixed $column , mixed &$param, int $type, int $maxlen, mixed $driverdata)
 * @method bool bindParam() bool bindParam(mixed $parameter, mixed &$variable, int $data_type = PDO::PARAM_STR, int $length, mixed $driver_options)
 * @method bool bindValue() bool bindValue(mixed $parameter, mixed $value, int $data_type = PDO::PARAM_STR)
 * @method bool closeCursor()
 * @method int columnCount()
 * @method bool debugDumpParams()
 * @method string errorCode()
 * @method array errorInfo()
 * @method string fetchColumn() string fetchColumn(int $column_number = 0)
 * @method mixed fetchObject() mixed fetchObject(string $class_name = "stdClass", array $ctor_args)
 * @method mixed getAttribute() getAttribute( int $attribute )
 * @method array getColumnMeta() getColumnMeta( int $column )
 * @method bool nextRowset()
 * @method int rowCount()
 * @method bool setAttribute() setAttribute( int $attribute , mixed $value )
 * @method bool setFetchMode() setFetchMode( int $mode )
 */
class Pinoco_PDOStatementWrapper
{
    private $_stmt;

    /**
     * Constructor
     *
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
     *     array incompatible:  applied as single argument.
     *   Multiple arguments:    applied to params as is. (only sequential)
     *
     * @param mixed $args,...
     * @return int
     */
    public function execute($args=Pinoco_OptionalParam::UNSPECIFIED)
    {
        $args = func_get_args();
        $args = Pinoco_OptionalParam::trim($args);
        if (count($args) == 0) {
            $args = null;
        }
        elseif (count($args) == 1) {
            $args = $args[0];
            if ($args instanceof Pinoco_ArrayConvertible) {
                $args = $args->toArray();
            }
            elseif (!is_array($args)) {
                $args = array($args);
            }
        }
        $this->_stmt->execute($args);
        return $this->rowCount();
    }

    /**
     * Alias to execute.
     *
     * @param mixed $args,...
     * @return int
     */
    public function exec($args=Pinoco_OptionalParam::UNSPECIFIED)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'execute'), Pinoco_OptionalParam::trim($args));
    }

    /**
     * Calls execute and returns self.
     *
     * @param mixed $args,...
     * @return Pinoco_PDOStatementWrapper
     */
    public function query($args=Pinoco_OptionalParam::UNSPECIFIED)
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'execute'), Pinoco_OptionalParam::trim($args));
        return $this;
    }

    /**
     * Fetches the next row in result set.
     * If false returned, you should close cursor using closeCursor().
     *
     * @param int $orientation
     * @param int $offset
     * @return Pinoco_Vars|boolean
     */
    public function fetch($orientation=PDO::FETCH_ORI_NEXT, $offset=0)
    {
        //return $this->_stmt->fetch(PDO::FETCH_CLASS, $orientation, $offset);
        $r = $this->_stmt->fetch(PDO::FETCH_ASSOC, $orientation, $offset);
        return $r !== false ? Pinoco_Vars::fromArray($r) : $r;
    }

    /**
     * Fetches all results.
     *
     * @return Pinoco_List
     */
    public function fetchAll()
    {
        //return Pinoco::newList($this->_stmt->fetchAll(PDO::FETCH_CLASS));
        $rs = new Pinoco_List();
        $rows = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $rs->push(Pinoco_Vars::fromArray($r));
        }
        return $rs;
    }

    /**
     * Fetches single result.
     *
     * @return Pinoco_Vars
     */
    public function fetchOne()
    {
        $r = $this->fetch();
        try { $this->closeCursor(); } catch (PDOException $ex) { }
        return $r;
    }
}

