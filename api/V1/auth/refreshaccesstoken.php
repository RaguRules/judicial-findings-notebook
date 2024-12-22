<?php
include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager ();
$checkAuth->refreshAccessToken("f04c2082c463572616e8d7eb8a48c2f16e61513dfe9591a8082ad9bba714563f");

?>