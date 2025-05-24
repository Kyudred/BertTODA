<?php
include 'db_connection.php';

header('Content-Type: application/json');

try {
    $id = $_GET['id'];
    $type = $_GET['type'];
    
    if ($type === 'news') {
        $sql = "SELECT id, title, category, summary, content, facebook, image, created_at 
                FROM news WHERE id = ?";
    } else {
        $sql = "SELECT id, title, category, description, description as content, facebook, image, created_at 
                FROM projects WHERE id = ?";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($post);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
