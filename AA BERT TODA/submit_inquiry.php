<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "brgy_sanisidro";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed: " . $conn->connect_error]);
    exit;
}

// Receive POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$age = $_POST['age'] ?? '';
$contact = $_POST['contact'] ?? '';
$gender = $_POST['gender-type'] ?? '';
$address = $_POST['address'] ?? '';
$inquiry_type = $_POST['inquiry-type'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (!$name || !$email || !$age || !$contact || !$gender || !$address || !$inquiry_type || !$message) {
    echo json_encode(["status" => "error", "message" => "❌ Missing fields."]);
    exit;
}

// Insert into DB (no consent column)
$stmt = $conn->prepare("INSERT INTO inquiry 
    (full_name, email, age, contact_number, gender, address, inquiry_type, message) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "❌ Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssisssss", 
    $name, $email, $age, $contact, $gender, $address, $inquiry_type, $message);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "✅ Your inquiry has been submitted."]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>