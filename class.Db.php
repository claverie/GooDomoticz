<?php

require_once __DIR__ . '/class.Logs.php';

class Db {

    private $log;
    private $db;
    
    function __construct()
    {
        $this->log = new Logs("DB");
        $this->db = false;
        try {
            $this->db = new SQLite3(ORDERS_DB_FILE, SQLITE3_OPEN_READWRITE);
            $this->db->enableExceptions(true);
        } catch (Exception $e) {
            $this->log->Error("Can't open db $dbfile [".$e->getMessage()."]");
            return false;
        }
        return $this;
    }

    function Close()
    {
        $this->db->close();
    }
    
    function ExecQuery($query=null)
    {
        $res = null;
        if ($query == null) return null;
        try {
            $this->log->Debug("Execute query [$query]");
            $res = $this->db->query($query);
        } catch (Exception $e) {
            $this->log->Error("[$query] [".$this->db->lastErrorCode()."::".$this->db->lastErrorMsg()."]");
        }
        return $res;
    }

}