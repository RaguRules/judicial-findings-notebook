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
$tokenValid = $authManager->validateToken($accessToken);

if ($tokenValid['status']=='expired') {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']);
}

if ($tokenValid['status']=='error') {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid access token.']);
}

// Assuming your validateToken() method returns an array with user_id and token type
$userId = $tokenValid['user_id']; 
$tokenType = $tokenValid['type']; 

// (Optional) Check if the token type is 'access'
if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']); // 403 Forbidden
}


// Now you have the $userId for the authenticated user.
// API code to create the note
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$title = null;
$content = null;


// --- Handle JSON data ---
if (strcasecmp($contentType, 'application/json') === 0) {
    $inputData = file_get_contents('php://input');
    $decodedData = json_decode($inputData, true);

    if ($decodedData === null) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
    }
    $title = $decodedData['title'] ?? '';
    $content = $decodedData['content'] ?? '';

// --- Handle Form data ---
} elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0 || strpos($contentType, 'multipart/form-data') === 0) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

} else {
    sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
}

if (empty($title) || empty($content)) {
    sendJsonResponse(400, ['status' => 'error', 'message' => 'Title and content are required.']);
}

// --- Sanitize Input ---
$title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
$content = htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8');

$note = $notesManager->createNotes($userId, $title, $content);

if ($note) {
    sendJsonResponse(201, ['status' => 'success', 'message' => 'Note created successfully', 'note' => $note]);
} else {
    sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to create note']);
}


?>