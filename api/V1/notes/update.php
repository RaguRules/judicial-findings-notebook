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
// $accessToken = str_replace('Bearer ', '', $authorizationHeader);
$accessToken = 'b9214b682dd2de9e695083ffc51b71302cac6a1aa52d740c84ff1122a305e534';

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

$requiredMethod = 'POST';
$requiredData = ['note_id', 'title', 'content'];

// API code to update the note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strcasecmp($contentType, 'application/json') === 0) {
        // --- Handle/Decode JSON data ---

        $inputData = file_get_contents('php://input');
        $decodedData = json_decode($inputData, true);

        // Check if decoding was successful
        if ($decodedData === null) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data.']);
        }

        // --- Now get the values from the decoded data ---
        $noteId = $decodedData['note_id'] ?? null;
        $title = $decodedData['title'] ?? '';
        $content = $decodedData['content'] ?? '';

        // Arise Title and content are are required error from this API
        // $title = trim($requestData['title'] ?? '');
        // $content = trim($requestData['content'] ?? '');
        
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    } elseif (strcasecmp($contentType, 'application/x-www-form-urlencoded') === 0) {
        // --- Handle Form data ---

        $noteId = $_POST['note_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

    } else {
        // --- Unsupported Content-Type ---
        sendJsonResponse(415, ['status' => 'error', 'message' => 'Unsupported Content-Type.']);
    }


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