<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}



// fetch last 10 notifications for current admin (re-using admin_dashboard logic)
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

// Fetch companies (users with role = 'company')
$sql = "SELECT u.username, u.email, u.status, cp.company_name, cp.phone, cp.logo
        FROM users u
        LEFT JOIN company_profiles cp ON u.username = cp.username
        WHERE u.role = 'company'
        ORDER BY cp.company_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Companies</title>
    <script src="https://kit.fontawesome.com/a2e0e9c6b1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-900 text-white">

    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <div class="flex items-center justify-center h-16 border-b border-slate-700">
                <span class="text-xl font-bold">
                    <span class="text-indigo-400">Nexus</span><span class="text-purple-400">Career</span>
                </span>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="manage_user.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-users mr-2"></i> Manage Users
                </a>
                <a href="manage_company.php" class="flex items-center px-3 py-2 rounded-lg bg-slate-700 transition">
                    <i class="fas fa-building mr-2"></i> Manage Companies
                </a>
                <a href="manage_jobs.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-briefcase mr-2"></i> Manage Jobs
                </a>
                <a href="settings.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->

        <main class="flex-1 p-6 overflow-y-auto">

            <div class="mt-6">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-gray-600 rounded-lg hover:bg-gray-700">← Back</a>
            </div>
            <div class="p-6">


                <h1 class="text-2xl font-bold mb-6">Manage Companies</h1>

                <!-- bell icon -->
                <div class="flex items-center justify-end mb-4">
                    <div class="relative">
                        <button id="bellBtn" class="relative px-3 py-2 bg-slate-800 rounded-xl hover:bg-slate-700">
                            <span class="text-xl">🔔</span>
                            <?php if (!empty($unread)): ?>
                                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1.5 rounded-full"><?= (int)$unread ?></span>
                            <?php endif; ?>
                        </button>

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

                <div class="p-6">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="p-6 rounded-lg shadow bg-slate-800">
                                    <div class="flex items-center gap-4">
                                        <a href="company_profile.php?username=<?= urlencode($row['username']); ?>">
                                            <img src="uploads/<?= htmlspecialchars($row['logo'] ?? 'default.png') ?>" class="w-16 h-16 rounded-full object-cover border">
                                        </a>
                                        <div>
                                            <h2 class="text-lg font-semibold"><?= htmlspecialchars($row['company_name'] ?? $row['username']) ?></h2>
                                            <p class="text-gray-400 text-sm"><?= htmlspecialchars($row['email'] ?? '') ?></p>
                                            <?php if (!empty($row['phone'])): ?>
                                                <p class="text-gray-400 text-sm">Phone: <?= htmlspecialchars($row['phone']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="admin_company_profile.php?username=<?= urlencode($row['username']); ?>" class="px-3 py-1 bg-blue-600 rounded-lg text-sm hover:bg-blue-700">Profile</a>
                                        <a href="manage_company_jobs.php?company=<?= urlencode($row['username']); ?>" class="px-3 py-1 bg-indigo-600 rounded-lg text-sm hover:bg-indigo-700">Jobs</a>

                                        <!-- Ban / Unban -->
                                        <?php if ($row['status'] === 'active') { ?>
                                            <!-- Ban button -->
                                            <form action="set1_status.php" method="post" onsubmit="return confirm('Are you sure you want to ban this company?');">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($row['username']) ?>">
                                                <input type="hidden" name="status" value="banned">
                                                <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 rounded-lg text-sm text-white">
                                                    Ban
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <!-- Unban button -->
                                            <form action="set1_status.php" method="post" onsubmit="return confirm('Are you sure you want to unban this company?');">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($row['username']) ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 rounded-lg text-sm text-white">
                                                    Unban
                                                </button>
                                            </form>
                                        <?php } ?>


                                        <!-- Delete (optional) -->

                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-6 bg-slate-800 rounded-lg text-center text-slate-400">No companies found.</div>
                    <?php endif; ?>
                </div>

                <!-- bell icon script -->
                <script>
                    const bellBtn = document.getElementById('bellBtn');
                    const bellDropdown = document.getElementById('bellDropdown');

                    if (bellBtn) {
                        bellBtn.addEventListener('click', () => {
                            bellDropdown.classList.toggle('hidden');
                        });
                        document.addEventListener('click', (e) => {
                            if (!bellBtn.contains(e.target) && !bellDropdown.contains(e.target)) {
                                bellDropdown.classList.add('hidden');
                            }
                        });
                    }
                </script>

        </main>

    </div>

</body>

</html>