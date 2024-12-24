<?php

// Function to generate random tokens
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Function to validate an email address
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to sanitize a string for HTML output
function sanitizeForHTML($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to redirect to a URL
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Function to get the current date and time in a specific format
function getCurrentDateTime($format = 'Y-m-d H:i:s') {
    return date($format);
}

// Function to check if an array is associative
function isAssociativeArray(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

// Function to convert an array to a comma-separated string
function arrayToCSV(array $arr) {
    return implode(',', $arr);
}

// ... add other utility functions as needed ...

?>