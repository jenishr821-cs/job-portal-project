<?php
session_start();
include 'db.php';

// Only admin can ban/unban
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $status   = $_POST['status'] ?? '';

    if ($username && in_array($status, ['active', 'banned'], true)) {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE username = ?");
        $stmt->bind_param("ss", $status, $username);
        $stmt->execute();
        $stmt->close();

        // Add notification
        $msg = ($status === 'banned') 
                ? '⛔ Your account has been banned by admin.' 
                : '✅ Your account has been unbanned.';
        $link = 'profile.php?username=' . urlencode($username);
        $type = 'status';

        $note = $conn->prepare("
            INSERT INTO notifications (receiver_username, message, link, type)
            VALUES (?, ?, ?, ?)
        ");
        $note->bind_param("ssss", $username, $msg, $link, $type);
        $note->execute();
        $note->close();
    }
}

header("Location: manage_user.php");
exit();
