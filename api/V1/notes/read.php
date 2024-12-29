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

if (!$tokenValid) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']);
}

$userId = $tokenValid['user_id'];
$tokenType = $tokenValid['type'];

if ($tokenType !== 'access') {
    sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid token type for this operation.']);
}
// API code to get a specific note
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the note ID from the request parameters
    $noteId = $_GET['note_id'] ?? null; 

    // Validate the note ID
    if (empty($noteId) || !is_numeric($noteId)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID.']);
    }

    // echo "am working";
    $note = $notesManager->readNotes($userId, $noteId); // Get the note

    if ($note !== false) {
        sendJsonResponse(200, ['status' => 'success', 'note' => $note]);
    } else {
        sendJsonResponse(404, ['status' => 'error', 'message' => 'Note not found.']); // 404 Not Found
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

?>