<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=sk_news", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get post type and ID
    $postType = $_POST['type'] ?? '';
    $postId = $_POST['id'] ?? '';

    if (!$postType || !$postId) {
        throw new Exception('Missing required parameters');
    }

    // Handle image upload if new image is provided
    $imagePath = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.');
        }

        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            // Delete old image if exists
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = $uploadFile;
        } else {
            throw new Exception('Failed to upload image');
        }
    }

    // Update based on post type
    if ($postType === 'news') {
        $stmt = $pdo->prepare("
            UPDATE news 
            SET title = ?, 
                summary = ?, 
                content = ?, 
                category = ?, 
                facebook = ?,
                image = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['title'],
            $_POST['summary'],
            $_POST['content'],
            $_POST['category'],
            $_POST['facebook'] ?? null,
            $imagePath,
            $postId
        ]);
    } else if ($postType === 'projects') {
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET title = ?, 
                description = ?, 
                category = ?, 
                facebook = ?,
                image = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['category'],
            $_POST['facebook'] ?? null,
            $imagePath,
            $postId
        ]);
    } else {
        throw new Exception('Invalid post type');
    }

    // Check if update was successful
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Post updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No changes were made to the post'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
