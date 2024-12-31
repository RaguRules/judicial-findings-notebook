<?php
/**
 * API endpoint for refreshing an access token using a refresh token.
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

// Rate Limiting (using a simple in-memory store for demonstration)
/**
 * @var array $rateLimit Rate limiting configuration.
 */
$rateLimit = [
    'window' => 60,       // Time window in seconds (60 seconds = 1 minute)
    'maxRequests' => 20,  // Maximum requests allowed per window (20 requests per minute)
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

    // Handle JSON content type
    if (strcasecmp($contentType, 'application/json') === 0) {
        $inputData = file_get_contents('php://input');
        $decodedData = json_decode($inputData, true);

        if ($decodedData === null) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
        }

        $refreshToken = $decodedData['refresh_token'] ?? '';

    } else {
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type. Only application/json is allowed.']);
    }

    // Input Validation
    $refreshToken = trim($refreshToken);

    if (empty($refreshToken)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Refresh token is required.']);
    }

    // Perform the token refresh
    $result = $authManager->refreshAccessToken($refreshToken);

    if ($result['status'] === 'success') {
        // Send the new access token in the response
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Access token is refreshed.', 'access_token' => $result['access_token']]);
    } else {
        sendJsonResponse(401, ['status' => 'error', 'message' => $result['message']]);
    }

} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}