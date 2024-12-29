<?php
// Implement GUARD
define('_Jilebi@54321|', true); // Define the constant

include_once("../../../lib/auth.class.php");


$authManager = new AuthManager();


// Function to send JSON response
function sendJsonResponse($statusCode, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


// Rate Limiting (using a simple in-memory store - you might want to use a database or caching mechanism for persistence)
$rateLimit = [
    'window' => 60, // Time window in seconds
    'maxRequests' => 5, // Maximum requests allowed per window
    'requests' => [] // Store request timestamps
];

$clientIP = $_SERVER['REMOTE_ADDR'];

if (isset($rateLimit['requests'][$clientIP])) {
    $requests = $rateLimit['requests'][$clientIP];
    $requests = array_filter($requests, function($timestamp) use ($rateLimit) {
        return $timestamp > time() - $rateLimit['window'];
    });

    if (count($requests) >= $rateLimit['maxRequests']) {
        sendJsonResponse(429, ['status' => 'error', 'message' => 'Too Many Requests']); // 429 Too Many Requests
    }

    $rateLimit['requests'][$clientIP] = $requests;
}

$rateLimit['requests'][$clientIP][] = time();


// HTTPS Enforcement (check if the request is HTTPS)
// if ($_SERVER['HTTPS'] !== 'on') {
//     sendJsonResponse(403, ['status' => 'error', 'message' => 'HTTPS is required']);
// }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strcasecmp($contentType, 'application/json') === 0) {
        // --- Handle/Decode JSON data ---

        $inputData = file_get_contents('php://input');
        $decodedData = json_decode($inputData, true);

        // Check if decoding was successful
        if ($decodedData === null) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
        }

        // --- Now get the values from the decoded data ---
        $username = $decodedData['username'] ?? '';
        $password = $decodedData['password'] ?? '';

        $username = trim($requestData['username'] ?? '');
        $password = trim($requestData['password'] ?? '');
        
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $passsword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

         // Validate username format
        if (!filter_var($username, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-zA-Z0-9_]+$/']])) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid username format.']);
        }

        // Validate password complexity (at least 8 characters, one uppercase, one lowercase, one digit)
        if (strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) { 
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.']);
        }

    } elseif (strcasecmp($contentType, 'application/x-www-form-urlencoded') === 0) {
        // --- Handle Form data ---
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // $username = trim($requestData['username'] ?? '');
        // $password = trim($requestData['password'] ?? '');
        
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

            // Validate username format
        if (!filter_var($username, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-zA-Z0-9_]+$/']])) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid username format.']);
        }

        // Validate password complexity (at least 8 characters, one uppercase, one lowercase, one digit)
        if (strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) { 
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.']);
        }

    } else {
        // --- Unsupported Content-Type ---
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
    }

    // Logging (log the signup attempt)
    $logMessage = sprintf("Signup attempt from %s - Username: %s", $clientIP, $username);
    error_log($logMessage); // Log to the error log or a separate log file


    $result = $authManager->signup($username, $password);

    if ($result === true) {
        sendJsonResponse(201, ['status' => 'success', 'message' => 'Signup successful']);
    } if(($result === -1)){
        sendJsonResponse(400, ['status' => 'error', 'message' => 'This username has already been taken. Select another username']); // Send the error message
    }
    else {
        sendJsonResponse(400, ['status' => 'error', 'message' => $result]); // Send the error message
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

?>