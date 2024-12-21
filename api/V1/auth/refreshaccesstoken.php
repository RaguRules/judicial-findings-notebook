<?php
include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager ();
$checkAuth->refreshAccessToken("e424f398f9be6d17049b4a64848dfe4b4416fb5ef3f8340f7c38c5bfcd490f0a");

?>