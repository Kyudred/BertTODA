<?php
// connect to DB
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch single post
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    echo "❌ News not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($news['title']) ?></title>
    <link rel="stylesheet" href="css/news.css">
</head>
<body>
    <div class="main-container">
        <h1><?= htmlspecialchars($news['title']) ?></h1>
        <p><strong>Date Posted:</strong> <?= date('F j, Y', strtotime($news['created_at'])) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($news['category']) ?></p>
        <div class="news-image">
            <img src="<?= htmlspecialchars($news['image']) ?>" alt="News Image" style="max-width: 100%; height: auto;">
        </div>
        <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($news['summary'])) ?></p>
        <div class="news-content"><?= $news['content'] ?></div>

        <?php if (!empty($news['facebook'])): ?>
            <p><a href="<?= htmlspecialchars($news['facebook']) ?>" target="_blank">📎 View on Facebook</a></p>
        <?php endif; ?>
        
        <p><a href="news.php">← Back to News</a></p>
    </div>
</body>
</html>
