<?php
require_once '../../../Lib/Database.php';
require_once '../../../Lib/Auth.php';
require_once '../../../Lib/Validation.php';

use Database;
use Auth;
use Validation;

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getDb();
$auth = new Auth();

$username = Validation::sanitizeInput($_POST['username'] ?? '');
$password = Validation::sanitizeInput($_POST['password'] ?? '');

$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $token = $auth->generateToken($user['id']);
    echo json_encode(['token' => $token]);
} else {
    echo json_encode(['error' => 'Invalid credentials']);
}
?>