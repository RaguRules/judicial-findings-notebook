<?php

require_once "../../../lib/auth.class.php";
// require_once "../../../lib/db.class.php";

// $db = new Database;
// $conn = db->getConn();

// $auth = new Auth($username, $password);
// $auth -> signup($username, $password);



// Function to send JSON response with status code
function sendJsonResponse($statusCode, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'signup') { 
        if (isset($data['username']) && isset($data['password'])) {
            $auth = new AuthManager($data['username'], $data['password']);
            $result = $auth->signup($data['username'], $data['password']);
            if ($result === true) {
                sendJsonResponse(201, ['status' => 'success', 'message' => 'User created successfully']);
            } else {
                sendJsonResponse(400, ['status' => 'error', 'message' => $result]); 
            }
        } else {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Missing username or password']); 
        }
    }else {
        sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid action']); 
    }
} else {
    sendJsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']); 
}













// use Database;
// use Validation;

// header('Content-Type: application/json');

// $db = new Database();
// $conn = $db->getDb();

// $username = Validation::sanitizeInput($_POST['username'] ?? '');
// $password = Validation::sanitizeInput($_POST['password'] ?? '');

// if (!Validation::isValidUsername($username) || !Validation::isValidPassword($password)) {
//     echo json_encode(['error' => 'Invalid username or password']);
//     exit;
// }

// $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
// $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
// $stmt->bindValue(':username', $username, SQLITE3_TEXT);
// $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

// try {
//     $stmt->execute();
//     echo json_encode(['message' => 'User registered successfully']);
// } catch (\Exception $e) {
//     echo json_encode(['error' => 'Username already exists']);
// }

?>