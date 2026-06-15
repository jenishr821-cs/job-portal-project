<?php
session_start();
include 'db.php';

$username = $_SESSION['username'] ?? '';

if ($username !== '') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo "success";
?>
