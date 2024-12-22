<?php

include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager();
$checkAuth->validateToken('af993f12e013b17be3e94dcf38ccd533182f2412fa47f211621eea88500ae4c5');

?>