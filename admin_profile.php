<?php
session_start();
include 'db.php';

// ✅ Must be admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied. Only admins can view this page.'); window.location.href='index.php';</script>";
    exit;
}

// ✅ Require username in query
if (!isset($_GET['username'])) {
    echo "<script>alert('No username provided.'); window.location.href='manage_user.php';</script>";
    exit;
}
$target_username = $_GET['username'];

// ✅ Fetch profile info
$stmt = $conn->prepare("SELECT fullname, phone, address, email, birthdate, aboutme, skills, language, logo, username 
                        FROM user_profiles WHERE username=?");
$stmt->bind_param("s", $target_username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "<script>alert('Profile not found for this user.'); window.location.href='manage_user.php';</script>";
    exit;
}

$logoPath = !empty($row['logo']) ? "uploads/" . $row['logo'] : "default-logo.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col items-center p-6">

    <div class="w-full max-w-3xl bg-slate-800 rounded-xl shadow-lg p-6">
        <div class="flex items-center space-x-4 mb-6">
            <img src="<?= htmlspecialchars($logoPath) ?>" 
                 alt="User Logo" 
                 class="w-20 h-20 rounded-full border border-slate-600 object-cover">
            <div>
                <h1 class="text-2xl font-bold"><?= htmlspecialchars($row['fullname'] ?? 'No Name') ?></h1>
                <p class="text-slate-400"><?= htmlspecialchars($row['email'] ?? '') ?></p>
            </div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Profile Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm">Username</label>
                <p class="p-2 bg-slate-700 rounded"><?= htmlspecialchars($row['username']) ?></p>
            </div>
            <div>
                <label class="block text-slate-400 text-sm">Phone</label>
                <p class="p-2 bg-slate-700 rounded"><?= htmlspecialchars($row['phone'] ?? '-') ?></p>
            </div>
            <div>
                <label class="block text-slate-400 text-sm">Birthdate</label>
                <p class="p-2 bg-slate-700 rounded"><?= htmlspecialchars($row['birthdate'] ?? '-') ?></p>
            </div>
            <div>
                <label class="block text-slate-400 text-sm">Languages</label>
                <p class="p-2 bg-slate-700 rounded"><?= htmlspecialchars($row['language'] ?? '-') ?></p>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-slate-400 text-sm">Address</label>
            <p class="p-2 bg-slate-700 rounded"><?= nl2br(htmlspecialchars($row['address'] ?? '-')) ?></p>
        </div>

        <div class="mt-4">
            <label class="block text-slate-400 text-sm">About Me</label>
            <p class="p-2 bg-slate-700 rounded"><?= nl2br(htmlspecialchars($row['aboutme'] ?? '-')) ?></p>
        </div>

        <div class="mt-4">
            <label class="block text-slate-400 text-sm">Skills</label>
            <p class="p-2 bg-slate-700 rounded"><?= nl2br(htmlspecialchars($row['skills'] ?? '-')) ?></p>
        </div>

        <div class="mt-6 text-center">
            <a href="manage_user.php" 
               class="px-6 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-medium">
               Back to Manage Users
            </a>
        </div>
    </div>

</body>
</html>
