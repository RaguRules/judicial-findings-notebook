<?php
// config.php (located in the lib/ directory)

// Define the absolute path to the database file
define('DB_PATH', realpath(__DIR__ . '/../model/data.db')); 

// Other configuration constants can be defined here if needed
// API
// define('API_BASE_URL', 'http://localhost/api');
// define('API_VERSION', 'V1');

// Security
// define('JWT_SECRET_KEY', 'your_very_strong_secret_key'); // For JWT, if you implement it
// define('RATE_LIMIT_WINDOW', 60);
// define('RATE_LIMIT_MAX_REQUESTS', 30);

// Application
// define('APP_NAME', 'My Notes App');
// define('APP_ENV', 'development'); // Or 'production'
// define('SITE_URL', 'http://localhost/my-project');
// define('EMAIL_FROM', 'noreply@mydomain.com');

// // File Paths
// define('LOG_DIR', __DIR__ . '/../logs');
// define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
?>