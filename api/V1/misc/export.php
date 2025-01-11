// api/V1/misc/export.php

require_once __DIR__ . '/../../../lib/auth.class.php';
require_once __DIR__ . '/../../../lib/db.class.php';
require_once __DIR__ . '/../../../lib/config.php';

// --- Initialize Classes ---
try {
    $db = new Database(DB_PATH);
    $authManager = new AuthManager($db, $config['security']);
} catch (Exception $e) {
    error_log('Initialization error: ' . $e->getMessage());
    sendJsonResponse(500, ['status' => 'error', 'message' => 'Internal Server Error']);
}

// Get the current user's ID based on the token
$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$accessToken = str_replace('Bearer ', '', $authorizationHeader);

if (empty($accessToken)) {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Authorization header missing.']);
}

// Authenticate the user
$tokenValid = $authManager->validateToken($accessToken);

if ($tokenValid['status'] !== 'success') {
    sendJsonResponse(401, ['status' => 'error', 'message' => 'Invalid or expired access token.']);
}

// Database file path (should be outside of webroot)
$databasePath = "/absolute/path/to/your/file/data.db";  // Make sure the path is correct

if (!file_exists($databasePath)) {
    sendJsonResponse(404, ['status' => 'error', 'message' => 'Database file not found.']);
    exit;
}

// Ensure the file size is correct
$fileSize = filesize($databasePath);
if ($fileSize === 0) {
    sendJsonResponse(500, ['status' => 'error', 'message' => 'The database file is empty.']);
    exit;
}

// Disable output buffering and ignore user abort (so the script doesn't stop unexpectedly)
ignore_user_abort(true);

// Clear any previous output to ensure clean file transfer
ob_clean();
flush();

// Set appropriate headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="data.db"');  // Ensure proper file name
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $fileSize);  // Send the correct content-length

// Output the database file content to the browser
$handle = fopen($databasePath, 'rb');  // Open file in binary mode
if ($handle === false) {
    sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to open the file.']);
    exit;
}

while (!feof($handle)) {
    // Read the file in chunks and send to the browser
    echo fread($handle, 8192);  // Read in 8 KB chunks
    flush();  // Flush output buffer to ensure the file is sent in chunks
}

fclose($handle);  // Close the file handle
exit;
