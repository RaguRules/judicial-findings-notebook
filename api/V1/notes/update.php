<?php

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

if (!$tokenValid) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']);
}

$userId = $tokenValid['user_id'];
$tokenType = $tokenValid['type'];

if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']);
}

// API code to update the note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noteId = $_POST['note_id'] ?? null; 
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
// if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
//     $noteId = $_PUT['note_id'] ?? null; 
//     $title = $_PUT['title'] ?? '';
//     $content = $_PUT['content'] ?? '';

    // Validate the input (add more validation as needed)
    if (empty($noteId) || !is_numeric($noteId)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID.']);
    }
    if (empty($title) || empty($content)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Title and content are required.']);
    }

    $success = $notesManager->updateNotes($userId, $noteId, $title, $content);

    if ($success) {
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Note updated successfully.']);
    } else {
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to update note.']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

?>