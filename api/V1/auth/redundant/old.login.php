<?php
// include_once("../../../lib/auth.class.php");

// $checkAuth = new AuthManager ();
// $checkAuth->login("Rabbit", "Rabbit");


require_once '../../../lib/auth.class.php';

$authManager = new AuthManager();

// Function to send JSON response
function sendJsonResponse($statusCode, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Rate Limiting (using a simple in-memory store)
$rateLimit = [
    'window' => 60,        // Time window in seconds
    'maxRequests' => 5,    // Maximum requests allowed per window
    'requests' => []      // Store request timestamps
];

$clientIP = $_SERVER['REMOTE_ADDR'];

if (isset($rateLimit['requests'][$clientIP])) {
    $requests = $rateLimit['requests'][$clientIP];
    $requests = array_filter($requests, function($timestamp) use ($rateLimit) {
        return $timestamp > time() - $rateLimit['window'];
    });

    if (count($requests) >= $rateLimit['maxRequests']) {
        sendJsonResponse(429, ['status' => 'error', 'message' => 'Too Many Requests']);
    }

    $rateLimit['requests'][$clientIP] = $requests;
}

$rateLimit['requests'][$clientIP][] = time();

// Logging (log the login attempt)
$logMessage = sprintf("Login attempt from %s - Username: %s", $clientIP, $_POST['username']);
error_log($logMessage);

// HTTPS Enforcement
// if ($_SERVER['HTTPS'] !== 'on') {
//     sendJsonResponse(403, ['status' => 'error', 'message' => 'HTTPS is required']);
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    $username = $password = '';

    if (strcasecmp($contentType, 'application/json') === 0) {
        $inputData = file_get_contents('php://input');
        $decodedData = json_decode($inputData, true);

        if ($decodedData === null) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
        }

        $username = $decodedData['username'] ?? '';
        $password = $decodedData['password'] ?? '';

    } elseif (strcasecmp($contentType, 'application/x-www-form-urlencoded') === 0) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
    } else {
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
    }

    // // Input Validation and Sanitization
    // $username = trim($username);
    // $password = trim($password);

    // ... (Add more validation, e.g., using filter_var(), checking password complexity) ...

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // Assuming your AuthManager has a login() method that returns an array with 'status' and 'message'
    $result = $authManager->login($username, $password); 

    if ($result['status'] === true) {
            // Set the tokens in the response headers
        header('X-Access-Token: ' . $result['access_token']); 
        header('X-Refresh-Token: ' . $result['refresh_token']);
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Login successful']);
    } else {
        sendJsonResponse(400, ['status' => 'error', 'message' => $result['message']]);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

?>