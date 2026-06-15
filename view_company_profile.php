<?php
include 'db.php';
$username = $_GET['username'] ?? '';

if (!$username) {
    echo "Company not found.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM company_profiles WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Company profile not available.";
    exit();
}

$company = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($company['company_name']) ?> - Company Profile</title>
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
            <img src="uploads/<?= htmlspecialchars($company['logo']) ?>" alt="Company Logo"
                 class="w-24 h-24 rounded-full border border-slate-600">
            <div>
                <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($company['company_name']) ?></h2>
                <p class="text-slate-400 text-sm"><?= htmlspecialchars($company['email']) ?></p>
            </div>
        </div>
        <hr class="border-slate-700 mb-6">
        <div class="text-slate-300 text-sm leading-relaxed space-y-4">
            <div>
                <h3 class="text-white font-medium">About Company</h3>
                <p><?= nl2br(htmlspecialchars($company['description'] ?? 'No description available.')) ?></p>
            </div>
            <div>
                <h3 class="text-white font-medium">Address</h3>
                <p><?= htmlspecialchars($company['address'] ?? 'Not provided.') ?></p>
            </div>
            <div>
                <h3 class="text-white font-medium">Contact No</h3>
                <p><?= htmlspecialchars($company['phone'] ?? 'Not provided.') ?></p>
            </div>
        </div>
        <div class="mt-6 text-right">
            <a href="job matches.php" class="apply-btn px-4 py-2 rounded bg-slate-700 text-white hover:bg-purple-700 transition">← Back to Jobs</a>
        </div>
    </div>
</div>

</body>
</html>
