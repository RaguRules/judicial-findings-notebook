<?php

/**
 * API endpoint for user logout.
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
$headers = apache_request_headers();
$authorizationHeader = $headers['Authorization'] ?? '';
$accessToken = str_replace('Bearer ', '', $authorizationHeader);

// if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//     $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
//     // Check if the header starts with "Bearer "
//     if (strpos($authHeader, 'Bearer ') === 0) {
//         $accessToken = substr($authHeader, 7); // Remove "Bearer " to get the token
//     }
// }


// If an access token is provided, validate it
if ($accessToken) {
    $validationResult = $authManager->validateToken($accessToken);
    if (!$validationResult) {
        // Invalid access token
        sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid access token.']);
    }
} else {
    // No access token provided
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Unauthorized. Access token required.']);
}

// --- END OF GUARD CONDITION ---

// --- LOGOUT LOGIC ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logout the user (delete the access token)
    $result = $authManager->logout($accessToken); // Pass the access token to the logout method

    if ($result['status'] === 'success') {
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Logout successful']);
    } else {
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Error during logout.']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}