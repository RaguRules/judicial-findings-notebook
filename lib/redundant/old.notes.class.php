<?php
// Include guard
if (!defined('*JusticeDelayedIsJusticeDenied@1')) {
    die('Direct access is not allowed.');
}

require_once("db.class.php");

class NotesManager extends Database{
    public function __construct(){
        parent::__construct("../../../model/data.db");
    }


    public function createNotes($userId, $title, $data){

        $createdAt = date("Y-m-d H:i:s");

        return $this->noteCreate($userId, $title, $data, $createdAt);
    }


    public function updateNotes($userId, $noteId, $title, $data){

        $createdAt = date("Y-m-d H:i:s");

        return $this->noteUpdate($userId, $noteId, $title, $data, $createdAt);
    }


    public function readNotes($userId, $noteId){
        return $this->noteRead($userId, $noteId);
    }


    public function deleteNotes($userId, $title){
        return $this->noteDelete($userId, $title);        
    }
    

    public function getAllNotes($userId){
        return $this->noteListAll($userId);
    }
}

?>