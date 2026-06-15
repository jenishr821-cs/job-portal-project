<?php
session_start();
include 'db.php';
error_reporting(0);
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// after include 'db.php' and session_start()


$username = $_SESSION['username'] ?? '';
$search = trim($_GET['search'] ?? '');

// ✅ Save search term if user typed something
if ($search !== '' && $username !== '') {
    $stmt = $conn->prepare("INSERT INTO search_history (username, search_term) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $search);
    $stmt->execute();
    $stmt->close();
}


// === Search & Pagination CONTROLLER (drop-in) ===
$role = $_SESSION['role'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Save search term to history
if ($search !== '' && $username !== '') {
    $stmt = $conn->prepare("INSERT INTO search_history (username, search_term) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $search);
    $stmt->execute();
    $stmt->close();
}

// Build WHERE conditions
$conditions = [];
$conditions[] = "1"; // always true so WHERE is never empty

$params = [];
$types = "";

// Exclude jobs the user applied to 7+ days ago
if ($username !== '') {
    $conditions[] = "NOT EXISTS (
        SELECT 1 FROM job_applications a
        WHERE a.job_id = j.job_id
          AND a.username = ?
          AND a.applied_at <= DATE_SUB(NOW(), INTERVAL 7 DAY)
    )";
    $types .= "s";
    $params[] = $username;
}

// Case-insensitive search across multiple fields
$conditions[] = "("
    . "LOWER(j.job_title) LIKE ? OR "
    . "LOWER(j.company_name) LIKE ? OR "
    . "LOWER(j.job_location) LIKE ? OR "
    . "LOWER(j.qualifications) LIKE ? OR "
    . "LOWER(j.responsibilities) LIKE ? OR "
    . "LOWER(j.salary) LIKE ? OR "
    . "LOWER(j.job_type) LIKE ? OR "
    . "LOWER(j.experience_required) LIKE ?"
    . ")";
$like = '%' . strtolower($search) . '%';
$types .= "ssssssss";
array_push($params, $like, $like, $like, $like, $like, $like, $like, $like);



$where = implode(" AND ", $conditions);

// Count total results
$count_sql = "SELECT COUNT(*) AS total FROM company_jobs j WHERE $where";
$count_stmt = $conn->prepare($count_sql);
if ($types !== "") {
    $bind = [];
    $bind[] = &$types;
    foreach ($params as $k => $p) {
        $bind[] = &$params[$k];
    }
    call_user_func_array([$count_stmt, "bind_param"], $bind);
}
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total = (int)($count_res->fetch_assoc()['total'] ?? 0);
$count_stmt->close();

// Fetch paginated jobs
$list_sql = "SELECT j.* FROM company_jobs j WHERE $where ORDER BY j.created_at DESC LIMIT ? OFFSET ?";
$list_stmt = $conn->prepare($list_sql);

$types2 = $types . "ii";
$params2 = $params;
$params2[] = $perPage;
$params2[] = $offset;

$bind2 = [];
$bind2[] = &$types2;
foreach ($params2 as $k => $p) {
    $bind2[] = &$params2[$k];
}
call_user_func_array([$list_stmt, "bind_param"], $bind2);
$list_stmt->execute();
$result = $list_stmt->get_result();

// Alert messages for availability
if ($search !== '') {
    if ($total > 0) {
        echo "<script>alert('Match found');</script>";
    } else {
        echo "<script>alert('Search is not available');</script>";
    }
}
// === END Search & Pagination CONTROLLER ===
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
$result = mysqli_query($conn, "SELECT logo FROM user_profiles WHERE username = '$username'");
$row = mysqli_fetch_assoc($result);
$logoPath = $row['logo'] ?? 'default-logo.png';

$username = $_SESSION['username'];
$query = "SELECT fullname, phone, address, email, birthdate, aboutme, skills, language, logo, username FROM user_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);



$username = $_SESSION['username'];
$role = $_SESSION['role'];

$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);


// Fetch jobs posted by the logged-in company
$sql = "SELECT * FROM company_jobs LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $jobs_per_page);
$stmt->execute();
$result = $stmt->get_result();;

$jobs_per_page = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $jobs_per_page;

// === Total Jobs Count for Pagination ===
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM company_jobs");
$stmt_count->execute();
$total_jobs = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_jobs / $jobs_per_page);

// === Fetch Paginated Jobs ===
$stmt = $conn->prepare("SELECT * FROM company_jobs LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $jobs_per_page);
$stmt->execute();
$result = $stmt->get_result();

$already_applied = false;

if (isset($_SESSION['username']) && $_SESSION['role'] === 'user') {
    $check_applied = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND username = ?");
    $check_applied->bind_param("is", $row['job_id'], $_SESSION['username']);
    $check_applied->execute();
    $check_result = $check_applied->get_result();
    $already_applied = $check_result->num_rows > 0;
}

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

                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <!-- Theme Toggle Switch -->



                   

                    <!-- right side logo -->
                    <a href="profile.php">
                        <div class="relative">
                            <button class="flex flex-col justify-right items-center space-x-2">
                                <img alt="User profile" class="w-8 h-8 rounded-full justigy-between" src="uploads/<?php echo $row['logo']; ?>" />
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

                    <main class="flex-1 p-6">
                        <div class="mb-6">
                            <h1 class="text-3xl font-bold">Job Application </h1>
                            <p class="text-slate-400 mt-1">View the all jobs </p>
                        </div>

                        <?php
                        // Base query
                        $sql = "SELECT * FROM company_jobs WHERE 1=1";

                        // Salary filter
                        if (!empty($_GET['salary_min'])) {
                            $salary_min = (int) $_GET['salary_min'];
                            $sql .= " AND salary >= $salary_min";
                        }

                        // Distance filter (placeholder)
                        if (!empty($_GET['job_location'])) {
                            $distance = (int) $_GET['job_location'];
                            // Future: add actual distance calculation
                        }

                        // Sector filter
                        if (!empty($_GET['job_title'])) {
                            $sector = $conn->real_escape_string($_GET['job_title']);
                            $sql .= " AND job_title LIKE '%$sector%'";
                        }

                        // Job Type filter
                        if (!empty($_GET['work_mode'])) {
                            $job_type = $conn->real_escape_string($_GET['work_mode']);
                            $sql .= " AND work_mode = '$job_type'";
                        }

                        // Qualification filter
                        if (!empty($_GET['qualifications'])) {
                            $qualification = $conn->real_escape_string($_GET['qualifications']);
                            $sql .= " AND qualifications LIKE '%$qualifications%'";
                        }

                        // Experience Required filter
                        if (!empty($_GET['experience_required'])) {
                            $experience_required = $conn->real_escape_string($_GET['experience_required']);
                            $sql .= " AND experience_required LIKE '%$experience_required%'";
                        }

                        // Sorting
                        if (!empty($_GET['sort_by'])) {
                            if ($_GET['sort_by'] == 'newest') {
                                $sql .= " ORDER BY created_at DESC";
                            } elseif ($_GET['sort_by'] == 'salary') {
                                $sql .= " ORDER BY salary DESC";
                            } elseif ($_GET['sort_by'] == 'distance') {
                                $sql .= " ORDER BY location ASC";
                            }
                        } else {
                            $sql .= " ORDER BY created_at DESC"; // default sort
                        }

                        // Run query
                        $result = $conn->query($sql);

                        // ✅ Show message based on results
                        if ($result->num_rows > 0) {
                            echo "<div class='p-4 mb-4 text-green-600 bg-green-100 rounded-lg'>✅ Jobs found (" . $result->num_rows . ")</div>";
                        } else {
                            echo "<div class='p-4 mb-4 text-red-600 bg-red-100 rounded-lg'>❌ No jobs found</div>";
                        }
                        ?>



                        <!-- Filter Button -->
                        <div class="flex justify-between items-center mb-4">
                            <button id="openFilterBtn"
                                class="px-4 py-2 rounded-lg bg-purple-800 text-white hover:bg-slate-700 transition">
                                Filters
                            </button>
                        </div>

                        <!-- Filter Modal -->
                        <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-slate-800 rounded-2xl shadow-lg w-96 max-h-[90vh] overflow-y-auto p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-semibold">Filters</h2>
                                    <button id="closeFilterBtn" class="text-white-500 hover:text-black">&times;</button>
                                </div>

                                <form id="filterForm" method="GET" action="job_matches.php" class="space-y-4">

                                    <!-- Salary -->
                                    <div>
                                        <label class="block text-sm font-medium">Salary (Min)</label>
                                        <input type="number" name="salary_min" placeholder="e.g. 15000"
                                            class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-purple-700">
                                    </div>

                                    <!-- Distance -->
                                    <div>
                                        <label class="block text-sm font-medium">Job Location</label>
                                        <input type="text" name="job_location" placeholder="e.g. 10"
                                            class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-purple-700">
                                    </div>

                                    <!-- Sector -->
                                    <div>
                                        <label class="block text-sm font-medium">Job Title</label>
                                        <input type="text" name="job_title" placeholder="e.g. Php,Laravel"
                                            class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-purple-700">
                                    </div>

                                    <!-- Job Type -->
                                    <div>
                                        <label class="block text-sm font-medium">Job Type</label>
                                        <select name="work_mode" class="w-full mt-1 px-3 py-2 border rounded-lg">
                                            <option value="">Any</option>
                                            <option value="Full-Time">Full-Time</option>
                                            <option value="Part-Time">Part-Time</option>
                                            <option value="Remote">Remote</option>
                                        </select>
                                    </div>

                                    <!-- Qualification -->
                                    <div>
                                        <label class="block text-sm font-medium">Qualifications</label>
                                        <input type="text" name="qualifications" placeholder="e.g. BSc, MBA"
                                            class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-purple-700">
                                    </div>

                                    <!-- Experience -->
                                    <div>
                                        <label class="block text-sm font-medium">Experience Required</label>
                                        <input type="text" name="experience_required" placeholder="e.g. 2 years"
                                            class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-purple-700">
                                    </div>

                                    <!-- Sort By -->
                                    <div>
                                        <label class="block text-sm font-medium">Sort By</label>
                                        <select name="sort_by" class="w-full mt-1 px-3 py-2 border rounded-lg">
                                            <option value="newest">Newest</option>
                                            <option value="salary">Salary</option>
                                            <option value="distance">Distance</option>
                                        </select>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex justify-between mt-4">
                                        <button type="button" id="clearFilters" class="text-white-500">Clear All</button>
                                        <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-purple-700">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                        <!-- ✅ Job Cards Grid Container -->
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-3 p-4">

                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">
                                        <?php
                                        $already_applied = false;
                                        if ($_SESSION['role'] === 'user') {
                                            $check_applied = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND username = ?");
                                            $check_applied->bind_param("is", $row['job_id'], $_SESSION['username']);
                                            $check_applied->execute();
                                            $check_result = $check_applied->get_result();
                                            $already_applied = $check_result->num_rows > 0;
                                        }
                                        ?>

                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($row['job_title']) ?></h2>
                                                <p class="text-sm text-slate-400"><?= htmlspecialchars($row['company_name']) ?> • <?= htmlspecialchars($row['job_location']) ?></p>
                                            </div>
                                            <a href="view_company_profile.php?username=<?= $row['username'] ?>">
                                                <img src="uploads/<?= htmlspecialchars($row['company_logo']) ?>" alt="Company Logo"
                                                    class="w-12 h-12 object-cover rounded-full border border-slate-600">
                                            </a>
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

                                        <div class="mt-6 flex gap-4">

                                            <?php if ($already_applied): ?>
                                                <button class="px-4 py-2 rounded-md bg-gray-600 text-white cursor-not-allowed" disabled>Applied</button>
                                            <?php else: ?>
                                                <form action="apply_jobs.php" method="POST">
                                                    <input type="hidden" name="job_id" value="<?= $row['job_id'] ?> " onclick="">
                                                    <button type="submit" class="px-4 py-2 rounded-md bg-purple-600 text-white hover:bg-purple-700">Apply</button>
                                                </form>
                                            <?php endif; ?>

                                        </div>

                                    </div>
                                <?php endwhile; ?>


                        </div>
                    <?php endwhile; ?>

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
            <main class="flex-1 p-6">

                <div class="mb-6">
                    <h1 class="text-3xl font-bold">Job Application </h1>
                    <p class="text-slate-400 mt-1">View the all jobs </p>
                </div>
                <!-- ✅ Job Cards Grid Container -->
                <div class="grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-3 p-4">

                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="p-6 rounded-lg shadow bg-slate-800 job-card card-hover transition-all duration-300">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($row['job_title']) ?></h2>
                                    <p class="text-sm text-slate-400"><?= htmlspecialchars($row['company_name']) ?> • <?= htmlspecialchars($row['job_location']) ?></p>
                                </div>
                                <a href="view_company_profile.php?username=<?= $row['username'] ?>">
                                    <img src="uploads/<?= htmlspecialchars($row['company_logo']) ?>" alt="Company Logo"
                                        class="w-12 h-12 object-cover rounded-full border border-slate-600">
                                </a>
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

                            <div class="mt-6 flex gap-4">
                                <?php if ($_SESSION['role'] === 'user'): ?>
                                    <form action="apply_job.php" method="post">
                                        <input type="hidden" name="job_id" value="<?= $row['job_id'] ?>">
                                        <button type="submit" class="apply-btn px-4 py-2 rounded-md bg-slate-700 hover:bg-purple-700 text-white transition-all">
                                            Apply
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endwhile; ?>

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
    </div>
<?php endif; ?>
<script src="script.js"></script>
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


    document.getElementById('openFilterBtn').addEventListener('click', () => {
        document.getElementById('filterModal').classList.remove('hidden');
    });

    document.getElementById('closeFilterBtn').addEventListener('click', () => {
        document.getElementById('filterModal').classList.add('hidden');
    });

    document.getElementById('clearFilters').addEventListener('click', () => {
        document.getElementById('filterForm').reset();
    });
</script>

</body>

</html>