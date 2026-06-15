<?php
session_start();
include 'db.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $company_name = $_POST['company_name']  ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['description'] ?? '';
    $logo = $_POST['logo'] ?? '';

    

    // Prevent duplicate
  $check = mysqli_query($conn, "SELECT * FROM company_profiles WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $update = "UPDATE company_profiles SET company_name='$company_name',  address='$address', username='$username', email='$email',phone='$phone',
        description='$description', logo='$logo' WHERE username='$username'";
        mysqli_query($conn, $update);
    } else {
        $insert = "INSERT INTO company_profiles (username, company_name, address, email, phone, description,  logo)
                   VALUES ('$username', '$company_name',  '$address', '$email', '$phone', '$description',  '$logo')";
        mysqli_query($conn, $insert);
    }

    echo "<script>alert('Profile saved successfully.'); window.location.href='index.php';</script>";
}



