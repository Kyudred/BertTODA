<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import PHPMailer classes manually (no Composer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer source files
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Get POST data
$inquiryId = $_POST['inquiry_id'] ?? null;
$status = $_POST['status'] ?? '';
$response = $_POST['response'] ?? '';

if (!$inquiryId || !$response) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "brgy_sanisidro");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

// Get recipient info
$sql = "SELECT email, full_name FROM inquiry WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inquiryId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Inquiry not found."]);
    exit;
}
$row = $result->fetch_assoc();
$to = $row['email'];
$name = $row['full_name'];

// Prepare and send email using Gmail SMTP
$mail = new PHPMailer(true);
try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'brgysanisidrosk01@gmail.com';          // Your Gmail address
    $mail->Password = 'rjdbtfygyezotunt';            // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Email content
    $mail->setFrom('brgysanisidrosk01@gmail.com', 'SK Barangay San Isidro');
    $mail->addAddress($to, $name);
    $mail->isHTML(true);
    $mail->Subject = 'Response to Your Inquiry';
    $mail->Body = nl2br(htmlspecialchars($response));

    $mail->send();

    // Update the database with response and new status
    $update = $conn->prepare("UPDATE inquiry SET response = ?, status = ? WHERE id = ?");
    $update->bind_param("ssi", $response, $status, $inquiryId);
    $update->execute();

    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
}
?>
