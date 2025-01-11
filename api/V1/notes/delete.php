<?php

define('*JusticeDelayedIsJusticeDenied@1', true); // Define the constant

require_once '../../../lib/notes.class.php';
require_once '../../../lib/auth.class.php'; 

$notesManager = new NotesManager();
$authManager = new AuthManager();


// Function to send JSON response with status code
function sendJsonResponse($statusCode, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// API authentication and authorization logic
// 1. Get the access token from the Authorization header
$headers = apache_request_headers();
$authorizationHeader = $headers['Authorization'] ?? '';
$accessToken = str_replace('Bearer ', '', $authorizationHeader); 

if (empty($accessToken)) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Authorization header missing.']);
}

// 2. Validate the access token and extract user_id
$tokenValid = $authManager->validateToken($accessToken); // Assuming you have a validateToken() method in AuthManager

if (!$tokenValid) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']); 
}

// ValidateToken() method returns an array with user_id and token type
$userId = $tokenValid['user_id']; 
$tokenType = $tokenValid['type']; 

// (Optional) Check if the token type is 'access'
if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']); // 403 Forbidden
}


if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}


$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$noteId = null;

// Handle different content types
if (strcasecmp($contentType, 'application/json') === 0) {
    // Handle JSON data
    $inputData = file_get_contents('php://input');
    $decodedData = json_decode($inputData, true);

    if ($decodedData === null) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
    }

    $noteId = $decodedData['note_id'] ?? null;

} elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0) {
    // Handle form data
    $noteId = $_POST['note_id'] ?? null;
} else {
    sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
}

// Validate note ID
if (empty($noteId) || !is_numeric($noteId)) {
    sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid or missing note ID.']);
}

$noteId = (int)$noteId; // Convert to integer after validation


// Delete the note
$success = $notesManager->deleteNotes($userId, $noteId);

if ($success) {
    sendJsonResponse(200, ['status' => 'success', 'message' => 'Note deleted successfully.']);
} else {
    sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to delete note.']);
}


?>