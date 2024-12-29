<?php

// require_once '../../../lib/notes.class.php';
require_once '../../../lib/auth.class.php';
require_once '../../../lib/print.class.php';

// $notesManager = new NotesManager();
$authManager = new AuthManager();
$printManager = new PrintManager();

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


// API code to print a specific note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $noteId = $data['note_id'] ?? null;

    if (empty($noteId) || !is_numeric($noteId)) {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid note ID.']);
    }

    // Get the PDF content from the printNote() method
    $pdfContent = $printManager->printNote($userId, $noteId); 

    if ($pdfContent) {
        // Send the PDF content in the response with appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="note.pdf"'); 
        echo $pdfContent; 
    } else {
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to generate PDF.']);
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

?>