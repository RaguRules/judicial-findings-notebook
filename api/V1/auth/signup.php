<?php
/**
 * API endpoint for user signup.
 */

// Define the constant to bypass the include guard in library files.
define('*JusticeDelayedIsJusticeDenied@1', true);

require_once '../../../lib/auth.class.php';

/**
 * @var AuthManager $authManager Instance of the AuthManager class.
 */
$authManager = new AuthManager();

/**
 * Sends a JSON response to the client.
 *
 * @param int $statusCode The HTTP status code.
 * @param array $data The data to be encoded as JSON.
 * @return void
 */
function sendJsonResponse($statusCode, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- GUARD CONDITION: Check for Valid Access Token ---

/**
 * @var string|null $accessToken The access token from the Authorization header.
 */
$accessToken = null;

// Get the Authorization header
$accessToken = null;
$headers = apache_request_headers();
$authorizationHeader = $headers['Authorization'] ?? '';
$accessToken = str_replace('Bearer ', '', $authorizationHeader);


// If an access token is provided, validate it
if ($accessToken) {
    $validationResult = $authManager->validateToken($accessToken);
    if ($validationResult) {
        // User is authenticated, return a message indicating they are already logged in
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Already logged in', 'user_id' => $validationResult['user_id']]);
    } else {
        // Invalid access token
        sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid access token.']);
    }
}

// --- END OF GUARD CONDITION ---

// Rate Limiting (using a simple in-memory store for demonstration)
/**
 * @var array $rateLimit Rate limiting configuration.
 */
$rateLimit = [
    'window' => 60,       // Time window in seconds (60 seconds = 1 minute)
    'maxRequests' => 30,  // Maximum requests allowed per window (30 requests per minute)
    'requests' => []      // Store request timestamps per IP address
];

/**
 * @var string $clientIP The client's IP address.
 */
$clientIP = $_SERVER['REMOTE_ADDR'];

// Check if the client has made requests before
if (isset($rateLimit['requests'][$clientIP])) {
    $requests = $rateLimit['requests'][$clientIP];

    // Remove requests older than the time window
    $requests = array_filter($requests, function ($timestamp) use ($rateLimit) {
        return $timestamp > time() - $rateLimit['window'];
    });

    // Check if the client has exceeded the maximum number of requests
    if (count($requests) >= $rateLimit['maxRequests']) {
        // Log the rate limit exceeded event
        error_log(sprintf("Rate limit exceeded for IP: %s", $clientIP));

        sendJsonResponse(429, ['status' => 'error', 'message' => 'Too Many Requests']);
    }

    // Update the requests array for the client
    $rateLimit['requests'][$clientIP] = $requests;
}

// Add the current request timestamp for the client
$rateLimit['requests'][$clientIP][] = time();

// HTTPS Enforcement (Comment out for localhost development)
/*
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'HTTPS is required']);
}
*/

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    $username = $password = '';

    // Handle different content types (JSON and form data)
    if (strcasecmp($contentType, 'application/json') === 0) {
        // Handle JSON data
        $inputData = file_get_contents('php://input');
        $decodedData = json_decode($inputData, true);

        if ($decodedData === null) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
        }

        $username = $decodedData['username'] ?? '';
        $password = $decodedData['password'] ?? '';

    } elseif (strcasecmp($contentType, 'application/x-www-form-urlencoded') === 0) {
        // Handle Form data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

    } else {
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
    }

    // Input Validation and Sanitization
    $username = trim($username);
    $password = trim($password);

    // Basic Server-Side Validation
    if (empty($username) || empty($password)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Username and password are required.']);
    }

    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid username format. Use only alphanumeric characters and underscores.']);
    }

    // Validate password complexity
    if (strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.']);
    }

    // Sanitize Input (Escape special characters to prevent XSS)
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // Logging (log the signup attempt)
    $logMessage = sprintf("Signup attempt from %s - Username: %s", $clientIP, $username);
    error_log($logMessage); // Log to the error log or a separate log file

    // Perform the signup attempt
    $result = $authManager->signup($username, $password);

    if ($result['status'] === 'success') {
        sendJsonResponse(201, ['status' => 'success', 'message' => 'Signup successful']);
    } elseif ($result['status'] === 'error' && $result['message'] === 'Username already taken.') {
        sendJsonResponse(400, ['status' => 'error', 'message' => $result['message']]);
    } else {
        sendJsonResponse(500, ['status' => 'error', 'message' => 'An error occurred during signup.']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}