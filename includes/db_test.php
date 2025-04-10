<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - same as in includes/db.php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'pets';

echo "<h1>Database Connection Test</h1>";

try {
    // Create connection
    echo "<p>Attempting to connect to database...</p>";
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p style='color: green;'>Connected successfully to database: <strong>{$db_name}</strong></p>";
    
    // Try a simple query
    echo "<p>Testing a simple query...</p>";
    
    $result = $conn->query("SHOW TABLES");
    
    if ($result) {
        echo "<p style='color: green;'>Query successful!</p>";
        
        echo "<h2>Tables in database:</h2>";
        echo "<ul>";
        
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        
        echo "</ul>";
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Close connection
    $conn->close();
    echo "<p>Connection closed.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h2>Troubleshooting:</h2>";
    echo "<ul>";
    echo "<li>Check if MySQL service is running</li>";
    echo "<li>Verify database credentials (username/password)</li>";
    echo "<li>Make sure the 'pets' database exists</li>";
    echo "<li>Check if your user has permission to access the database</li>";
    echo "</ul>";
}