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
$username = $_SESSION['username'];
$role = $_SESSION['role'];


$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);


$company_username = $_SESSION['username'];

$sql = "
    SELECT 
        up.*, cr.*, cj.job_title, cj.number_of_openings, ja.job_id, ja.id AS application_id, ja.application_status,
        (
            SELECT COUNT(*) 
            FROM job_applications 
            WHERE job_id = ja.job_id AND application_status = 'approved'
        ) AS approved_count
    FROM job_applications ja
    JOIN company_jobs cj ON ja.job_id = cj.job_id
    JOIN user_profiles up ON ja.username = up.username
    JOIN create_resume cr ON ja.username = cr.username
    WHERE cj.username = ?
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();


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

                <?php if ($role === 'company'): ?>

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
            <div class="mt-6">
                <a href="index.php">
                    <button
                        class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                        ← Back

                    </button>
                </a>



                <?php
                    include 'db.php';

                    // ✅ Correct query based on resume_portal.sql
                   
    $query = "
    SELECT 
    u.username,
    up.fullname, up.email, up.logo, up.skills, up.is_verified,
    cr.role, cr.experience, cr.degree, cr.university, cr.passing_year, cr.percentage,
    cr.programming_languages, cr.tools_frameworks, cr.summary, cr.phone, cr.address,
    r.file_name, r.file_path,
    ja.id AS application_id, ja.application_status,
    cj.job_id, cj.job_title, cj.number_of_openings,
    (
        SELECT COUNT(*) 
        FROM job_applications ja2 
        WHERE ja2.job_id = cj.job_id AND ja2.application_status = 'approved'
    ) AS approved_count
FROM users u
LEFT JOIN user_profiles up ON u.username = up.username
LEFT JOIN create_resume cr ON u.username = cr.username
LEFT JOIN resumes r ON u.username = r.username
LEFT JOIN job_applications ja ON u.username = ja.username
LEFT JOIN company_jobs cj ON ja.job_id = cj.job_id

";



                    $result = $conn->query($query);
                ?>


                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-6">Applicants Resumes</h1>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-2">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300 border border-transparent hover:border-purple-500">

                                <?php $rowVerified = is_verified($row); ?>
<?php $rowVerified = is_verified($row); ?>
<div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($row['job_title']) ?></h2>
                                        <p class="text-sm text-slate-400"><?= htmlspecialchars($row['fullname']) ?> • <?= htmlspecialchars($row['email']) ?></p>
                                    </div>

                                    
<div class="relative inline-block">
    <img src="uploads/<?= htmlspecialchars($row['logo']) ?>" alt="User Logo"
                                        class="w-12 h-12 object-cover rounded-full border border-slate-600">
    <?php if (!empty($rowVerified)): ?>
        <span class="absolute -right-1 -bottom-1 w-5 h-5 rounded-full bg-white flex items-center justify-center border border-slate-300">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" fill="#1DA1F2"></circle>
                <path d="M16.5 9l-5.2 6-3.1-2.8" stroke="white" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    <?php endif; ?>
</div>

                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 text-sm">
                                    <p><strong>Role:</strong> <?= htmlspecialchars($row['role']) ?></p>
                                    <p><strong>Experience:</strong> <?= htmlspecialchars($row['experience']) ?> Years</p>
                                    <p><strong>Degree:</strong> <?= htmlspecialchars($row['degree']) ?></p>
                                    <p><strong>University:</strong> <?= htmlspecialchars($row['university']) ?></p>
                                    <p><strong>Passing Year:</strong> <?= htmlspecialchars($row['passing_year']) ?></p>
                                    <p><strong>Percentage:</strong> <?= htmlspecialchars($row['percentage']) ?>%</p>
                                    <p class="md:col-span-2"><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></p>
                                    <p class="md:col-span-2"><strong>Programming Languages:</strong> <?= htmlspecialchars($row['programming_languages']) ?></p>
                                    <p class="md:col-span-2"><strong>Frameworks:</strong> <?= htmlspecialchars($row['tools_frameworks']) ?></p>
                                    <p class="md:col-span-2"><strong>Career Summary:</strong> <?= nl2br(htmlspecialchars($row['summary'])) ?></p>
                                </div>

                                <hr class="my-4 border-slate-700">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 text-sm">
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
                                    <p><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
                                    <p class="md:col-span-2"><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
                                </div>

                                <!-- Resume Section -->
                                <div class="mt-4">

                                    <?php if (!empty($row['file_path'])): ?>
                                        <p class="mt-3 text-sm font-semibold text-slate-200">📎 Uploaded Resume:</p>
                                        <a href="<?= htmlspecialchars($row['file_path']) ?>"
                                            download="<?= htmlspecialchars($row['file_name']) ?>"
                                            class="inline-flex items-center mt-2 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg shadow hover:bg-indigo-700 transition">
                                            <i class="fas fa-download mr-2"></i> Download <?= htmlspecialchars($row['file_name']) ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (empty($row['summary']) && empty($row['file_path'])): ?>
                                        <p class="text-gray-400 text-sm">No resume submitted.</p>
                                    <?php endif; ?>
                                </div>


                                <?php
                                $approved_count = $row['approved_count'];
                                $number_of_openings = $row['number_of_openings'];
                                $application_status = $row['application_status'];
                                ?>

                                <?php if ($application_status === 'approved'): ?>
                                    <button class="px-4 py-2 rounded-md bg-green-600 text-white cursor-not-allowed" disabled>
                                        <i class="fas fa-check mr-2"></i>Approved
                                    </button>
                                <?php elseif ($application_status === 'rejected'): ?>
                                    <button class="px-4 py-2 rounded-md bg-red-600 text-white cursor-not-allowed" disabled>
                                        <i class="fas fa-times mr-2"></i>Rejected
                                    </button>
                                <?php elseif ($approved_count < $number_of_openings): ?>
                                    <!-- APPROVE / DECLINE buttons only if limit not reached -->
                                    <form method="POST" action="approve_decline.php" class="flex gap-4">
                                        <input type="hidden" name="job_id" value="<?= $row['job_id'] ?>">
                                       <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">

                                        <button name="action" value="approved" type="submit"
                                            class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700"
                                            onclick="alert('Successfully approved!')">
                                            <i class="fas fa-check mr-2"></i>Approve
                                        </button>

                                        <button name="action" value="rejected" type="submit"
                                            class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700"
                                            onclick="alert('Successfully declined!')">
                                            <i class="fas fa-times mr-2"></i>Decline
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Show "Limit Reached" only if NOT already approved/rejected AND limit hit -->
                                    <button class="px-4 py-2 rounded-md bg-gray-500 text-white cursor-not-allowed" disabled>
                                        <i class="fas fa-ban mr-2"></i>Limit Reached
                                    </button>
                                <?php endif; ?>




                                <div class="mt-4 text-xs text-slate-500 text-right">
                                    Last updated: <?= date("F d, Y") ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>



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