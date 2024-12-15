<?php
class AuthManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a user account
    public function createUser($username, $password) {
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindValue(':username', htmlspecialchars($username), SQLITE3_TEXT); // Prevent XSS
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT); // Hash password
        return $stmt->execute();
    }

    // Login and generate a token
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->bindValue(':username', htmlspecialchars($username), SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            // Generate a token
            $token = bin2hex(random_bytes(16));
            $stmt = $this->db->prepare("INSERT INTO auth_tokens (user_id, token, created_at) VALUES (:user_id, :token, :created_at)");
            $stmt->bindValue(':user_id', $result['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
            $stmt->execute();

            return $token; // Return token to client
        }
        return false; // Invalid credentials
    }

    // Validate a token
    public function validateToken($token) {
        $stmt = $this->db->prepare("SELECT user_id FROM auth_tokens WHERE token = :token AND created_at >= datetime('now', '-1 day')");
        $stmt->bindValue(':token', $token, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return $result ? $result['user_id'] : false; // Return user_id or false if invalid
    }
}


?>