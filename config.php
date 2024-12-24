<?php

// Database Configuration
define('DB_PATH', '../../../model/data.db');

// Authentication Configuration
define('ACCESS_TOKEN_EXPIRY', 5); // Expiry time for access tokens in minutes
define('REFRESH_TOKEN_EXPIRY', 30 * 24 * 60); // Expiry time for refresh tokens in minutes
define('JWT_SECRET_KEY', 'your-secret-key'); // Secret key for JWT signing (if using JWT)

// API Configuration
define('API_BASE_URL', 'http://your-api-domain.com/api/v1'); // Base URL for your API
define('ALLOWED_ORIGINS', ['http://your-frontend-domain.com']); // Allowed origins for CORS

// Email Configuration (if applicable)
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_USERNAME', 'your-smtp-username');
define('SMTP_PASSWORD', 'your-smtp-password');
define('SMTP_PORT', 587); // Example port
define('SMTP_FROM_EMAIL', 'your-from-email@example.com');

// Other Application-Specific Configuration
define('UPLOAD_DIR', '../../../uploads'); // Directory for file uploads (if applicable)
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // Maximum upload size in bytes (2MB)

// ... add other configuration settings as needed ...

?>