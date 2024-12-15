<?php
class Database {
    private $db;

    public function __construct() {
        $this->db = new \SQLite3('notes_app.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->db->exec('PRAGMA foreign_keys = ON;'); // Ensure FK enforcement
    }

    public function getDb() {
        return $this->db;
    }

    public function close() {
        $this->db->close();
    }
}
?>