<?php
include 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $table = ($data['type'] === 'news') ? 'news' : 'projects';
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $result = $stmt->execute([$data['id']]);
    
    error_log("Deleting from table: $table with ID: " . $data['id']);
    echo json_encode(['success' => $result]);
} catch(PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
