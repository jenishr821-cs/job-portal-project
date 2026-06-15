<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Not logged in']);
    exit();
}

$admin = $_SESSION['username'];

// Only allow admins
$roleCheck = $conn->prepare("SELECT role FROM users WHERE username=?");
$roleCheck->bind_param("s", $admin);
$roleCheck->execute();
$r = $roleCheck->get_result()->fetch_assoc();
if (!$r || $r['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'msg' => 'Forbidden']);
    exit();
}

// Mark all as read
$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE receiver_username=? AND is_read=0");
$stmt->bind_param("s", $admin);
$stmt->execute();

echo json_encode(['status' => 'ok']);
