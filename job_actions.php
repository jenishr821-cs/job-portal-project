<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$page = $_GET['page'] ?? 1;

// --- DELETE JOB ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job_id'])) {
    $delete_id = $_POST['delete_job_id'];
    $stmt = $conn->prepare("DELETE FROM company_jobs WHERE job_id = ? AND username = ?");
    $stmt->bind_param("is", $delete_id, $username);
    $stmt->execute();
    header("Location: myjobs.php?page=$page");
    exit();
}

// --- UPDATE JOB ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_job_id'])) {
    $update_id = $_POST['update_job_id'];
    $job_title = $_POST['job_title'];
    $job_type = $_POST['job_type'];
    $experience_required = $_POST['experience_required'];
    $qualifications = $_POST['qualifications'];
    $salary = $_POST['salary'];
    $number_of_openings = $_POST['number_of_openings'];
    $work_mode = $_POST['work_mode'];
    $responsibilities = $_POST['responsibilities'];
    $benefits= $_POST['benefits'];
    $job_description = $_POST['job_description'];
    

   $stmt = $conn->prepare("UPDATE company_jobs SET 
    job_title = ?, job_type = ?, experience_required = ?, qualifications = ?, salary = ?, 
    number_of_openings = ?, work_mode = ?, responsibilities = ?, benefits = ?, job_description = ?
    WHERE job_id = ? AND username = ?");
$stmt->bind_param("sssssissssis", $job_title, $job_type, $experience_required, $qualifications, $salary, $number_of_openings, $work_mode, $responsibilities, $benefits, $job_description, $update_id, $username);

    $stmt->execute();
    header("Location: myjobs.php?page=$page");
    exit();
}
