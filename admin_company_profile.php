<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$username = $_GET['username'] ?? '';
if (!$username) {
    header("Location: manage_companies.php");
    exit();
}

$sql = "SELECT u.username, u.email, u.status, cp.* 
        FROM users u 
        LEFT JOIN company_profiles cp ON u.username = cp.username
        WHERE u.username = ? AND u.role = 'company'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$company) {
    header("Location: manage_companies.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Company Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
  <div class="max-w-3xl mx-auto bg-slate-800 p-6 rounded-xl shadow-lg">
    <div class="flex items-center gap-4">
      <img src="uploads/<?= htmlspecialchars($company['logo'] ?? 'default.png') ?>" 
           class="w-20 h-20 rounded-full border object-cover">
      <div>
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($company['company_name'] ?? $company['username']) ?></h1>
        <p class="text-gray-400">Status: 
          <span class="<?= $company['status']==='active'?'text-green-400':'text-red-400' ?>">
            <?= htmlspecialchars($company['status']) ?>
          </span>
        </p>
      </div>
    </div>

    <div class="mt-6 space-y-2">
      <p><strong>Email:</strong> <?= htmlspecialchars($company['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($company['phone'] ?? 'N/A') ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($company['address'] ?? 'N/A') ?></p>
      <p><strong>Description:</strong> <?= htmlspecialchars($company['description'] ?? 'N/A') ?></p>
    </div>

    <div class="mt-6">
      <a href="manage_companies.php" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700">← Back</a>
    </div>
  </div>
</body>
</html>
