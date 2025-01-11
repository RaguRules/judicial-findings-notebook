<?php

header('Location: http://localhost/public/login.php'); 

// 2. Include essential files (if any)
//   - Configuration files
//   - Database connection
//   - Helper functions

// 3. Start session (if needed)
// session_start();

// 4. Handle user authentication (if not already done in login.php)
//   - Check if the user is logged in (e.g., check for a session variable)
//   - If not logged in, redirect to login.php

// 5. Routing (basic example)
//   - Determine which page to load based on the requested URL
// $page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Default to home.php

// switch ($page) {
//   case 'home':
//     include 'home.php';
//     break;
//   case 'register':
//     include 'register.php';
//     break;
//   case 'login':
//     include 'login.php';
//     break;
//   default:
//     // Handle invalid page requests (e.g., show a 404 error page)
//     include '404.php'; 
//     break;
// }

?>