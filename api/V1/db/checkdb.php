<?php

require_once "../../../lib/db.class.php";

function conn(){
    echo "conn API starts...<br>";
    $db = new Database("../../../model/data.db");
    $conn = $db->getConn();
    echo "conn API ends...<br>";

}

function createTables(){
    echo "createTables API starts...<br>";
    $db = new Database ("../../../model/data.db");
    $db->createTables();
    echo "createTables API ends...<br>";
}

$conn = conn();
