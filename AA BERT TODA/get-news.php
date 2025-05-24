<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");
$stmt = $pdo->query("SELECT id, title, category, summary, content, facebook, image, created_at AS date FROM news ORDER BY created_at DESC");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($news);
?>
