<?php
session_start();
include 'db.php'; // your database connection

if (!isset($_SESSION['username'])) {
    echo "<script>alert('You must be logged in to upload a resume.'); window.location.href='index.php';</script>";
    exit;
}

$username = $_SESSION['username'];

if (isset($_FILES['resume_file'])) {
    $file_name = $_FILES['resume_file']['name'];
    $file_tmp = $_FILES['resume_file']['tmp_name'];
    $file_size = $_FILES['resume_file']['size'];

    $allowed_extensions = ['pdf', 'doc', 'docx', 'rtf', 'txt'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_extensions)) {
        echo "<script>alert('Invalid file type! Only PDF, DOC, DOCX, RTF, TXT allowed.'); window.location.href='index.php';</script>";
        exit;
    }

    // Create uploads folder if not exists
    $upload_dir = "uploads/resumes/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_file_name = uniqid('resume_') . "." . $ext;
    $file_path = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO resumes (username, file_name, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $file_name, $file_path);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('✅ Resume uploaded successfully!'); window.location.href='index.php?upload=success';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Failed to upload file. Please try again.'); window.location.href='index.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No file selected!'); window.location.href='index.php';</script>";
    exit;
}
?>
