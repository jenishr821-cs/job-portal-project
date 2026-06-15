<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
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
        $logo_sql = $conn->prepare("SELECT logo FROM company WHERE username = ?");
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
$query = "SELECT fullname, email, logo FROM user_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$username = $_SESSION['username'];
$query = $conn->prepare("
    SELECT ja.*, cj.job_title, cj.company_name, cp.logo AS company_logo
    FROM job_applications ja
    JOIN company_jobs cj ON ja.job_id = cj.job_id
    JOIN company_profiles cp ON cj.username = cp.username
    WHERE ja.username = ?
");

$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
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
        input[type="number"] {
            appearance: textfield;
            -moz-appearance: textfield;
            /* Firefox */
            -webkit-appearance: none;
            /* Chrome/Safari */
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            display: none;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <link href="styles.css" rel="stylesheet" />
</head>

<body class="dark">


    <body class=" overflow-auto h-screen">
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
                            <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                                href="myresume.php">
                                <i class="fas fa-file-alt mr-3 text-slate-400"></i>
                                My Resumes
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
                                <img alt="User profile" class="w-8 h-8 rounded-full" src="uploads/<?php echo $row['logo']; ?>" />
                            </button>
                    </a>
                </div>
            </div>
        </div>
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto p-6 bg-slate-900">
            <!-- Dashboard Header -->
            <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
         <div class="flex-1 overflow-auto p-6 bg-slate-900">
            <div class="mt-6">
                    <a href="index.php">
                        <button
                            class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                            ← Back 

                        </button>
                    </a>
                    <h2 class="text-2xl font-bold text-white mb-4">Your Job Applications</h2>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-2 p-4">
<?php if ($result->num_rows > 0): ?>
    <?php while ($application = $result->fetch_assoc()): ?>
        <div class="p-6 rounded-lg shadow bg-slate-800 job-card transition-all duration-300">
            <div class="flex items-center gap-4">
                <!-- Company Logo -->
                <div class="shrink-0">
                    <img src="uploads/<?= htmlspecialchars($application['company_logo']) ?>"
                         alt="Company Logo"
                         class="w-16 h-16 object-cover rounded-full border border-slate-600">
                </div>

                <!-- Job Info + Status -->
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-white mb-1">
                        <?= htmlspecialchars($application['job_title']) ?>
                    </h2>
                    <p class="text-sm text-slate-400 mb-2">
                        <?= htmlspecialchars($application['company_name']) ?>
                    </p>
                    <?php
                        $status = $application['application_status'];
                        if ($status === 'approved') {
                            echo '<span class="px-3 py-1 text-sm rounded-full bg-green-600 text-white">Approved</span>';
                        } elseif ($status === 'rejected') {
                            echo '<span class="px-3 py-1 text-sm rounded-full bg-red-600 text-white">Rejected</span>';
                        } else {
                            echo '<span class="px-3 py-1 text-sm rounded-full bg-yellow-500 text-white">Pending</span>';
                        }
                    ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-slate-400">You have not applied to any jobs yet.</p>
<?php endif; ?>
</div>

  
<?php endif; ?>
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdown');
        dropdown.classList.toggle('hidden');
    }

    window.addEventListener('click', function (e) {
        const dropdown = document.getElementById('dropdown');
        const button = document.querySelector('button[onclick="toggleDropdown()"]');
        if (!dropdown.contains(e.target) && !button.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>

</body>
</html>
