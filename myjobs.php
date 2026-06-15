<?php
session_start();
include 'db.php';
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

//close the job card from company side
if (isset($_POST['close_job']) && isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);
    $company_id = $_SESSION['username']; // company’s session id

    // Extra check: only allow closing own jobs
    $stmt = $conn->prepare("UPDATE company_jobs SET status = 'closed' WHERE job_id = ? AND company_id = ?");
    $stmt->bind_param("ii", $job_id, $company_id);
    $stmt->execute();
}

// Fetch jobs for logged-in company
$company_id = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM company_jobs WHERE username = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();




header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['username'])  || !isset($_SESSION['role'])) {
    echo "<script>alert('login first'); window.location.href='login.php';</script>";
}

$username = $_SESSION['username'];
$result = mysqli_query($conn, "SELECT logo FROM user_profiles WHERE username = '$username'");
$row = mysqli_fetch_assoc($result);
$logoPath = $row['logo'] ?? 'default-logo.png';


$username = $_SESSION['username'];
$role = $_SESSION['role'];

$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);


// Fetch jobs posted by the logged-in company
$sql = "SELECT * FROM company_jobs WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$jobs_per_page = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $jobs_per_page;

// === Total Jobs Count for Pagination ===
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM company_jobs WHERE username = ?");
$stmt_count->bind_param("s", $username);
$stmt_count->execute();
$total_jobs = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_jobs / $jobs_per_page);

// === Fetch Paginated Jobs ===
$stmt = $conn->prepare("SELECT * FROM company_jobs WHERE username = ? LIMIT ?, ?");
$stmt->bind_param("sii", $username, $offset, $jobs_per_page);
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

  <style>
  #editModal::-webkit-scrollbar {
    width: 8px;
  }
  #editModal::-webkit-scrollbar-thumb {
    background-color: #6b7280;
    border-radius: 8px;
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
                <a href="profile.html">
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
                    <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                        href="index.php">
                        <i class="fas fa-chart-pie mr-3 text-slate-400"></i>
                        Dashboard
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                        href="profile.php">
                        <i class="fas fa-user mr-3 text-slate-400"></i>
                        Profile
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link active" href="myjobs.php">
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
                    <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link "
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
                <div class="p-6 rounded-lg shadow bg-slate-800">

                   
                </div>
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
                                <img alt="User profile" class="w-8 h-8 rounded-full" src="uploads/<?php echo $raw['logo']; ?>" />
                            </button>
                    </a>
                </div>
            </div>
        </div>
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto p-6 bg-slate-900">
            <!-- Dashboard Header -->
            <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
               <!-- ✅ Job Cards Grid Container -->
<div id="jobs-grid" class="grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-3 p-4">

<?php while ($row = $result->fetch_assoc()): ?>
  <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">

    <div class="flex justify-between items-start mb-4">
      <div>
        <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($row['job_title']) ?></h2>
        <p class="text-sm text-slate-400"><?= htmlspecialchars($row['company_name']) ?> • <?= htmlspecialchars($row['job_location']) ?></p>
      </div>
      <img src="uploads/<?= htmlspecialchars($row['company_logo']) ?>" alt="Company Logo"
           class="w-12 h-12 object-cover rounded-full border border-slate-600">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 text-sm">
      <p><strong>Job Type:</strong> <?= htmlspecialchars($row['job_type']) ?></p>
      <p><strong>Experience:</strong> <?= htmlspecialchars($row['experience_required']) ?></p>
      <p><strong>Qualifications:</strong> <?= htmlspecialchars($row['qualifications']) ?></p>
      <p><strong>Salary:</strong> <?= htmlspecialchars($row['salary']) ?></p>
      <p><strong>Openings:</strong> <?= htmlspecialchars($row['number_of_openings']) ?></p>
      <p><strong>Work Mode:</strong> <?= htmlspecialchars($row['work_mode']) ?></p>
      <p class="md:col-span-2"><strong>Responsibilities:</strong> <?= htmlspecialchars($row['responsibilities']) ?></p>
      <p class="md:col-span-2"><strong>Benefits:</strong> <?= htmlspecialchars($row['benefits']) ?></p>
      <p class="md:col-span-2"><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['job_description'])) ?></p>
    </div>

    <hr class="my-4 border-slate-700">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 text-sm">
      <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_no']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
      <p><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
    </div>

      <div class="mt-4 flex space-x-3">
      <!-- Edit Button -->
      <button
        class="edit-btn px-4 py-2 bg-yellow-500 text-white rounded"
        data-job-id="<?= $row['job_id'] ?>"
        data-job-title="<?= htmlspecialchars($row['job_title'], ENT_QUOTES) ?>"
        data-job-type="<?= htmlspecialchars($row['job_type'], ENT_QUOTES) ?>"
        data-experience="<?= htmlspecialchars($row['experience_required'], ENT_QUOTES) ?>"
        data-qualifications="<?= htmlspecialchars($row['qualifications'], ENT_QUOTES) ?>"
        data-salary="<?= htmlspecialchars($row['salary'], ENT_QUOTES) ?>"
        data-openings="<?= $row['number_of_openings'] ?>"
        data-work-mode="<?= htmlspecialchars($row['work_mode'], ENT_QUOTES) ?>"
        data-responsibilities="<?= htmlspecialchars($row['responsibilities'], ENT_QUOTES) ?>"
        data-benefits="<?= htmlspecialchars($row['benefits'], ENT_QUOTES) ?>"
        data-description="<?= htmlspecialchars($row['job_description'], ENT_QUOTES) ?>"
      >
        Edit
      </button>

      <!-- Delete Button -->
      <form method="POST" action="job_actions.php">
        <input type="hidden" name="delete_job_id" value="<?= $row['job_id'] ?>">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded" onclick="return confirm('Are you sure you want to delete this job?')">Delete</button>
      </form>
    </div>
  </div>
<?php endwhile; ?>

<!-- Edit Modal -->
<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-60">
  <div class="bg-slate-800 p-6 rounded-xl w-full max-w-4xl shadow-2xl text-white relative max-h-[90vh] overflow-y-auto custom-scrollbar">
    <!-- Close Button -->
    <button onclick="closeModal()" class="absolute top-3 right-4 text-white text-2xl font-bold hover:text-red-400">&times;</button>

    <h2 class="text-3xl font-bold text-center mb-6 border-b border-slate-700 pb-2">Edit Job</h2>

    <form method="POST" action="job_actions.php" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <input type="hidden" name="update_job_id" id="update_job_id" />

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Job Title</label>
        <input type="text" name="job_title" id="job_title" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Job Type</label>
        <input type="text" name="job_type" id="job_type" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Experience Required</label>
        <input type="text" name="experience_required" id="experience_required" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Qualifications</label>
        <input type="text" name="qualifications" id="qualifications" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Salary</label>
        <input type="text" name="salary" id="salary" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Number of Openings</label>
        <input type="number" name="number_of_openings" id="number_of_openings" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Work Mode</label>
        <input type="text" name="work_mode" id="work_mode" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1">Responsibilities</label>
        <textarea name="responsibilities" id="responsibilities" rows="2" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1">Benefits</label>
        <textarea name="benefits" id="benefits" rows="2" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1">Job Description</label>
        <textarea name="job_description" id="job_description" rows="3" class="w-full rounded-lg px-4 py-2 bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="md:col-span-2 flex justify-end mt-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg font-semibold transition duration-200">
          Update Job
        </button>
      </div>
    </form>
  </div>
</div>


<!-- JavaScript -->
<script>
  const modal = document.getElementById('editModal');
  const editButtons = document.querySelectorAll('.edit-btn');

  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('update_job_id').value = btn.dataset.jobId;
      document.getElementById('job_title').value = btn.dataset.jobTitle;
      document.getElementById('job_type').value = btn.dataset.jobType;
      document.getElementById('experience_required').value = btn.dataset.experience;
      document.getElementById('qualifications').value = btn.dataset.qualifications;
      document.getElementById('salary').value = btn.dataset.salary;
      document.getElementById('number_of_openings').value = btn.dataset.openings;
      document.getElementById('work_mode').value = btn.dataset.workMode;
      document.getElementById('responsibilities').value = btn.dataset.responsibilities;
      document.getElementById('benefits').value = btn.dataset.benefits;
      document.getElementById('job_description').value = btn.dataset.description;

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    });
  });

  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
</script>


</div>
  </div>
<!-- ✅ Pagination Links -->
<div class="flex justify-center space-x-2 pb-6">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="px-3 py-2 bg-gray-700 text-white rounded">Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?page=<?= $i ?>" class="px-3 py-2 <?= $i == $page ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-300' ?> rounded">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page + 1 ?>" class="px-3 py-2 bg-gray-700 text-white rounded">Next</a>
  <?php endif; ?>
</div>



                   
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <?php endif; ?>
    <!-- ✅ Your modal code here -->


<!-- Then your scripts and closing tags -->
<script src="script.js"></script>
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
 

    <script src="script.js"></script>
</body>

</html>