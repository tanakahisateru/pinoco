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
 * PDOWrapper provides extra methods to PDO.
 * Of course you can use also PDO functions.
 *
 * @package Pinoco
 * @property-read PDO $connection
 * @property mixed $afterConnection
 * @method bool beginTransaction()
 * @method bool commit()
 * @method mixed errorCode()
 * @method array errorInfo()
 * @method int exec(string $statement)
 * @method mixed getAttribute(int $attribute)
 * @method array getAvailableDrivers()
 * @method bool inTransaction()
 * @method string lastInsertId(string $name = null)
 * @method string quote( string $string, int $parameter_type = PDO::PARAM_STR)
 * @method bool rollBack()
 * @method bool setAttribute( int $attribute , mixed $value )
 */
class Pinoco_PDOWrapper
{
    private $_dsn;
    private $_un;
    private $_pw;
    private $_opts;
    private $_conn;
    private $_after_connection;

    /**
     * Wrapped PDO factory.
     *
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
     * Constructor
     *
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
        $this->_conn = null;
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
     * Returns the database connection as PDO.
     *
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->_conn === null) {
            $this->_conn = new PDO($this->_dsn, $this->_un, $this->_pw, $this->_opts);
            $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($this->_after_connection) {
                if (is_callable($this->_after_connection)) {
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
     *
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
     * Alias to exec().
     *
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
     *
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

