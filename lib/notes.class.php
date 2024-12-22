<?php

require_once("db.class.php");

class NotesManager extends Database{
    public function __construct(){
        parent::__construct("../../../model/data.db");
    }

    public function createNotes($userId, $title, $data){

        $createdAt = date("Y-m-d H:i:s");

        if($this->noteCreate($userId, $title, $data, $createdAt)){
            echo "Notes created...";
            return true;
        }

    }
}