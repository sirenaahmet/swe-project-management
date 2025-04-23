<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$db_host = 'localhost';     // Database host
$db_user = 'root';          // Database username - change if different
$db_pass = '';              // Database password - change if different
$db_name = 'pets';          // Database name

// Create connection with error handling
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // For debugging only - remove in production
    echo "Database connection error: " . $e->getMessage();
    exit();
}