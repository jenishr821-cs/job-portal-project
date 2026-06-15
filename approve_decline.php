<?php
session_start();
include 'db.php';
include 'notification_helper.php'; // 💡 Ensure this exists

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'], $_POST['application_id'], $_POST['action'])) {
    $job_id = $_POST['job_id'];
    $application_id = $_POST['application_id'];
    $action = $_POST['action']; // 'approved' or 'rejected'

    if (in_array($action, ['approved', 'rejected'])) {
        // ✅ Update application status
        $stmt = $conn->prepare("UPDATE job_applications SET application_status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $application_id);
        $stmt->execute();

        // ✅ Get info for notification
        $info = $conn->prepare("SELECT username, job_title, company_name FROM job_applications WHERE id = ?");
        $info->bind_param("i", $application_id);
        $info->execute();
        $result = $info->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $job_title = $row['job_title'];
            $company_name = $row['company_name'];

            $status_uc = ucfirst($action); // Approved or Rejected
           
            // ✅ Store notification
            $message = "
<p><span class='font-semibold'>Job Title:</span> $job_title</p>
<p><span class='font-semibold'>Company:</span> $company_name</p>
";
            addNotification($conn, $username, $message, "track_application.php?job_id=$job_id", "Approved"); // or "Rejected"

        }
    }
}

header("Location: applicants_resume.php");
exit;
