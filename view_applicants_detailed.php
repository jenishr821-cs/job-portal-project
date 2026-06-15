<?php
session_start();
include 'db.php';



if (!function_exists('is_verified')) {
    function is_verified($row) {
        if (!is_array($row)) return false;
        $keys = ['is_verified','verified','verified_status','verification_status','trusted','trust_status'];
        foreach ($keys as $k) {
            if (isset($row[$k])) {
                $v = strtolower(trim((string)$row[$k]));
                if ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'verified' || $v === 'approved' || $v === 'trusted') {
                    return true;
                }
            }
        }
        return false;
    }
}


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'company') {
    echo "<script>alert('Access Denied'); window.location.href='login.php';</script>";
    exit;
}
include 'notification_helper.php';

$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$notifications = [];
$noti_count = 0;

// Fetch notifications only if user is logged in
if ($username !== '') {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE receiver_username  = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Count unread
    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE receiver_username  = ? AND is_read = 0");
    $count_stmt->bind_param("s", $username);
    $count_stmt->execute();
    $noti_count = $count_stmt->get_result()->fetch_assoc()['unread'] ?? 0;

    // Fetch logo (user or company)
    if ($role === 'company') {
        $logo_sql = $conn->prepare("SELECT logo FROM company_profiles WHERE username = ?");
    } else {
        $logo_sql = $conn->prepare("SELECT logo FROM user_profiles WHERE username = ?");
    }
    $logo_sql->bind_param("s", $username);
    $logo_sql->execute();
    $logo_res = $logo_sql->get_result()->fetch_assoc();
    $raw['logo'] = $logo_res['logo'] ?? '';
}

$company_username = $_SESSION['username'];
$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
    echo "<script>alert('Invalid Job ID'); window.location.href='view_applicants.php';</script>";
    exit;
}

// Optional: Check if this job belongs to the logged-in company
$check_job = $conn->prepare("SELECT job_title FROM company_jobs WHERE job_id = ? AND username = ?");
$check_job->bind_param("is", $job_id, $company_username);
$check_job->execute();
$job_result = $check_job->get_result();

if ($job_result->num_rows !== 1) {
    echo "<script>alert('Job not found or unauthorized'); window.location.href='view_applicants.php';</script>";
    exit;
}

$job = $job_result->fetch_assoc();
$job_title = $job['job_title'];

// Fetch applicants
$app_stmt = $conn->prepare("
    SELECT up.username, up.fullname, up.email, up.logo, up.skills
    FROM job_applications ja
    JOIN user_profiles up ON ja.username = up.username
    WHERE ja.job_id = ?
");
$app_stmt->bind_param("i", $job_id);
$app_stmt->execute();
$applicants = $app_stmt->get_result();
$verify_stmt = $conn->prepare("SELECT is_verified FROM user_profiles WHERE username = ?");

$username = $_SESSION['username'];
$role = $_SESSION['role'];


$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);
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
    <style>
        body {
            background-color: #12141d;
        }
    </style>
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

              
    <!-- Navigation -->
    <nav class="flex-1 px-2 py-4 space-y-1">
        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link active"
            href="index.php">
            <i class="fas fa-chart-pie mr-3 text-slate-400"></i>
            Dashboard
        </a>

        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
            href="profile.php">
            <i class="fas fa-user mr-3 text-slate-400"></i>
            Profile
        </a>
        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link" href="myjobs.php">
            <i class="fas fa-file-alt mr-3 text-slate-400"></i>
            My Jobs
        </a>


        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
            href="job matches.php">
            <i class="fas fa-briefcase mr-3 text-slate-400"></i>
            Job Matches
        </a>



        <?php if (isset($_SESSION['username'])): ?>
            <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                href="logout.php">
                <i class="fas fa-sign-out-alt mr-3 text-slate-400"></i>
                Logout
            </a>
        <?php else: ?>
            <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                href="login.php">
                <i class="fas fa-users mr-3 text-slate-400"></i>
                Login
            </a>
        <?php endif; ?>



        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
            href="setting.php">
            <i class="fas fa-cog mr-3 text-slate-400"></i>
            Settings
        </a>
    </nav>


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
                        <div class="mt-6">
                            <a href="view_applicants.php"> <button
                                    class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">← Back to Applications Overview</button></a>
                        </div>
                        <div class="p-6">


                            <h1 class="text-2xl font-bold mb-4 ">Applicants for <span class="text-purple-400"><?= htmlspecialchars($job_title) ?></span></h1>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ">
                                <?php while ($row = $applicants->fetch_assoc()): ?>
<?php 
    $applicant_verified = false;
    if (isset($row['username']) && !empty($row['username'])) {
        $verify_stmt->bind_param("s", $row['username']);
        $verify_stmt->execute();
        $ver_res = $verify_stmt->get_result()->fetch_assoc();
        $applicant_verified = is_verified($ver_res);
    }
?>


                                    <div class="p-4 bg-slate-800 rounded-lg shadow job-card card-hover transition-all duration-300 ">
                                        <div class="flex items-center space-x-4">
                                            <a href="view_applicants_profile.php?username=<?= urlencode($row['username']) ?>">
                                                
<div class="relative inline-block">
    <img src="uploads/<?= htmlspecialchars($row['logo']) ?>" class="w-12 h-12 rounded-full border border-slate-600" alt="User Logo">
    <?php if (!empty($applicant_verified) && $applicant_verified): ?>
        <span class="absolute -right-1 -bottom-1 w-5 h-5 rounded-full bg-white flex items-center justify-center border border-slate-300">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" fill="#1DA1F2"></circle>
                <path d="M16.5 9l-5.2 6-3.1-2.8" stroke="white" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    <?php endif; ?>
</div>

                                            </a>
                                            <div>
                                                <p class="font-semibold"><?= htmlspecialchars($row['fullname']) ?></p>
                                                <p class="text-xs text-slate-400"><?= htmlspecialchars($row['email']) ?></p>
                                                <p class="text-xs mt-1 text-slate-300"><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        


                        </div>
                        <script>
                            function toggleDropdown() {
                                const dropdown = document.getElementById('dropdown');
                                dropdown.classList.toggle('hidden');
                            }

                            window.addEventListener('click', function(e) {
                                const dropdown = document.getElementById('dropdown');
                                const button = document.querySelector('button[onclick="toggleDropdown()"]');
                                if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                                    dropdown.classList.add('hidden');
                                }
                            });
                        </script>

</body>

</html>