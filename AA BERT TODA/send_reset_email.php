<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Send JSON headers
header('Content-Type: application/json');

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Database connection
$host = "localhost";
$username = "root";            // ✅ Change to your actual DB username
$password = "";                // ✅ Change to your actual DB password
$dbname = "sk_registration";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get email from POST
$email = $_POST['email'] ?? '';
if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required."]);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found."]);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Generate token and expiry
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Save token
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
$update->bind_param("ssi", $token, $expires, $userId);
$update->execute();

// Create reset link
$resetLink = "http://localhost/AA%20BERT%20TODA/reset.html?token=" . urlencode($token);

// Send email with PHPMailer
$mail = new PHPMailer(true);
try {
    // SMTP config
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'brgysanisidrosk01@gmail.com';
    $mail->Password = 'rjdbtfygyezotunt'; // App password (NOT Gmail login)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Sender and receiver
    $mail->setFrom('brgysanisidrosk01@gmail.com', 'SK Barangay San Isidro');
    $mail->addAddress($email);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Password';
    $mail->Body = "
        <h3>You forgot your password?</h3>
        <p>Click the link below to reset it:</p>
        <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "'>Reset Password</a>
        <br><br>
        <small>This link will expire in 1 hour.</small>
    ";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Email sent."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
}
?>
