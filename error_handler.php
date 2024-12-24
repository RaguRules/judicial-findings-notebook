<?php

// Custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log the error details to a file or error logging service
    error_log("Error: [$errno] $errstr in $errfile on line $errline");

    // Display a generic error message to the user (optional)
    // echo "An error occurred. Please try again later.";

    // Prevent the default error handling
    return true;
}

// Set the custom error handler
set_error_handler("customErrorHandler");

?>