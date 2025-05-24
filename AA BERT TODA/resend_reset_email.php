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

header('Content-Type: application/json');

$host = 'localhost';
$db = 'sk_registration';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$oldToken = $data['token'] ?? '';
if (!$oldToken) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing token']);
    exit();
}

// Find user by old token
$stmt = $conn->prepare("SELECT id, email FROM users WHERE reset_token = ?");
$stmt->bind_param('s', $oldToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit();
}

$user = $result->fetch_assoc();

// Generate a new secure token
$newToken = bin2hex(random_bytes(16));
$newExpiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

// Update the user with the new token and expiry
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
$update->bind_param('ssi', $newToken, $newExpiry, $user['id']);
if (!$update->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update reset token']);
    exit();
}

// Prepare the reset email
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.your-email-server.com'; // Replace with your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@example.com'; // Your SMTP username
    $mail->Password = 'your-email-password';   // Your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('no-reply@example.com', 'Your App Name');
    $mail->addAddress($user['email']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $resetLink = "https://yourdomain.com/reset.html?token=$newToken";
    $mail->Body = "Hello,<br><br>Click the link below to reset your password:<br><a href=\"$resetLink\">$resetLink</a><br><br>This link will expire in 1 hour.";

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
}

$conn->close();
exit();
