<?php
session_start();
include 'db.php';
include 'notification_helper.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>alert('Login first'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'] ?? '';
    $company_name = $_POST['company_name'];
    $job_title = $_POST['job_title'];
    $job_type = $_POST['job_type'];
    $job_location = $_POST['job_location'];
    $benefits = $_POST['benefits'];
    $responsibilities = $_POST['responsibilities'];
    $experience_required = $_POST['experience_required'];
    $qualifications = $_POST['qualifications'];
    $salary = $_POST['salary'];
    $number_of_openings = $_POST['number_of_openings'];
    $work_mode = $_POST['work_mode'];
    $job_description = $_POST['job_description'];
    $contact_no = $_POST['contact_no'];
    $email = $_POST['email'];
    $company_logo = $_POST['company_logo'];

    // ✅ Prepare the SQL insert statement

    $stmt = $conn->prepare("INSERT INTO company_jobs (
    username, company_name, job_title, job_type, job_location,
    salary, experience_required, job_description, responsibilities,
    qualifications, contact_no, email, company_logo,
    number_of_openings, work_mode, benefits
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // --- Notify admins about new job ---
    $company  = $_SESSION['username']; // or company name variable
    $jobTitle = $job_title;            // replace with your job title variable

    $msg  = "New job posted: <strong>{$jobTitle}</strong> by <strong>{$company}</strong>";
    $link = "job_details.php?id=" . (int)$job_id;
    $type = 'job';

    $admins = $conn->query("SELECT username FROM users WHERE role='admin'");
    $ins = $conn->prepare("INSERT INTO notifications (receiver_username, message, link, type) VALUES (?,?,?,?)");
    while ($a = $admins->fetch_assoc()) {
        $adminUser = $a['username'];
        $ins->bind_param("ssss", $adminUser, $msg, $link, $type);
        $ins->execute();
    }
    $ins->close();


    $stmt->bind_param(
        "sssssssssssssiss", // 13 strings, 1 int, 1 int, 1 string
        $username,
        $company_name,
        $job_title,
        $job_type,
        $job_location,
        $salary,
        $experience_required,
        $job_description,
        $responsibilities,
        $qualifications,
        $contact_no,
        $email,
        $company_logo,
        $number_of_openings,
        $work_mode,
        $benefits
    );


    // ✅ Execute and notify
    if ($stmt->execute()) {
        $job_id = $conn->insert_id;

        // Notify all users
        $users = $conn->query("SELECT username FROM users WHERE role = 'user'");
        while ($user = $users->fetch_assoc()) {
            addNotification(
                $conn,
                $user['username'],
                "<div class='space-y-1 leading-6'>
        <p>New job posted: <span class='font-semibold'>{$job_title}</span> at <span class='font-semibold'>{$company_name}</span></p>
        <p class='text-slate-400'>View job details below.</p>
    </div>",
                "job_matches.php"
            );
        }



        echo "<script>alert('Job posted successfully'); window.location.href='create_resume.php';</script>";
    } else {
        echo "<script>alert('Error posting job.'); window.history.back();</script>";
    }

    $stmt->close();
}

//admin message
// after inserting company_jobs and obtaining $job_id
$job_id = $conn->insert_id; // if using mysqli and last insert id
$company = $company_name;   // your variable
$jobTitle = $job_title;     // your variable

$msg  = "New job posted: <strong>{$jobTitle}</strong> by <strong>{$company}</strong>";
$link = "job_details.php?id=" . (int)$job_id; // adjust URL if different
$type = 'job';

// Notify every admin
$admins = $conn->query("SELECT username FROM users WHERE role = 'admin'");
$ins = $conn->prepare("INSERT INTO notifications (receiver_username, message, link, type) VALUES (?, ?, ?, ?)");
while ($a = $admins->fetch_assoc()) {
    $adminUser = $a['username'];
    $ins->bind_param("ssss", $adminUser, $msg, $link, $type);
    $ins->execute();
}
$ins->close();
