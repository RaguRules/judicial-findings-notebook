<?php
/**
 * API endpoint for validating an access token.
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

        $accessToken = $decodedData['token'] ?? '';

    } else {
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type. Only application/json is allowed.']);
    }

    // Input Validation
    $accessToken = trim($accessToken);

    if (empty($accessToken)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Access/Refresh token is required.']);
    }

    // Perform token validation
    $result = $authManager->validateToken($accessToken);

    if ($result['status'] === 'success') {
        // Token is valid
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Token is valid.', 'user_id' => $result['user_id'], 'type' => $result['type']]);
    } elseif ($result['status'] === 'expired') {
        sendJsonResponse(401, ['status' => 'expired', 'message' => 'Token is expired.']);
    } else {
        // Token is invalid
        sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid Token.']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}