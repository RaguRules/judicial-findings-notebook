<?php

class Database {
    public $db;
    public $dbpath = "db.db";

    public function __contruct($dbpath){
        $this->db = new SQLite3 ($dbpath);
        echo "contructor is executed";
    }

    public function getConnection(){
        echo "get conn func work";
        return $this->db;
    }
}

?>