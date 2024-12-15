<?php
require_once '../../../Lib/Database.class.php';
require_once '../../../Lib/Validation.class.php';

use Database;
use Validation;

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getDb();

$username = Validation::sanitizeInput($_POST['username'] ?? '');
$password = Validation::sanitizeInput($_POST['password'] ?? '');

if (!Validation::isValidUsername($username) || !Validation::isValidPassword($password)) {
    echo json_encode(['error' => 'Invalid username or password']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

try {
    $stmt->execute();
    echo json_encode(['message' => 'User registered successfully']);
} catch (\Exception $e) {
    echo json_encode(['error' => 'Username already exists']);
}

?>