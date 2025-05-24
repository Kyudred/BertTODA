<?php
$pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");

// Upload image
$imagePath = "";
if (!empty($_FILES["project-featured-image"]["name"])) {
    $imageName = basename($_FILES["project-featured-image"]["name"]);
    $target = "uploads/" . $imageName;
    move_uploaded_file($_FILES["project-featured-image"]["tmp_name"], $target);
    $imagePath = $target;
}

// Insert project details into database
$stmt = $pdo->prepare("INSERT INTO projects (title, category, description, facebook, image) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([
    $_POST["project-title"],
    $_POST["project-category"],
    $_POST["project-description"],
    $_POST["project-facebook"],
    $imagePath
]);

header('Content-Type: application/json');
echo json_encode(["success" => true, "message" => "Project published successfully!"]);
?>