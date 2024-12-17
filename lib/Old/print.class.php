<?php
class PrintManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function fetchNoteById($noteId, $userId) {
        $stmt = $this->db->prepare("SELECT title, content, created_at FROM notes WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $noteId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if (!$result) {
            return ["status" => "error", "message" => "Note not found"];
        }
        return $result;
    }

    public function generatePrintableHTML($note) {
        $html = "
        <html>
        <head>
            <title>Print Note</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .note { border: 1px solid #ccc; padding: 20px; border-radius: 10px; width: 60%; margin: auto; }
                .note h1 { text-align: center; }
                .note .meta { font-size: 0.9em; color: #555; text-align: center; margin-bottom: 20px; }
                .note .content { line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class='note'>
                <h1>{$note['title']}</h1>
                <div class='meta'>Created at: {$note['created_at']}</div>
                <div class='content'>{$note['content']}</div>
            </div>
        </body>
        </html>";
        return $html;
    }
}

?>