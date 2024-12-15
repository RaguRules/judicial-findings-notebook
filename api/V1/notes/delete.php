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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
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

    // Parse input
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['noteId'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing noteId']);
        exit;
    }

    $noteId = (int) $data['noteId'];
    $success = $notesManager->deleteNote($noteId, $userId);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Note deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete note']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
