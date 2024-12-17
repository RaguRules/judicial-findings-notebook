<?php

class Database {
    public $db;
    public $dbpath = "db.db";

    public function __construct($dbpath="db.db"){
        echo "contructor is executed";
        $this->db = new SQLite3 ($dbpath);
    }

    public function getConnection(){
        echo "get conn func work";
        return $this->db;
    }
}

?>