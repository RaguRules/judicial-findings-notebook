<?php
// Example route for print API
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'print') {
    $noteId = $_GET['note_id']; // Get note ID
    $userId = $_GET['user_id']; // Validate user ID (via token ideally)

    $notesDb = new SQLite3('notes.db');
    $printManager = new PrintManager($notesDb);

    $note = $printManager->fetchNoteById($noteId, $userId);
    if (isset($note['status']) && $note['status'] === 'error') {
        echo json_encode($note);
        exit;
    }

    $html = $printManager->generatePrintableHTML($note);
    echo $html; // Send HTML to browser for printing
}

?>