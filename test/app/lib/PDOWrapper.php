<?php
/**
 * PDO wrapper
 */
class PDOWrapper {
    private $_dsn;
    private $_un;
    private $_pw;
    private $_opts;
    private $_conn;
    
    public function __construct($dsn, $un="", $pw="", $opts=array())
    {
        $this->_dsn = $dsn;
        $this->_un = $un;
        $this->_pw = $pw;
        $this->_opts = $opts;
        $this->_conn = NULL;
    }
    
    public function getConnection()
    {
        if($this->_conn === NULL) {
            $this->_conn = new PDO($this->_dsn, $this->_un, $this->_pw, $this->_opts);
            $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->_conn;
    }
    
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->getConnection(), $name), $args);
    }
    
    public function prepare($sql, $opts=array())
    {
        return new PDOStatementWrapper(
            $this->getConnection()->prepare($sql, $opts)
        );
    }
    
    public function execute()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'exec'), $args);
    }
    
    public function query($sql)
    {
        return new PDOStatementWrapper(
            $this->getConnection()->query($sql)
        );
    }
}

/**
 * PDO Statement wrapper
 */
class PDOStatementWrapper {
    private $_stmt;
    
    public function __construct($stmt)
    {
        $this->_stmt = $stmt;
        //$this->_stmt->setFetchMode(PDO::FETCH_CLASS, "Pinoco_Vars", array());
    }
    
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->_stmt, $name), $args);
    }
    
    public function execute()
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
    
    public function exec()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'execute'), $args);
    }
    
    public function query()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'execute'), $args);
        return $this;
    }
    
    public function fetch($orientation=PDO::FETCH_ORI_NEXT, $offset=0)
    {
        //return $this->_stmt->fetch(PDO::FETCH_CLASS, $orientation, $offset);
        $r = $this->_stmt->fetch(PDO::FETCH_ASSOC, $orientation, $offset);
        return $r !== FALSE ? Pinoco_Vars::wrap($r) : $r;
    }
    
    public function fetchAll()
    {
        //return Pinoco::newList($this->_stmt->fetchAll(PDO::FETCH_CLASS));
        $rs = new Pinoco_List();
        $rows = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r) {
            $rs->push(Pinoco_Vars::wrap($r));
        }
        return $rs;
    }
    
    public function fetchOne()
    {
        $r = $this->fetch();
        try { $this->closeCursor(); } catch(PDOException $ex){ }
        return $r;
    }
}

