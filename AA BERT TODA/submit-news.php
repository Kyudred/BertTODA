<?php
header('Content-Type: application/json');
include 'db_connection.php';

try {
    // Validate required fields
    if (empty($_POST['news-title']) || empty($_POST['news-category']) || empty($_POST['news-summary']) || empty($_POST['news-content'])) {
        throw new Exception('Please fill in all required fields.');
    }

    // Prepare the data
    $title = $_POST['news-title'];
    $category = $_POST['news-category'];
    $summary = $_POST['news-summary'];
    $content = $_POST['news-content'];
    $facebook = !empty($_POST['news-facebook']) ? $_POST['news-facebook'] : null;
    $created_at = date('Y-m-d H:i:s');

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['news-featured-image']) && $_FILES['news-featured-image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/news/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['news-featured-image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.');
        }

        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['news-featured-image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO news (title, category, summary, content, facebook, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $category, $summary, $content, $facebook, $image_path, $created_at]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'News published successfully!'
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
