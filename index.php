<?php
session_start();
include 'db.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");




//for user bell icon notification
$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$notifications = [];
$noti_count = 0;

if ($username !== '') {
    // 1. Get unread count
    $stmt1 = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM notifications 
        WHERE receiver_username = ? AND is_read = 0
    ");
    $stmt1->bind_param("s", $username);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $noti_count = $result1->fetch_assoc()['unread_count'] ?? 0;
    $stmt1->close();

    // 2. Get logo + name based on current user
    if ($role === 'user') {
        $stmt2 = $conn->prepare("
            SELECT n.message, n.created_at, c.company_name AS fullname, c.logo 
            FROM notifications n
            LEFT JOIN company_profiles c ON n.receiver_username = c.username
            WHERE n.receiver_username = ?
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        $stmt2->bind_param("s", $username);
    } elseif ($role === 'company') {
        $stmt2 = $conn->prepare("
            SELECT n.message, n.created_at, u.fullname, u.logo 
            FROM notifications n
            LEFT JOIN user_profiles u ON n.receiver_username = u.username
            WHERE n.receiver_username = ?
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        $stmt2->bind_param("s", $username);
    }

    // 3. Execute notifications query
    if (isset($stmt2)) {
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        while ($row = $result2->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt2->close();
    }
}

// Helper function
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60)
        return 'Just now';
    elseif ($diff < 3600)
        return floor($diff / 60) . ' min ago';
    elseif ($diff < 86400)
        return floor($diff / 3600) . ' hrs ago';
    else
        return date("d M Y", $time);
}







if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

//for comapny job posts
$company_username = $_SESSION['username'] ?? '';

// Get total jobs
$job_query = $conn->prepare("SELECT COUNT(*) AS total_jobs FROM company_jobs WHERE username = ?");
$job_query->bind_param("s", $company_username);
$job_query->execute();
$job_result = $job_query->get_result()->fetch_assoc();
$total_jobs = $job_result['total_jobs'] ?? 0;

// Get latest job post date
$latest_query = $conn->prepare("SELECT MAX(created_at) AS last_posted FROM company_jobs WHERE username = ?");
$latest_query->bind_param("s", $company_username);
$latest_query->execute();
$latest_result = $latest_query->get_result()->fetch_assoc();
$last_posted = $latest_result['last_posted'] ?? 'N/A';
$last_posted_display = $last_posted !== 'N/A' ? date('M d, Y', strtotime($last_posted)) : 'No jobs posted';

//for user application to company showable
$applications_count = 0;

$application_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM job_applications ja
    JOIN company_jobs cj ON ja.job_id = cj.job_id
    WHERE cj.username = ?
");
$application_stmt->bind_param("s", $company_username);
$application_stmt->execute();
$application_result = $application_stmt->get_result();
if ($row = $application_result->fetch_assoc()) {
    $applications_count = $row['total'];
}


//for user resume score
function calculateResumeScore($resume)
{
    $score = 0;

    // Experience scoring
    if ($resume && isset($resume['experience'])) {
        $years = (int)$resume['experience'];

        if ($years >= 3) {
            $score += 40;
        } elseif ($years >= 2) {
            $score += 30;
        } elseif ($years >= 1) {
            $score += 20;
        }
    } else {
        $years = 0; // default if resume or experience is missing
    }

    // Skills scoring
    $high_value_skills = ['python', 'machine learning', 'data science', 'react', 'node.js'];
    $user_skills = explode(',', strtolower($resume['skills'] ?? ''));
    foreach ($high_value_skills as $skill) {
        if (in_array(trim($skill), $user_skills)) {
            $score += 10;
        }
    }

    // Degree scoring
    $degree = strtolower($resume['degree'] ?? '');

    if (strpos($degree, 'phd') !== false) {
        $score += 20;
    } elseif (
        strpos($degree, 'master') !== false ||
        strpos($degree, 'mca') !== false ||
        strpos($degree, 'msc') !== false
    ) {
        $score += 15;
    } elseif (
        strpos($degree, 'bachelor') !== false ||
        strpos($degree, 'bca') !== false
    ) {
        $score += 10;
    }

    return min($score, 100);
}

$username = $_SESSION['username'];
$resume = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM create_resume WHERE username = '$username'"));
$resume_score = calculateResumeScore($resume);


$username = $_SESSION['username'];
$role = $_SESSION['role'];

include 'db.php';
$username = $_SESSION['username'] ?? null;
$query = "SELECT fullname, email, logo FROM user_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);


$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);
// or 'email' or 'user_id' based on your setup

$jobs_applied_count = 0;
$all_jobs_count = 0;

if ($_SESSION['role'] === 'user') {
    $username = $_SESSION['username'];
    $stmt_applied = $conn->prepare("SELECT COUNT(*) as count FROM job_applications WHERE username = ?");
    $stmt_applied->bind_param("s", $username);
    $stmt_applied->execute();
    $jobs_applied_count = $stmt_applied->get_result()->fetch_assoc()['count'];
}

// Count all jobs posted
$stmt_jobs = $conn->prepare("SELECT COUNT(*) as count FROM company_jobs");
$stmt_jobs->execute();
$all_jobs_count = $stmt_jobs->get_result()->fetch_assoc()['count'];


//profile completeness
$profile_percent = 0;

if ($_SESSION['role'] === 'user') {
    $username = $_SESSION['username'] ?? '';
    $sql = "SELECT fullname, phone, address, email, birthdate, language, aboutme, skills FROM user_profiles WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();

    $fields = ['fullname', 'phone', 'address', 'email', 'birthdate', 'language', 'aboutme', 'skills'];
    $filled = 0;

    foreach ($fields as $field) {
        if (!empty($profile[$field])) {
            $filled++;
        }
    }

    $profile_percent = round(($filled / count($fields)) * 100);
}

//progress bar
if ($profile_percent >= 80) {
    $progressColor = 'bg-green-500';      // Excellent
} elseif ($profile_percent >= 50) {
    $progressColor = 'bg-yellow-500';     // Medium
} else {
    $progressColor = 'bg-red-500';        // Low
}


// Count total resumes
$total_sql = "
    SELECT COUNT(*) as total 
    FROM job_applications ja
    JOIN company_jobs cj ON ja.job_id = cj.job_id
    WHERE cj.username = ?
";
$stmt = $conn->prepare($total_sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_resumes = $total_result['total'] ?? 0;

// Example static/new logic
$new_resumes = $total_resumes; // Or implement logic to fetch "new" resumes only




// Approved applications
$approved_query = $conn->prepare("SELECT COUNT(*) as approved FROM job_applications WHERE username = ? AND application_status = 'approved'");
$approved_query->bind_param("s", $_SESSION['username']);
$approved_query->execute();
$approved_result = $approved_query->get_result()->fetch_assoc();
$approved_count = $approved_result['approved'] ?? 0;

//envelop logic
$username = $_SESSION['username'] ?? '';
$emails = [];



?>








<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>NexusCareer - Resume Analysis Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        dark: {
                            800: '#1e1e2d',
                            900: '#12141d',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <link href="styles.css" rel="stylesheet" />
</head>

<body class="dark">
    <!-- Main Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-slate-900 border-r border-slate-800">
                <div class="flex items-center h-16 px-4">

                    <h1 class="text-2xl font-bold text-white"><a href="index.php">Nexus<span class="text-primary-600">Career</span></a></h1>

                </div>

                <!-- User Profile -->
                <a href="profile.php">
                    <div class="flex items-center px-4 py-3 space-x-3 border-b border-slate-800">
                        <img alt="User Logo" class="w-10 h-10 rounded-full"
                            <?php if ($role === 'user'): ?>
                            src="uploads/<?php echo $row['logo']; ?>" />
                    <?php elseif ($role === 'company'): ?>
                        src="uploads/<?php echo $raw['logo']; ?>" />
                    <?php endif; ?>



                    <div>
                        <?php if ($role === 'user'): ?>
                            <p class="font-medium text-white"><?php echo $row['fullname'] ?? null; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $row['email'] ?? null; ?></p>
                        <?php elseif ($role === 'company'): ?>
                            <p class="font-medium text-white"><?php echo $raw['company_name'] ?? null; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $raw['email'] ?? null; ?></p>

                        <?php endif; ?>
                    </div>
                    </div>
                </a>

                <?php if ($role === 'user'): ?>
                    <!-- Navigation -->
                    <!-- Sidebar -->
                    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col">
                        <!-- Logo / Branding -->

                        <!-- Profile Info -->


                        <!-- Navigation -->
                        <nav class="flex-1 px-3 py-4 space-y-1">
                            <a href="index.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-chart-pie mr-3"></i> Dashboard
                            </a>

                            <a href="profile.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-user mr-3"></i> Profile
                            </a>

                            <a href="myresume.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'myresume.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-file-alt mr-3"></i> My Resume
                            </a>

                            <a href="job_matches.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'job_matches.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-briefcase mr-3"></i> Job Matches
                            </a>

                            <a href="setting.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-cog mr-3"></i> Settings
                            </a>

                            <a href="logout.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-red-400 hover:bg-red-600 hover:text-white transition">
                                <i class="fas fa-sign-out-alt mr-3"></i> Logout
                            </a>
                        </nav>
                    </aside>


                    <!-- Upgrade Banner -->

            </div>
        </div>
        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-auto custom-scrollbar">
            <!-- Top Navigation -->

            <div class="flex items-center justify-between h-16 px-4 bg-slate-800 border-b border-slate-700">
                <!-- 🔍 Job Search Bar -->
                <form action="job_matches.php" method="get" class="p-4 bg-slate-900 shadow-md flex items-center gap-2" onsubmit="return handleSearchSubmit(event)">
                    <input
                        type="text"
                        id="searchInput"
                        name="search"
                        placeholder="Search jobs by title, company, or skills..."
                        class="w-full px-4 py-2 rounded-lg bg-slate-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        autocomplete="off" />
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Search</button>
                </form>



                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <!-- Theme Toggle Switch -->

                    <!-- envelop btn -->



                    <!-- BELL ICON AND DROPDOWN -->
                    <div class="relative inline-block text-left">
                        <button onclick="toggleDropdown()" class="relative p-2 text-slate-400 rounded-full hover:text-slate-300 hover:bg-slate-700 focus:outline-none">
                            <i class="fas fa-bell"></i>
                            <?php if ($noti_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1 rounded-full animate-ping">
                                    <?= $noti_count ?>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- DROPDOWN -->
                        <div id="dropdown" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
                            <div class="flex items-center gap-3 p-4 border-b bg-slate-50">
                                <img src="uploads/<?php echo $row['logo']; ?> ?? 'default-logo.png' ?>" alt="logo" class="w-10 h-10 rounded-full object-cover border border-slate-300">
                                <div>
                                    <div class="font-semibold text-gray-800"><?= htmlspecialchars($username) ?></div>

                                </div>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $noti): ?>
                                        <a href="" class="block px-4 py-3 text-sm hover:bg-gray-100 border-b">
                                            <div class="text-gray-800">
                                                <?= htmlspecialchars(strip_tags($noti['message'])) ?>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1"><?= date("M d, Y h:i A", strtotime($noti['created_at'])) ?></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-4 text-sm text-gray-500">No notifications</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                    <!-- right side logo -->
                    <a href="profile.php">
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img alt="User profile" class="w-8 h-8 rounded-full"
                                    <?php if ($role === 'user'): ?>
                                    src="uploads/<?php echo $row['logo']; ?>" />
                            <?php elseif ($role === 'company'): ?>
                                src="uploads/<?php echo $raw['logo']; ?>" />
                            <?php endif; ?>
                            </button>
                    </a>
                </div>
            </div>
        </div>


        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto p-6 bg-slate-900">
            <!-- Dashboard Header -->
            <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Dashboard</h2>
                    <p class="text-slate-400">Hello <?php echo $row['fullname'] ?? null; ?> , welcome back! Here's your career insight.</p>
                </div>


                <a href="create_resume.php">
                    <button
                        class="px-4 py-2 mt-4 text-sm font-medium text-white rounded-lg md:mt-0 gradient-bg hover:opacity-90">
                        <i class="fas fa-plus mr-2"></i>Create Resume
                    </button>
                </a>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Resume Score Card -->
                <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Resume Score</p>
                            <p class="mt-1 text-3xl font-semibold text-white"><?= $resume_score ?>/100</p>
                        </div>
                        <div class="relative resume-score">
                            <svg class="w-12 h-12">
                                <circle cx="24" cy="24" r="22" stroke="#334155" stroke-width="4" fill="none" />
                                <circle cx="24" cy="24" r="22" stroke="#7c3aed" stroke-width="4" fill="none"
                                    stroke-dasharray="<?= $resume_score * 1.38 ?> 999" transform="rotate(-90 24 24)" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center text-sm font-bold text-white">
                                <?= $resume_score ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Last updated: <?= date('d M Y', strtotime($resume['updated_at'] ?? 'now')) ?></span>
                            <a class="font-medium text-primary-600 hover:text-primary-500" href="myresume.php">Improve</a>
                        </div>
                    </div>
                </div>
                <!-- Job Matches Card -->
                <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Jobs Applied</p>
                            <p class="mt-1 text-3xl font-semibold text-white"><?= $jobs_applied_count ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-900/30 text-primary-600">
                            <i class="text-xl fas fa-briefcase"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>All Jobs: <?= $all_jobs_count ?></span>
                            <a class="font-medium text-primary-600 hover:text-primary-500" href="job_matches.php">View all</a>
                        </div>
                    </div>
                </div>

                <!-- Applications Card -->
                <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">Approved Resumes</p>
                            <p class="mt-1 text-3xl font-semibold text-white"><?= $approved_count ?></p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-900/30 text-green-500">
                            <i class="text-xl fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>All approved only</span>
                            <a class="font-medium text-purple-400 hover:text-purple-300" href="track_status.php">See All</a>
                        </div>
                    </div>
                </div>


                <!-- Profile Completeness Card -->
                <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="w-full">
                            <p class="text-sm font-medium text-slate-400">Profile Completeness</p>
                            <div class="w-full mt-2">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-slate-400">Completed: <?= $profile_percent ?>%</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-3">
                                    <div class="<?= $progressColor ?> h-3 rounded-full transition-all duration-500"
                                        style="width: <?= $profile_percent ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="w-12 h-12 ml-4 rounded-full bg-indigo-900/30 flex items-center justify-center text-primary-600">
                            <i class="text-xl fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Last update: this week</span>
                            <a class="font-medium text-primary-600 hover:text-primary-500" href="profile.php">Update</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resume Upload Section -->
            <div class="p-6 mb-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white flex flex-col items-center justify-center">Upload Resume</h3>
                <form id="resume-form" action="upload_file.php" method="POST" enctype="multipart/form-data">
                    <div class="file-upload relative flex flex-col items-center justify-center p-8 mb-4 text-center border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                        id="dropzone">
                        <div class="flex flex-col items-center justify-center">
                            <i class="mb-3 text-4xl text-slate-500 fas fa-cloud-upload-alt"></i>
                            <p class="mb-1 font-medium text-slate-300">Drag and drop your file here</p>
                            <p class="text-sm text-slate-500">or click to browse files</p>
                            <input accept=".pdf,.doc,.docx,.txt,.rtf" name="resume_file"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="resume-file"
                                type="file" />
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between">
                        <div class="text-sm text-slate-500">
                            Supported formats: PDF, DOC, DOCX (Max 5MB)
                        </div>
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90 disabled:opacity-50"
                            disabled id="upload-btn">
                            Upload Resume
                        </button>
                    </div>
                </form>

                <!-- Progress Section -->
                <div class="hidden mt-6" id="analysis-progress">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-slate-300">Uploading & Analyzing your resume...</div>
                        <div class="text-xs text-slate-500" id="progress-percentage">0%</div>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-primary-600 h-2 rounded-full transition-all" id="progress-bar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Success Section -->
                <div class="hidden mt-6 p-4 rounded-lg bg-slate-700/50" id="analysis-results">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-green-400">✅ Resume Uploaded Successfully!</h4>
                    </div>
                    <p class="text-slate-300 text-sm">Your resume has been uploaded and saved in the system.</p>
                </div>
            </div>

        </div>
    </div>
    </div>




<?php elseif ($role === 'company'): ?>

    <!-- Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col">
        <!-- Logo / Branding -->

        <!-- Profile Info -->


        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="index.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-chart-pie mr-3"></i> Dashboard
            </a>

            <a href="profile.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-user mr-3"></i> Profile
            </a>

            <a href="myjobs.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'myjobs.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-file-alt mr-3"></i> My Jobs
            </a>

            <a href="job_matches.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'job_matches.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-briefcase mr-3"></i> Job Matches
            </a>

            <a href="setting.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-cog mr-3"></i> Settings
            </a>

            <a href="logout.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-red-400 hover:bg-red-600 hover:text-white transition">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </aside>



    <!-- Upgrade Banner -->

    </div>
    </div>
    <!-- Main Content -->
    <div class="flex flex-col flex-1 overflow-auto custom-scrollbar">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between h-16 px-4 bg-slate-800 border-b border-slate-700">
            <div class="flex items-center">
                <button class="p-1 text-slate-400 rounded-md md:hidden hover:text-slate-300 hover:bg-slate-700">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="relative ml-4">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-slate-500"></i>
                    </div>
                    <input
                        class="w-full py-2 pl-10 pr-4 text-sm bg-slate-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-600 text-slate-200 placeholder-slate-400"
                        placeholder="Search jobs, profiles..." type="text" />
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <!-- Theme Toggle Switch -->
                <!-- BELL ICON AND DROPDOWN -->
                <div class="relative inline-block text-left">
                    <!-- Bell Icon -->
                    <button onclick="toggleDropdown()" class="relative p-2 text-slate-400 rounded-full hover:text-slate-300 hover:bg-slate-700 focus:outline-none transition">
                        <i class="fas fa-bell"></i>
                        <?php if ($noti_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] min-w-[18px] h-[18px] flex items-center justify-center rounded-full">
                                <?= $noti_count ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown -->
                    <div id="dropdown" class="hidden absolute right-0 mt-2 w-96 bg-white shadow-xl ring-1 ring-black ring-opacity-10 rounded-xl z-50 overflow-hidden">

                        <!-- Header with user info -->
                        <div class="flex items-center gap-3 p-4 bg-gray-50 border-b">
                            <img src="uploads/<?= !empty($raw['logo']) ? htmlspecialchars($raw['logo']) : 'default-logo.png' ?>"
                                alt="logo"
                                class="w-10 h-10 rounded-full object-cover border border-gray-300">
                            <div>
                                <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($username) ?></div>
                                <div class="text-xs text-gray-500"><?= ucfirst($role) ?> Panel</div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $noti): ?>
                                    <a href=""
                                        class="block px-4 py-3 hover:bg-gray-50 transition text-sm text-gray-800 notification-item"
                                        data-type="<?= htmlspecialchars($noti['type'] ?? 'general') ?>">

                                        <div class="font-medium text-gray-900 leading-5">
                                            <?= htmlspecialchars(strip_tags($noti['message'])) ?>
                                        </div>

                                        <div class="text-xs text-gray-400 mt-1">
                                            <?= date("M d, Y h:i A", strtotime($noti['created_at'])) ?>
                                        </div>

                                        <?php if (!empty($noti['type'])): ?>
                                            <div class="mt-1">
                                                <span class="inline-block text-[10px] uppercase font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                                    <?= strtoupper($noti['type']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-sm text-gray-500 text-center">No notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


                <!-- right side logo -->
                <a href="profile.php">
                    <div class="relative">
                        <button class="flex items-center space-x-2">
                            <img alt="User profile" class="w-8 h-8 rounded-full"
                                src="uploads/<?php echo $raw['logo']; ?>" />
                        </button>
                </a>
            </div>
        </div>
    </div>
    <!-- Main Content Area -->
    <div class="flex-1 overflow-auto p-6 bg-slate-900">
        <!-- Dashboard Header -->
        <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Dashboard</h2>
                <p class="text-slate-400">Hello <?php echo $raw['company_name'] ?? null; ?> , welcome back! Place Job career insight.</p>
            </div>


            <a href="create_resume.php">
                <button
                    class="px-4 py-2 mt-4 text-sm font-medium text-white rounded-lg md:mt-0 gradient-bg hover:opacity-90">
                    <i class="fas fa-plus mr-2"></i>Upload Jobs
                </button>
            </a>
        </div>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- job Score Card -->
            <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400">Job Posts</p>
                        <p class="mt-1 text-3xl font-semibold text-white"><?= $total_jobs ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-900/30 flex items-center justify-center text-primary-600">
                        <i class="text-xl fas fa-briefcase"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Last updated: <?= $last_posted_display ?></span>
                        <a class="font-medium text-primary-600 hover:text-primary-500" href="myjobs.php">Manage</a>
                    </div>
                </div>
            </div>
            <!-- Job Matches Card -->
            <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400">Applications Received</p>
                        <p class="mt-1 text-3xl font-semibold text-white"><?= $applications_count ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-900/30 flex items-center justify-center text-primary-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Updated: <?= date("F j, Y") ?></span>
                        <a class="font-medium text-primary-600 hover:text-primary-500" href="view_applicants.php">View All</a>
                    </div>
                </div>
            </div>

            <!-- Applications Card -->
            <div class=" p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400">Applicants Resume</p>
                        <p class="mt-1 text-3xl font-semibold text-white"><?= $total_resumes ?></p> <!-- dynamically set -->
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-900/30 text-green-500">
                        <i class="text-xl fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>New resumes: <?= $new_resumes ?></span> <!-- dynamic placeholder -->
                        <a class="font-medium text-purple-400 hover:text-purple-300" href="applicants_resume.php">See All</a>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Card -->

            <!-- You can include list preview here if needed -->

            <!-- See All Button at Bottom -->




        </div>
    </div>


    <!-- Resume Upload Section -->

    <!-- Analysis Progress (hidden by default) -->
    <div class="hidden mt-6" id="analysis-progress">
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-medium text-slate-300">Analyzing your resume...</div>
            <div class="text-xs text-slate-500" id="progress-percentage">30%</div>
        </div>
        <div class="w-full bg-slate-700 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full animate-pulse" id="progress-bar" style="width: 30%">
            </div>
        </div>
    </div>
    <!-- Analysis Results (hidden by default) -->
    <div class="hidden mt-6 p-4 rounded-lg bg-slate-700/50" id="analysis-results">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-medium text-white">Resume Analysis Results</h4>
            <div class="flex items-center">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                    <i class="mr-1 fas fa-check-circle"></i> 85/100
                </span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Strengths -->
            <div class="p-3 rounded-lg bg-slate-800">
                <div class="flex items-center mb-2">
                    <div
                        class="flex items-center justify-center w-8 h-8 mr-3 rounded-full bg-green-900/30 text-green-500">
                        <i class="fas fa-check">&lt;/i</i>
                    </div>
                </div>
            </div>


        

        <?php endif; ?>




        <script>
            document.getElementById('uploadResume').addEventListener('click', function() {
                fetch('check_login.php')
                    .then(res => res.json())
                    .then(data => {
                        if (!data.loggedIn) {
                            window.location.href = 'login.html';
                        } else {
                            // Redirect to a PHP script that checks if resume exists
                            window.location.href = `resume_router.php`;
                        }
                    });
            });
        </script>
        <script>
            const fileInput = document.getElementById("resume-file");
            const uploadBtn = document.getElementById("upload-btn");
            const progressBar = document.getElementById("progress-bar");
            const progressPercentage = document.getElementById("progress-percentage");
            const progressSection = document.getElementById("analysis-progress");
            const resultsSection = document.getElementById("analysis-results");
            const form = document.getElementById("resume-form");

            let fileSelected = false;

            // Enable button after selecting file
            fileInput.addEventListener("change", () => {
                if (fileInput.files.length > 0) {
                    uploadBtn.disabled = false;
                    fileSelected = true;
                } else {
                    uploadBtn.disabled = true;
                    fileSelected = false;
                }
            });

            // On click upload → show progress
            uploadBtn.addEventListener("click", () => {
                if (!fileSelected) return;

                progressSection.classList.remove("hidden");
                uploadBtn.disabled = true;

                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = progress + "%";
                    progressPercentage.textContent = progress + "%";

                    if (progress >= 100) {
                        clearInterval(interval);

                        // Simulate small delay before final step
                        setTimeout(() => {
                            progressSection.classList.add("hidden");
                            resultsSection.classList.remove("hidden");

                            // Submit form after fake progress
                            form.submit();
                        }, 500);
                    }
                }, 200);
            });
        </script>

        <script>
            function toggleDropdown() {
                const dropdown = document.getElementById("dropdown");
                dropdown.classList.toggle("hidden");

                // Mark as read only once when opened
                if (!dropdown.classList.contains("marked")) {
                    fetch("<?= ($role === 'company') ? 'mark_company_notifications_read.php' : 'mark_notifications_read.php' ?>", {
                        method: "POST"
                    }).then(() => {
                        dropdown.classList.add("marked");
                    });
                }
            }
        </script>

        <script>
            function toggleDropdown() {
                const dropdown = document.getElementById('dropdown');
                dropdown.classList.toggle('hidden');
            }

            // Close when clicking outside
            window.addEventListener('click', function(e) {
                const dropdown = document.getElementById('dropdown');
                const button = document.querySelector('button[onclick="toggleDropdown()"]');
                if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        </script>

        <script>
            function toggleNotificationDropdown() {
                const dropdown = document.getElementById("notificationDropdown");
                dropdown.classList.toggle("hidden");
            }

            // Optional: Close dropdown if clicked outside
            document.addEventListener('click', function(e) {
                const bell = document.getElementById('notificationBell');
                const dropdown = document.getElementById('notificationDropdown');

                if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        </script>


        <script>
            document.getElementById('notifBtn').addEventListener('click', function() {
                document.getElementById('notifDropdown').classList.toggle('hidden');
            });

            document.getElementById('markAllRead').addEventListener('click', function() {
                fetch('mark_notifications_read.php')
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('notifDropdown').classList.add('hidden');
                        location.reload(); // Refresh to update badge
                    });
            });
        </script>
        <script>
            document.getElementById('notifBtnCompany').addEventListener('click', function() {
                const dropdown = document.getElementById('notifDropdownCompany');
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                const btn = document.getElementById('notifBtnCompany');
                const dropdown = document.getElementById('notifDropdownCompany');
                if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        </script>



        <script src="script.js"></script>

        <script>
            function handleSearchSubmit(e) {
                const input = document.getElementById('searchInput');
                const q = input ? input.value.trim() : '';
                if (!q) {
                    alert('Please type something to search');
                    e.preventDefault();
                    return false;
                }
                return true;
            }
            (function() {
                const inp = document.getElementById('searchInput');
                if (!inp) return;
                inp.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        inp.form && inp.form.requestSubmit();
                    }
                });
            })();
        </script>

</body>

</html>