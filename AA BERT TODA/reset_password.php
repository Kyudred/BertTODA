<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/reset_errors.log');
error_reporting(E_ALL);

ob_start();

header('Content-Type: application/json');

$host = 'localhost';
$db = 'sk_registration';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    ob_end_flush();
    exit();
}

$raw = file_get_contents('php://input');
file_put_contents(__DIR__ . '/debug_reset_input.log', $raw . PHP_EOL, FILE_APPEND);
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    ob_end_flush();
    exit();
}

$token = $data['token'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (!$token || !$newPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing token or new password']);
    ob_end_flush();
    exit();
}

// Find user by token and check expiry
$stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    ob_end_flush();
    exit();
}
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    ob_end_flush();
    exit();
}

$user = $result->fetch_assoc();
$expiry = strtotime($user['reset_expires']);
if ($expiry < time()) {
    echo json_encode(['success' => false, 'tokenExpired' => true, 'message' => 'Token expired']);
    ob_end_flush();
    exit();
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password and clear reset token fields
$update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
if (!$update) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    ob_end_flush();
    exit();
}
$update->bind_param('si', $hashedPassword, $user['id']);

if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password reset successful']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
}

$conn->close();
ob_end_flush();
exit();
?>
