<?php
session_start();

$host = 'localhost'; // or your server
$db = 'sk_registration'; // your database name
$user = 'root'; // your database username
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, password FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        header("Location: admin-dashboard.html");
        exit();
    } else {
        header("Location: login.html?error=wrongpassword");
    exit();
    }
} else {
    header("Location: login.html?error=usernotfound");
    exit();
}



$stmt->close();
$conn->close();
?>
