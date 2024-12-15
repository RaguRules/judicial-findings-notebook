<?php
class NotesManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a note with title
    public function createNote($userId, $title, $content) {
        $stmt = $this->db->prepare("INSERT INTO notes (user_id, title, content, created_at) VALUES (:user_id, :title, :content, :created_at)");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':title', htmlspecialchars($title), SQLITE3_TEXT); // Prevent XSS
        $stmt->bindValue(':content', htmlspecialchars($content), SQLITE3_TEXT); // Prevent XSS
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        return $stmt->execute();
    }

    // Fetch notes for a specific user
    public function getNotes($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $notes = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $notes[] = $row;
        }
        return $notes;
    }

    // Delete a specific note
    public function deleteNote($noteId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM notes WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $noteId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    // Update a note's title and content
    public function updateNote($noteId, $userId, $title, $content) {
        $stmt = $this->db->prepare("UPDATE notes SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':title', htmlspecialchars($title), SQLITE3_TEXT); // Prevent XSS
        $stmt->bindValue(':content', htmlspecialchars($content), SQLITE3_TEXT); // Prevent XSS
        $stmt->bindValue(':id', $noteId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        return $stmt->execute();
    }
}
?>