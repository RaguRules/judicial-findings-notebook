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
$headers = apache_request_headers();
$authorizationHeader = $headers['Authorization'] ?? '';
$accessToken = str_replace('Bearer ', '', $authorizationHeader);

if (empty($accessToken)) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Authorization header missing.']);
}

$tokenValid = $authManager->validateToken($accessToken);

if ($tokenValid['status']=='expired') {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']);
}

if ($tokenValid['status']=='error') {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid access token.']);
}

$userId = $tokenValid['user_id'];
$tokenType = $tokenValid['type'];

if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']);
}

$requiredMethod = 'POST';
$requiredData = ['note_id', 'title', 'content'];

// --- Input Validation and Processing ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$noteId = null;
$title = null;
$content = null;


// --- Handle JSON data ---
if (strcasecmp($contentType, 'application/json') === 0) {
    $inputData = file_get_contents('php://input');
    $decodedData = json_decode($inputData, true);

    if ($decodedData === null) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
    }

    $noteId = $decodedData['note_id'] ?? null;
    $title = $decodedData['title'] ?? '';
    $content = $decodedData['content'] ?? '';

// --- Handle Form data ---
} elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0 || strpos($contentType, 'multipart/form-data') === 0) {
    $noteId = $_POST['note_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

} else {
    sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
}

// --- Input Validation ---
if (empty($noteId)) {
    sendJsonResponse(400, ['status' => 'error', 'message' => 'Note ID is required.']);
}

// Strict validation: Check if note_id is a valid integer using ctype_digit (for string input)
if (!is_numeric($noteId) || !ctype_digit((string)$noteId)) {
    sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID format.']);
}

// Convert to integer only after validation
$noteId = (int)$noteId;

if (empty($title) || empty($content)) {
    sendJsonResponse(400, ['status' => 'error', 'message' => 'Title and content are required.']);
}

// --- Sanitize Input ---
$title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
$content = htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8');

// --- Update the Note ---
$success = $notesManager->updateNotes($userId, $noteId, $title, $content);


    // if (strcasecmp($contentType, 'application/json') === 0) {
    //     // --- Handle/Decode JSON data ---

    //     $inputData = file_get_contents('php://input');
    //     $decodedData = json_decode($inputData, true);

    //     // Check if decoding was successful
    //     if ($decodedData === null) {
    //         sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
    //     }

    //     // --- Now get the values from the decoded data ---
    //     $noteId = $decodedData['note_id'] ?? null;
    //     $title = $decodedData['title'] ?? '';
    //     $content = $decodedData['content'] ?? '';

    //     // --- Input Validation ---
    //     if (empty($noteId)) {
    //         sendJsonResponse(400, ['status' => 'error', 'message' => 'Note ID is required.']);
    //     }

    //     // Strict validation: Check if note_id is a valid integer using ctype_digit (for string input)
    //     if (!is_numeric($noteId) || !ctype_digit((string)$noteId)) {
    //         sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID format.']);
    //     }

    //     // Convert to integer only after validation
    //     $noteId = (int)$noteId;

    //     // Arise Title and content are are required error from this API
    //     // $title = trim($requestData['title'] ?? '');
    //     // $content = trim($requestData['content'] ?? '');
        
    //     $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    //     $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    //     // $success = $notesManager->updateNotes($userId, $noteId, $title, $content);
        

    // } elseif (strcasecmp($contentType, 'application/x-www-form-urlencoded') === 0) {
    //     // --- Handle Form data ---

    //     $noteId = $_POST['note_id'] ?? null;
    //     $title = $_POST['title'] ?? '';
    //     $content = $_POST['content'] ?? '';

    //     // --- Input Validation ---
    //     if (empty($noteId)) {
    //         sendJsonResponse(400, ['status' => 'error', 'message' => 'Note ID is required.']);
    //     }

    //     // Strict validation: Check if note_id is a valid integer using ctype_digit (for string input)
    //     if (!is_numeric($noteId) || !ctype_digit((string)$noteId)) {
    //         sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID format.']);
    //     }

    //     // Convert to integer only after validation
    //     $noteId = (int)$noteId;

    //     // Input validation and sanitization (add more as needed)
    //     $title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8'); 
    //     $content = htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8'); 

    // if (isset($title) && isset($content)) {
    //     $success = $notesManager->updateNotes($userId, $noteId, $title, $content);

    // } else {
    //     // --- Unsupported Content-Type ---
    //     sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
    // }

    // $success = $notesManager->updateNotes($userId, $noteId, $title, $content);

if ($success) {
    sendJsonResponse(200, ['status' => 'success', 'message' => 'Note updated successfully.']);
} else {
    sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to update note.']);
}

?>