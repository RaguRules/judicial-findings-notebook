<?php
include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager ();
$checkAuth->refreshAccessToken("431c4880f26cdda4df7dfc748d74f2a95c71cbc1f41352f979b62c71da5eb029");

?>