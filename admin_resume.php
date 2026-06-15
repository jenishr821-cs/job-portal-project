<?php
session_start();
include 'db.php';
include 'notification_helper.php';

// --- DEBUG (optional: comment in production) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$notifications = [];
$noti_count = 0;

// Basic auth guard
if ($username === '' || ($role !== 'user' && $role !== 'admin' && $role !== 'company')) {
    http_response_code(403);
    exit('Unauthorized access.');
}

//bell icon

// fetch last 10 notifications for current admin
$adminUser = $_SESSION['username'];

$nsql = "SELECT id, message, link, is_read, created_at, type
         FROM notifications
         WHERE receiver_username = ?
         ORDER BY created_at DESC
         LIMIT 10";
$nstmt = $conn->prepare($nsql);
$nstmt->bind_param("s", $adminUser);
$nstmt->execute();
$notifications = $nstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$nstmt->close();

// unread count
$csql = "SELECT COUNT(*) AS unread FROM notifications WHERE receiver_username = ? AND is_read = 0";
$cstmt = $conn->prepare($csql);
$cstmt->bind_param("s", $adminUser);
$cstmt->execute();
$unreadRow = $cstmt->get_result()->fetch_assoc();
$unread = (int)($unreadRow['unread'] ?? 0);
$cstmt->close();


// ---------- Notifications & Logo (for header) ----------
if ($username !== '') {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE receiver_username = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE receiver_username = ? AND is_read = 0");
    $count_stmt->bind_param("s", $username);
    $count_stmt->execute();
    $noti_count = ($count_stmt->get_result()->fetch_assoc()['unread'] ?? 0);

    if ($role === 'company') {
        $logo_sql = $conn->prepare("SELECT company_name, email, logo FROM company WHERE username = ?");
    } else {
        $logo_sql = $conn->prepare("SELECT fullname, email, logo FROM user_profiles WHERE username = ?");
    }
    $logo_sql->bind_param("s", $username);
    $logo_sql->execute();
    $header_profile = $logo_sql->get_result()->fetch_assoc();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ---------- Decide whose resume to show ----------
$target_username = null;
if ($role === 'admin') {
    // Admin can view a specific user's resume via query param
    if (!empty($_GET['username'])) {
        $target_username = $_GET['username'];
    } else {
        // If admin came without username, show an info message
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>View User Resume</title></head><body>';
        echo '<p style="font-family:system-ui">No username selected. Please open from <strong>manage_user.php</strong> using the Resume button.</p>';
        echo '</body></html>';
        exit;
    }
} else {
    // Normal user only sees their own resume
    $target_username = $username;
}

// ---------- Handle POST (only users can update their own resume) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role !== 'user') {
        http_response_code(403);
        exit('Admins cannot modify resumes.');
    }

    // Trust only the session username for updates
    $post_username = $username;

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role_field = $_POST['role'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $address = $_POST['address'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $university = $_POST['university'] ?? '';
    $passing_year = $_POST['passing_year'] ?? '';
    $percentage = $_POST['percentage'] ?? '';
    $job_title = $_POST['job_title'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $work_duration_from = $_POST['work_duration_from'] ?? '';
    $work_duration_to = $_POST['work_duration_to'] ?? '';
    $previous_salary = $_POST['previous_salary'] ?? '';
    $expected_salary = $_POST['expected_salary'] ?? '';
    $programming_languages = $_POST['programming_languages'] ?? '';
    $tools_frameworks = $_POST['tools_frameworks'] ?? '';
    $hobbies = $_POST['hobbies'] ?? '';
    $ref_name = $_POST['ref_name'] ?? '';
    $ref_position = $_POST['ref_position'] ?? '';
    $ref_contact = $_POST['ref_contact'] ?? '';
    $ref_email = $_POST['ref_email'] ?? '';

    $query = "UPDATE create_resume SET 
        name = ?, email = ?, phone = ?, role = ?, experience = ?, address = ?, summary = ?, 
        degree = ?, university = ?, passing_year = ?, percentage = ?, 
        job_title = ?, company_name = ?, work_duration_from = ?, work_duration_to = ?, previous_salary = ?, expected_salary = ?,
        programming_languages = ?, tools_frameworks = ?, hobbies = ?, 
        ref_name = ?, ref_position = ?, ref_contact = ?, ref_email = ?
        WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssissssssssssssssssssss",
        $name,
        $email,
        $phone,
        $role_field,
        $experience,
        $address,
        $summary,
        $degree,
        $university,
        $passing_year,
        $percentage,
        $job_title,
        $company_name,
        $work_duration_from,
        $work_duration_to,
        $previous_salary,
        $expected_salary,
        $programming_languages,
        $tools_frameworks,
        $hobbies,
        $ref_name,
        $ref_position,
        $ref_contact,
        $ref_email,
        $post_username
    );
    if ($stmt->execute()) {
        echo "<script>alert('Resume updated successfully!'); window.location.href='myresume.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating resume.');</script>";
    }
}

// ---------- Fetch Resume ----------
$resume = null;
$stmt = $conn->prepare("SELECT * FROM create_resume WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $target_username);
$stmt->execute();
$res = $stmt->get_result();
$resume = $res->fetch_assoc();
$stmt->close();

// Fetch Profile Info (logo, fullname, email) for target user (left sidebar card)
$profile_stmt = $conn->prepare("SELECT fullname, email, logo FROM user_profiles WHERE username = ? LIMIT 1");
$profile_stmt->bind_param("s", $target_username);
$profile_stmt->execute();
$row = $profile_stmt->get_result()->fetch_assoc();
$profile_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://kit.fontawesome.com/a2e0e9c6b1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-900 text-white">

    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <!-- Branding -->
            <div class="flex items-center justify-center h-16 border-b border-slate-700">
                <span class="text-xl font-bold">
                    <span class="text-indigo-400">Nexus</span><span class="text-purple-400">Career</span>
                </span>
            </div>



            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition ">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="manage_user.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-users mr-2"></i> Manage Users
                </a>
                <a href="manage_companies.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-building mr-2"></i> Manage Companies
                </a>
                <a href="manage_jobs.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-briefcase mr-2"></i> Manage Jobs
                </a>
                <a href="settings.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-cog mr-2"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-bold mb-4">Welcome, Admin 👋</h1>
            <p class="text-gray-300">Here you can manage users, companies, and job postings.</p>

            <!-- bell icon -->
            <div class="flex items-center justify-end mb-4">
                <div class="relative">
                    <button id="bellBtn" class="relative px-3 py-2 bg-slate-800 rounded-xl hover:bg-slate-700">
                        <span class="text-xl">🔔</span>
                        <?php if (!empty($unread)): ?>
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1.5 rounded-full">
                                <?= (int)$unread ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown -->
                    <div id="bellDropdown" class="hidden absolute right-0 mt-2 w-80 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-50">
                        <?php if (empty($notifications)): ?>
                            <div class="p-4 text-sm text-gray-300">No notifications yet.</div>
                        <?php else: ?>
                            <div class="max-h-96 overflow-y-auto">
                                <?php foreach ($notifications as $n): ?>
                                    <a href="<?= htmlspecialchars($n['link'] ?? '#') ?>" class="block p-3 hover:bg-slate-700">
                                        <div class="text-sm text-white leading-snug"><?= $n['message'] ?></div>
                                        <div class="text-[11px] text-gray-400 mt-1"><?= htmlspecialchars($n['created_at']) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resume Form (read-only for admin) -->
            <form action="myresume.php" method="post" id="resume-form">
                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($resume['name'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($resume['email'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($resume['phone'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-slate-300 mb-1">Current Role</label>
                            <input type="text" id="role" name="role" value="<?= htmlspecialchars($resume['role'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="experience" class="block text-sm font-medium text-slate-300 mb-1">Years of Experience</label>
                            <input type="number" id="experience" name="experience" value="<?= htmlspecialchars($resume['experience'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-300 mb-1">User Name</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($resume['username'] ?? $target_username) ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly required />
                        </div>
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                            <textarea id="address" name="address" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required><?= htmlspecialchars($resume['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Career Objective</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="summary" class="block text-sm font-medium text-slate-300 mb-1">Summary</label>
                            <textarea id="summary" name="summary" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" <?= ($role === 'admin') ? 'readonly' : '' ?> required><?= htmlspecialchars($resume['summary'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Qualification</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="degree" class="block text-sm font-medium text-slate-300 mb-1">Degree</label>
                            <input type="text" id="degree" name="degree" value="<?= htmlspecialchars($resume['degree'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="university" class="block text-sm font-medium text-slate-300 mb-1">University</label>
                            <input type="text" id="university" name="university" value="<?= htmlspecialchars($resume['university'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="passing_year" class="block text-sm font-medium text-slate-300 mb-1">Passing Year</label>
                            <input type="number" id="passing_year" name="passing_year" value="<?= htmlspecialchars($resume['passing_year'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="percentage" class="block text-sm font-medium text-slate-300 mb-1">Percentage</label>
                            <input type="number" step="0.01" id="percentage" name="percentage" value="<?= htmlspecialchars($resume['percentage'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Work Experience</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="job_title" class="block text-sm font-medium text-slate-300 mb-1">Job Title</label>
                            <input type="text" id="job_title" name="job_title" value="<?= htmlspecialchars($resume['job_title'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                            <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($resume['company_name'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="work_duration_from" class="block text-sm font-medium text-slate-300 mb-1">Duration (From)</label>
                            <input type="date" id="work_duration_from" name="work_duration_from" value="<?= htmlspecialchars($resume['work_duration_from'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="work_duration_to" class="block text-sm font-medium text-slate-300 mb-1">Duration (To)</label>
                            <input type="date" id="work_duration_to" name="work_duration_to" value="<?= htmlspecialchars($resume['work_duration_to'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Salary</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="previous_salary" class="block text-sm font-medium text-slate-300 mb-1">Previous Salary</label>
                            <input type="number" id="previous_salary" name="previous_salary" value="<?= htmlspecialchars($resume['previous_salary'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="expected_salary" class="block text-sm font-medium text-slate-300 mb-1">Expected Salary</label>
                            <input type="number" id="expected_salary" name="expected_salary" value="<?= htmlspecialchars($resume['expected_salary'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">Skills</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="programming_languages" class="block text-sm font-medium text-slate-300 mb-1">Programming Languages</label>
                            <input type="text" id="programming_languages" name="programming_languages" value="<?= htmlspecialchars($resume['programming_languages'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="tools_frameworks" class="block text-sm font-medium text-slate-300 mb-1">Tools/Frameworks</label>
                            <input type="text" id="tools_frameworks" name="tools_frameworks" value="<?= htmlspecialchars($resume['tools_frameworks'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow bg-slate-800">
                    <h3 class="mb-4 text-lg font-semibold text-white">References</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="ref_name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                            <input type="text" id="ref_name" name="ref_name" value="<?= htmlspecialchars($resume['ref_name'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="ref_position" class="block text-sm font-medium text-slate-300 mb-1">Company Position</label>
                            <input type="text" id="ref_position" name="ref_position" value="<?= htmlspecialchars($resume['ref_position'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="ref_contact" class="block text-sm font-medium text-slate-300 mb-1">Company Contact No</label>
                            <input type="tel" id="ref_contact" name="ref_contact" value="<?= htmlspecialchars($resume['ref_contact'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                        <div>
                            <label for="ref_email" class="block text-sm font-medium text-slate-300 mb-1">Company Email</label>
                            <input type="email" id="ref_email" name="ref_email" value="<?= htmlspecialchars($resume['ref_email'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white" <?= ($role === 'admin') ? 'readonly' : '' ?> required />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center gap-3">
                        <?php if ($role === 'user'): ?>
                            <button type="button" id="edit-btn" onclick="toggleEdit()" class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </button>
                        <?php else: ?>
                            <a href="manage_user.php" class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90 inline-flex items-center"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <?php if (!$resume): ?>
                <div class="p-6">
                    <div class="rounded-md bg-yellow-100 text-yellow-800 px-4 py-3">No resume found for <strong><?= htmlspecialchars($target_username) ?></strong>.</div>
                </div>
            <?php endif; ?>

    </div>
    </div>

    <script>
        const bellBtn = document.getElementById('bellBtn');
        const bellDropdown = document.getElementById('bellDropdown');

        if (bellBtn) {
            bellBtn.addEventListener('click', () => {
                bellDropdown.classList.toggle('hidden');
            });
            // close on outside click
            document.addEventListener('click', (e) => {
                if (!bellBtn.contains(e.target) && !bellDropdown.contains(e.target)) {
                    bellDropdown.classList.add('hidden');
                }
            });
        }
    </script>
</body>

</html>