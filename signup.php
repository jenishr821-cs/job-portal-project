<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Check for Gmail-only email
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        echo "<script>alert('Only Gmail addresses are allowed.'); window.location.href='login.php';</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Auto-detect role based on company keywords
    $username_lower = strtolower($username);
    if (
        strpos($username_lower, 'company') !== false ||
        strpos($username_lower, 'pvt ltd') !== false ||
        strpos($username_lower, 'private limited') !== false ||
        strpos($username_lower, 'limited') !== false ||
        strpos($username_lower, 'pvt') !== false ||
        strpos($username_lower, 'ltd') !== false 

) {
        $role = 'company';
    } else {
        $role = 'user';
    }

    // Store in database
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Signup successful!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: Could not sign up');</script>";
    }
}
?>


