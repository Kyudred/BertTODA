<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=brgy_sanisidro", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'inquiry'");
    if ($stmt->rowCount() === 0) {
        echo "Table 'inquiry' does not exist\n";
        exit;
    }
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE inquiry");
    echo "Table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    // Check if there's any data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inquiry");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nNumber of records: " . $count . "\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
?> 