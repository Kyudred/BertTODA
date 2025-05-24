<?php
// Database connection settings
define('DB_HOST', 'localhost');       // or your host (e.g., 127.0.0.1)
define('DB_USER', 'root');   // replace with your MySQL username
define('DB_PASS', '');   // replace with your MySQL password
define('DB_NAME', 'sk_registration'); // this is your database name

// OPTIONAL: You can also create a reusable connection function if needed
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
