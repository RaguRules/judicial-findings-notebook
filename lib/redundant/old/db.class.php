<?php
class Database{
    public $db;
    public $dbpath = '../db/data.db'; 

    public function __construct($dbpath = null){
        if ($dbpath !== null) {
            $this->dbpath = $dbpath;
        }
        try{
            if(!file_exists($this->dbpath)){
                $dbDir = dirname($this->dbpath); // Get the directory part of the path
    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0755, true)) { // Create the directory recursively with permissions
            throw new Exception("Unable to create directory: $dbDir");
        }
    }
                if (!touch($this->dbpath)) { 
                    throw new Exception("Unable to create database file.");
                }
                $this->db = new SQLite3($this->dbpath);
                $this->createTables();
            }
            else{
                $this->db = new SQLite3("$dbpath");
            }
        }catch(Exception $e){
            error_log("error at creating/openning database: " . $e->getMessage());
            var_dump($this->db); // Check the value of $this->db
        }
    }


    private function createTables() {
        // SQL statements to create the tables
        $usersTableSQL = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )";

        $tokensTableSQL = "CREATE TABLE IF NOT EXISTS auth_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT UNIQUE NOT NULL,
            type TEXT NOT NULL, 
            created_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id)
        )";

        $notesTableSQL = "CREATE TABLE IF NOT EXISTS notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT,
            created_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id)
        )";

        // Execute the SQL statements
        $this->db->exec($usersTableSQL);
        $this->db->exec($tokensTableSQL);
        $this->db->exec($notesTableSQL);
    }

    public function getConn(){
        return $this->db;
    }
}



// class Database {
//     private $db;

//     public function __construct() {
//         $this->db = new \SQLite3('notes_app.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
//         $this->db->exec('PRAGMA foreign_keys = ON;'); // Ensure FK enforcement
//     }

//     public function getDb() {
//         return $this->db;
//     }

//     public function close() {
//         $this->db->close();
//     }
// }
?>