<?php
// connect to DB
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch single post
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$projects = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projects) {
    echo "❌ Project not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($projects['title']) ?></title>
    <link rel="stylesheet" href="css/projects.css">
</head>
<body>
    <div class="main-container">
        <h1><?= htmlspecialchars($projects['title']) ?></h1>
        <p><strong>Date Posted:</strong> <?= date('F j, Y', strtotime($projects['created_at'])) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($projects['category']) ?></p>
        <div class="project-image">
            <img src="<?= htmlspecialchars($projects['image']) ?>" alt="Project Image" style="max-width: 100%; height: auto;">
        </div>
        <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($projects['summary'])) ?></p>
        <div class="project-content"><?= $projects['content'] ?></div>

        <?php if (!empty($projects['facebook'])): ?>
            <p><a href="<?= htmlspecialchars($projects['facebook']) ?>" target="_blank">📎 View on Facebook</a></p>
        <?php endif; ?>
        
        <p><a href="project.php">← Back to Projects</a></p>
    </div>
</body>
</html>
