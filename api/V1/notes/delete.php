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


// Now you have the $userId for the authenticated user.
// API code to delete the note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $data = json_decode(file_get_contents('php://input'), true);
    $title = $_POST['title'] ?? ''; // Get the title from form data


    if (isset($title)) {
        $noteId = $notesManager->deleteNotes($userId, $title);

        if ($noteId) {
            sendJsonResponse(201, ['status' => 'success', 'message' => 'Note deleted successfully', 'note_id' => $noteId]);
        } else {
            sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed. Either no notes found or This Notes could be created by another user']);
        }
    } else {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Missing title']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}


?>