<?php
/**
 * API endpoint for user login.
 */

// Define the constant to bypass the include guard in library files
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
      // User is authenticated, proceed with API logic
      // ... (You can add further logic here if needed, e.g., checking user roles) ...

      // Since this is login.php, if a user is already logged in, we can redirect them.
      sendJsonResponse(200, ['status' => 'success', 'message' => 'Already logged in', 'user_id' => $validationResult['user_id'], 'type' => $validationResult['type']]);
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
    'maxRequests' => 10,  // Maximum requests allowed per window (10 requests per minute)
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

// Logging (Log the login attempt with username and IP)
$logMessage = sprintf("Login attempt from %s - Username: %s", $clientIP, $_POST['username'] ?? '');
error_log($logMessage);

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

    // Input Validation and Sanitization
    $username = trim($username);
    $password = trim($password);

    // Basic Server-Side Validation (add more as needed)
    if (empty($username) || empty($password)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Username and password are required.']);
    }

    // Further Sanitize Input (Escape special characters to prevent XSS)
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // Perform the login attempt
    $result = $authManager->login($username, $password);

    if ($result['status'] === 'success') {
        // Set the tokens in the response headers (consider HttpOnly cookies for refresh token in production)
        header('X-Access-Token: ' . $result['access_token']);
        header('X-Refresh-Token: ' . $result['refresh_token']);

        sendJsonResponse(200, ['status' => 'success', 'message' => 'Login successful']);
    } else {
        sendJsonResponse(401, ['status' => 'error', 'message' => $result['message']]);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}