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

// Assuming your validateToken() method returns an array with user_id and token type
$userId = $tokenValid['user_id']; 
$tokenType = $tokenValid['type']; 

// (Optional) Check if the token type is 'access'
if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']); // 403 Forbidden
}


// Now you have the $userId for the authenticated user.
// API code to create the note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $data = json_decode(file_get_contents('php://input'), true);
    $title = $_POST['title'] ?? ''; // Get the title from form data
    $content = $_POST['content'] ?? ''; // Get the content from form data
    echo "Title is: $title";
    echo "Content is: $content";


    if (isset($title) && isset($content)) {
        $noteId = $notesManager->createNotes($userId, $title, $content);

        if ($noteId) {
            sendJsonResponse(201, ['status' => 'success', 'message' => 'Note created successfully', 'note_id' => $noteId]);
        } else {
            sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to create note']);
        }
    } else {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Missing title or content']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}


?>