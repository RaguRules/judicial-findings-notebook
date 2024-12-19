<?php
echo "hi";

require_once "../../lib/db.class.php";

function conn(){
    echo "API starts...";
    $db = new Database("../../db/data.db");
    $conn = $db->getConnection();

}

$conn = conn();