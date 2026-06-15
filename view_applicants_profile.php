<?php
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


$username = $_GET['username'] ?? '';

if (!$username) {
    echo "User not found.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User profile not available.";
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['fullname']) ?> - User Profile</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #12141d;
        }
    </style>
</head>
<body class="custom-scrollbar">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-3xl p-6 rounded-lg shadow-lg job-card">
        <div class="flex items-center space-x-6 mb-6">
            
<div class="relative inline-block">
    <img src="uploads/<?= htmlspecialchars($user['logo']) ?>" alt="user Logo"
                 class="w-24 h-24 rounded-full border border-slate-600">
    <?php if (is_verified($user)): ?>
        <span class="absolute -right-1 -bottom-1 w-6 h-6 rounded-full bg-white flex items-center justify-center border border-slate-300">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" fill="#1DA1F2"></circle>
                <path d="M16.5 9l-5.2 6-3.1-2.8" stroke="white" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    <?php endif; ?>
</div>

            <div>
                <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($user['fullname']) ?></h2>
                <p class="text-slate-400 text-sm"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        <hr class="border-slate-700 mb-6">
        <div class="text-slate-300 text-sm leading-relaxed space-y-4">
            <div>
                <h3 class="text-white font-medium">Description</h3>
                <p><?= nl2br(htmlspecialchars($user['aboutme'] ?? 'No description available.')) ?></p>
            </div>
            <div>
                <h3 class="text-white font-medium">Address</h3>
                <p><?= htmlspecialchars($user['address'] ?? 'Not provided.') ?></p>
            </div>
            <div>
                <h3 class="text-white font-medium">Contact No</h3>
                <p><?= htmlspecialchars($user['phone'] ?? 'Not provided.') ?></p>
            </div>
        </div>
        <div class="mt-6 text-right">
            <a href="view_applicants.php" class="apply-btn px-4 py-2 rounded bg-slate-700 text-white hover:bg-purple-700 transition">← Back to Jobs</a>
        </div>
    </div>
</div>

</body>
</html>
