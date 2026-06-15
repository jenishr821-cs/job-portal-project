<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    if ($username) {
        // delete company profile
        $stmt1 = $conn->prepare("DELETE FROM company_profiles WHERE username = ?");
        $stmt1->bind_param("s", $username);
        $stmt1->execute();
        $stmt1->close();

        // delete user entry
        $stmt2 = $conn->prepare("DELETE FROM users WHERE username = ? AND role = 'company'");
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $stmt2->close();
    }
}

header("Location: manage_companies.php");
exit();
