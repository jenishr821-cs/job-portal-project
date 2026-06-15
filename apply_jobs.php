<?php
session_start();
include 'db.php';
require_once 'notification_helper.php'; // include only once

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];
    $username = $_SESSION['username'];

    // 🔹 Step 1: Check if user already applied for this job
    $check = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND username = ?");
    $check->bind_param("is", $job_id, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // User already applied
        echo "<script>
                alert('⚠️ You have already applied for this job.');
                window.location.href = 'index.php';
              </script>";
        $check->close();
    } else {
        $check->close();

        // 🔹 Step 2: Get company_name, job_title, and company_username for this job
        $stmt2 = $conn->prepare("SELECT company_name, job_title, username FROM company_jobs WHERE job_id = ?");
        $stmt2->bind_param("i", $job_id);
        $stmt2->execute();
        $stmt2->bind_result($company_name, $job_title, $company_username);
        $stmt2->fetch();
        $stmt2->close();

        if (!empty($company_name) && !empty($job_title)) {
            // 🔹 Step 3: Fetch applicant fullname
            $fullname = '';
            $stmt3 = $conn->prepare("SELECT fullname FROM user_profiles WHERE username = ?");
            $stmt3->bind_param("s", $username);
            $stmt3->execute();
            $stmt3->bind_result($fullname);
            $stmt3->fetch();
            $stmt3->close();

            // 🔹 Step 4: Insert new application
            $stmt = $conn->prepare("INSERT INTO job_applications (job_id, username, fullname, company_name, job_title, application_status, applied_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("issss", $job_id, $username, $fullname, $company_name, $job_title);

            if ($stmt->execute()) {
                // 🔹 Step 5: Send notification to company
                if (!empty($company_username)) {
                    $message = "
                        <p><span class='font-semibold'>$fullname</span> applied for <span class='font-semibold'>$job_title</span></p>
                        <p class='text-slate-400'></p>
                    ";
                    addNotification($conn, $company_username, $message, "view_applicants.php?job_id=$job_id");
                }

                echo "<script>
                        alert('✅ Application submitted and notification sent!');
                        window.location.href = 'index.php';
                      </script>";
            } else {
                echo "<script>
                        alert('❌ Error applying for job. Please try again.');
                        window.location.href = 'index.php';
                      </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                    alert('❌ Job details not found.');
                    window.location.href = 'index.php';
                  </script>";
        }
    }
}

$conn->close();
?>
