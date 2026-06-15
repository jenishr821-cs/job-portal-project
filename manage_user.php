<?php
session_start();
include 'db.php';

// Fetch all users
$query = "SELECT u.username, u.status, p.fullname, p.email, p.logo 
          FROM users u 
          LEFT JOIN user_profiles p ON u.username = p.username
          WHERE u.role = 'user'";


$result = $conn->query($query);


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

if (isset($_GET['trusted_username'])) {
    $trusted_username = $_GET['trusted_username'];
    $stmt = $conn->prepare("UPDATE user_profiles SET is_verified = 1 WHERE username = ?");
    $stmt->bind_param("s", $trusted_username);
    $stmt->execute();
    echo "<script>alert('User verified successfully!'); window.location.href='manage_user.php';</script>";
}


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
                <a href="admin_dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
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


            <div class="mt-6">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-gray-600 rounded-lg hover:bg-gray-700">← Back</a>
            </div>
            <div class="p-6">


                <h1 class="text-2xl font-bold mb-6">Manage Users</h1>



                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="p-6 rounded-lg shadow bg-slate-800">
                            <!-- User Logo -->
                            <div class="flex items-center gap-4">
                                <a href="admin_profile.php?username=<?= urlencode($row['username']); ?>">
                                    <img src="uploads/<?= htmlspecialchars($row['logo'] ?? 'default.png') ?>"
                                        class="w-16 h-16 rounded-full object-cover border">
                                </a>
                                <div>
                                    <h2 class="text-lg font-semibold"><?= htmlspecialchars($row['fullname'] ?? $row['username']) ?></h2>
                                    <p class="text-gray-400 text-sm"><?= htmlspecialchars($row['email'] ?? '') ?></p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 flex gap-2">
                                <!-- Resume Button -->
                                <a href="admin_resume.php?username=<?= urlencode($row['username']); ?>"

                                    class="px-3 py-1 bg-indigo-600 rounded-lg text-sm hover:bg-indigo-700">
                                    Resume
                                </a>

                                <!-- Trusted Button -->
                                <a href="manage_user.php?trusted_username=<?= urlencode($row['username']); ?>"
                                    class="px-3 py-1 bg-blue-600 rounded-lg text-sm hover:bg-blue-700 text-white">
                                    Trusted
                                </a>


                                <!-- Banned Button -->
                                <?php if ($row['status'] === 'banned') { ?>
                                    <!-- Unban button -->
                                    <form action="set_status.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="px-3 py-1 bg-green-600 rounded-lg text-sm hover:bg-green-700">Unban</button>
                                    </form>
                                <?php } else { ?>
                                    <!-- Ban button -->
                                    <form action="set_status.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">
                                        <input type="hidden" name="status" value="banned">
                                        <button type="submit" class="px-3 py-1 bg-red-600 rounded-lg text-sm hover:bg-red-700">Ban</button>
                                    </form>
                                <?php } ?>

                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- bell icon script -->
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