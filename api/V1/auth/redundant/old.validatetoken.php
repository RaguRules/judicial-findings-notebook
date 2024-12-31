<?php

include_once("../../../lib/auth.class.php");

$checkAuth = new AuthManager();
$checkAuth->validateToken('af993f12e013b17be3e94dcf38ccd533182f2412fa47f211621eea88500ae4c5');





// Include your database connection and user authentication functions
// require_once '../db_connect.php'; // Your DB connection file
// require_once '../auth_functions.php'; // Functions to handle auth logic

// header('Content-Type: application/json');

// $headers = getallheaders();
// $accessToken = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// $response = ['status' => 'error', 'message' => 'Invalid token'];

// if ($accessToken) {
//     // Your logic to validate the token (e.g., check against database, verify expiration)
//     $user = validateAccessToken($accessToken); 

//     if ($user) {
//         $response = ['status' => 'success', 'message' => 'Token is valid'];
//     } 
// }

// echo json_encode($response);
?>