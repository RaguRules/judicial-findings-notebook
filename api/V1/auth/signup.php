<?php

include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager ();
$checkAuth->signup("Sanchaya", "Rabbit");

?>