<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$successMsg = $errorMsg = "";

// Handle Query Submission
if (isset($_POST['send_query'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $insert = "INSERT INTO notifications (receiver_username, message, type) 
               VALUES ('admin', '<strong>$username</strong> sent a query.<br>Email: $email<br>Phone: $phone<br>Message: $message', 'query')";

    if ($conn->query($insert)) {
        $successMsg = "✅ Your query has been sent to admin.";
    } else {
        $errorMsg = "❌ Failed to send query. Try again.";
    }
}

// Handle Account Deletion
if (isset($_POST['delete_account'])) {
    $delete = "DELETE FROM users WHERE username='$username'";
    if ($conn->query($delete)) {
        session_destroy();
        header("Location: login.php?msg=Account Deleted Successfully");
        exit;
    } else {
        $errorMsg = "❌ Failed to delete account.";
    }
}

// Fetch role
$role = "";
$sqlUser = $conn->query("SELECT role FROM users WHERE username='$username' LIMIT 1");
if ($sqlUser && $sqlUser->num_rows > 0) {
    $rowUser = $sqlUser->fetch_assoc();
    $role = $rowUser['role'];
}

// Profile data
$logo = "default.png";
$fullname = "";
$email = "";

if ($role === 'user') {
    $sqlProfile = $conn->query("SELECT logo, fullname, email FROM user_profiles WHERE username='$username' LIMIT 1");
    if ($sqlProfile && $sqlProfile->num_rows > 0) {
        $rowProfile = $sqlProfile->fetch_assoc();
        $logo = $rowProfile['logo'] ?: "default.png";
        $fullname = $rowProfile['fullname'];
        $email = $rowProfile['email'];
    }
} elseif ($role === 'company') {
    $sqlCompany = $conn->query("SELECT logo, company_name, email FROM company_profiles WHERE username='$username' LIMIT 1");
    if ($sqlCompany && $sqlCompany->num_rows > 0) {
        $rowCompany = $sqlCompany->fetch_assoc();
        $logo = $rowCompany['logo'] ?: "default.png";
        $fullname = $rowCompany['company_name'];
        $email = $rowCompany['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>NexusCareer - Settings</title>
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
</head>

<body class="dark">
    <!-- Main Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-slate-900 border-r border-slate-800">
                <div class="flex items-center h-16 px-4">
                    <h1 class="text-2xl font-bold text-white">
                        <a href="index.php">Nexus<span class="text-primary-600">Career</span></a>
                    </h1>
                </div>

                <!-- User/Company Profile -->
                <a href="profile.php">
                    <div class="flex items-center px-4 py-3 space-x-3 border-b border-slate-800">
                        <img src="uploads/<?php echo $logo; ?>" alt="Logo" class="w-10 h-10 rounded-full" />
                        <div>
                            <p class="font-medium text-white"><?php echo $fullname ?: $username; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $email; ?></p>
                        </div>
                    </div>
                </a>

                <!-- Navigation -->
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

            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-8">
            <h1 class="text-2xl font-bold text-black-200 mb-8">⚙️ Settings</h1>

            <!-- Success/Error Messages -->
            <?php if ($successMsg): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-800 text-green-200 shadow"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-800 text-red-200 shadow"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Query Card -->
                <div class="bg-slate-900 rounded-2xl shadow-lg p-6 border border-slate-800">
                    <h2 class="text-lg font-semibold text-gray-200 mb-4">📩 Send Query</h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Username</label>
                            <input type="text" value="<?php echo $username; ?>" readonly
                                class="w-full p-2 border border-slate-700 rounded-lg bg-slate-800 text-gray-200 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Email</label>
                            <input type="email" name="email" required
                                class="w-full p-2 border border-slate-700 rounded-lg bg-slate-800 text-gray-200 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Phone</label>
                            <input type="text" name="phone" required
                                class="w-full p-2 border border-slate-700 rounded-lg bg-slate-800 text-gray-200 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Message</label>
                            <textarea name="message" rows="4" required
                                class="w-full p-2 border border-slate-700 rounded-lg bg-slate-800 text-gray-200 focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" name="send_query"
                            class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
                            Send Query
                        </button>
                    </form>
                </div>

                <!-- Delete Account Card -->
                <div class="bg-slate-900 rounded-2xl shadow-lg p-6 flex flex-col justify-between border border-slate-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-200 mb-4">🗑️ Delete Account</h2>
                        <p class="text-sm text-gray-400 mb-6">
                            Deleting your account will remove only your login credentials. 
                            Your profile, resumes, and applications remain visible to the admin.
                        </p>
                    </div>
                    <form method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete your account?');">
                        <button type="submit" name="delete_account"
                            class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                            Delete My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
