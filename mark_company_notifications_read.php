<?php
session_start();
include 'db.php';

$company_username = $_SESSION['username'] ?? '';
if ($company_username !== '') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_username = ?");
    $stmt->bind_param("s", $company_username);
    $stmt->execute();
}
?>
