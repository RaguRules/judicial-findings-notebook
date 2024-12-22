<?php

require_once "../../../lib/db.class.php";

echo "<br><br><b>This will case an Error with regard <i><u>Call a protected function in Library by this externel API.</i></u> This is okay.</b><br><br>";

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
$CT = createTables();
