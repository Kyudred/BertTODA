<?php
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");
$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
?>

<div class="projects-grid">
<?php foreach ($projects as $item): ?>
  <div class="projects-card">
    <img src="<?= htmlspecialchars($item['image']) ?>" alt="Project Image">
    <h2><?= htmlspecialchars($item['title']) ?></h2>
    <p><?= htmlspecialchars($item['description']) ?></p>
    <?php if ($item['facebook']): ?>
      <a href="<?= htmlspecialchars($item['facebook']) ?>" target="_blank">View on Facebook</a>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
