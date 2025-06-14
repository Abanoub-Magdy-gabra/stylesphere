<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');      // Default XAMPP username
define('DB_PASSWORD', '');          // Default XAMPP password is empty
define('DB_NAME', 'style_quiz');    // You'll need to create this database

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>
