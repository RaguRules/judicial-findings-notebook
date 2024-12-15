<?php
class Validation {
    public static function sanitizeInput($input) {
        return trim(htmlspecialchars($input));
    }

    public static function isValidUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username); // Alphanumeric, 3-20 chars
    }

    public static function isValidPassword($password) {
        return strlen($password) >= 6; // Minimum 6 characters
    }
}
?>