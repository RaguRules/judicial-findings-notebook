<?php
echo "hi";

require_once "../../lib/db.class.php";

function conn(){
    echo "API starts...";
    $db = new Database();
    $conn = $db->getConnection();

}

$conn = conn();