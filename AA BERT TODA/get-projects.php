<?php
// Ensure proper JSON content type
header('Content-Type: application/json');

// Error handling
try {
    // Connect to database
    $pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Query for projects
    $stmt = $pdo->query("SELECT id, title, category, description, facebook, image, created_at AS date FROM projects ORDER BY created_at DESC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output JSON
    echo json_encode($projects);
} catch (PDOException $e) {
    // Return error as JSON
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>