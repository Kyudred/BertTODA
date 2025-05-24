<?php
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");
$news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
?>

<div class="news-grid">
<?php foreach ($news as $item): ?>
  <div class="news-card">
    <img src="<?= htmlspecialchars($item['image']) ?>" alt="News Image">
    <h2><?= htmlspecialchars($item['title']) ?></h2>
    <p><?= htmlspecialchars($item['summary']) ?></p>
    <?php if ($item['facebook']): ?>
      <a href="<?= htmlspecialchars($item['facebook']) ?>" target="_blank">View on Facebook</a>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
