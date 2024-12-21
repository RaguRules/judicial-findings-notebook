<?php
require_once '../../../NotesManager.class.php'; // Include NotesManager
require_once '../../../AuthManager.class.php'; // Include AuthManager

// Initialize databases
$notesDb = new SQLite3('notes.db');
$authDb = new SQLite3('auth.db');

// Initialize classes
$notesManager = new NotesManager($notesDb);
$authManager = new AuthManager($authDb);

// Set response type
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $headers = getallheaders();

    // Validate token
    if (!isset($headers['Authorization'])) {
        echo json_encode(['status' => 'error', 'message' => 'Authorization token missing']);
        exit;
    }

    $authToken = str_replace('Bearer ', '', $headers['Authorization']);
    $userId = $authManager->getUserIdFromToken($authToken);

    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        exit;
    }

    // Fetch user notes
    $notes = $notesManager->getNotes($userId);
    echo json_encode(['status' => 'success', 'notes' => $notes]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
