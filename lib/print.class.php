<?php

require_once("db.class.php");
require_once("notes.class.php");

class PrintManager extends Database {

    public function __construct() {
        $notesManager = new NotesManager();
    }
    // ... (your other methods: createNote, getNotes, updateNote, deleteNote)

    

    public function printNote($userId, $noteId) {
        $note = $notesManager->getNoteById($userId, $noteId);

        if (!$note) {
            return "Note not found.";
        }

        // Generate HTML with enhanced styling (from previous example)
        $html = "
            <!DOCTYPE html> 
            <html> 
            <head> 
                <title>Note - " . $note['title'] . "</title> 
                <style>
                    /* ... (your enhanced CSS styles for printing) ... */
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>" . $note['title'] . "</h1>";

        // Assuming you have an 'image_path' column in your 'notes' table
        $imagePath = $note['image_path'] ?? '';
        if (!empty($imagePath)) {
            $html .= "<img src='" . $imagePath . "' alt='Note Image'>";
        }

        $html .= "<p>" . $note['content'] . "</p>
                    <div style='text-align: right; font-size: 10pt;'>
                        Created at: " . $note['created_at'] . "
                    </div>
                </div>
            </body>
            </html>
        ";

        // Generate PDF using Dompdf
        require_once 'add-ons/autoload.inc.php';

        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return the PDF content as a string
        return $dompdf->output(); 
    }
}

?>