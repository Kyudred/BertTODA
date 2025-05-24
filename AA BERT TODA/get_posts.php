<?php
include 'db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    if ($type === 'news') {
        $sql = "SELECT id, title, created_at as date_posted, 'news' as type, category FROM news";
        if ($search) {
            $sql .= " WHERE title LIKE :search";
        }
    } elseif ($type === 'projects') {
        $sql = "SELECT id, title, created_at as date_posted, 'projects' as type, category FROM projects";
        if ($search) {
            $sql .= " WHERE title LIKE :search";
        }
    } else {
        // For 'all' type
        $sql = "SELECT id, title, created_at as date_posted, 'news' as type, category FROM news
                UNION ALL
                SELECT id, title, created_at as date_posted, 'projects' as type, category FROM projects
                ORDER BY date_posted DESC";
    }

    $stmt = $pdo->prepare($sql);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($posts);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>
