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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if (!isset($data['title'], $data['content'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing title or content']);
        exit;
    }

    $title = $data['title'];
    $content = $data['content'];

    // Create the note
    $success = $notesManager->createNoteWithTitle($userId, $title, $content);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Note created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create note']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>






// require_once '../../../Database.php';
// require_once '../../../Auth.php';
// require_once '../../../NotesManager.php';

// use Lib\Database;
// use Lib\Auth;
// use Lib\NotesManager;

// header('Content-Type: application/json');

// $db = new Database();
// $conn = $db->getDb();
// $auth = new Auth();
// $notesManager = new NotesManager($conn);

// $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
// $userId = $auth->validateToken($token);

// if (!$userId) {
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

// $content = $_POST['content'] ?? '';
// if ($notesManager->createNote($userId, $content)) {
//     echo json_encode(['message' => 'Note created successfully']);
// } else {
//     echo json_encode(['error' => 'Failed to create note']);
// }
?>