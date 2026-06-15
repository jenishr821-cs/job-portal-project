<?php
session_start();
include 'db.php';

// ✅ Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$company = $_GET['company'] ?? '';
if (!$company) {
    header("Location: manage_companies.php");
    exit();
}

// fetch company jobs
$sql = "SELECT j.*, c.company_name, c.logo 
        FROM company_jobs j
        LEFT JOIN company_profiles c ON j.username = c.username
        WHERE j.username = ?
        ORDER BY j.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $company);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Company Jobs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">
      Jobs for <span class="text-indigo-400"><?= htmlspecialchars($company) ?></span>
    </h1>

    <?php if ($result->num_rows > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="p-6 bg-slate-800 rounded-lg shadow-lg">
            <div class="flex items-center gap-4 mb-3">
              <img src="uploads/<?= htmlspecialchars($row['logo'] ?? 'default.png') ?>" 
                   class="w-12 h-12 rounded-full object-cover border">
              <div>
                <h2 class="text-lg font-semibold"><?= htmlspecialchars($row['job_title']) ?></h2>
                <p class="text-gray-400 text-sm"><?= htmlspecialchars($row['company_name']) ?></p>
              </div>
            </div>

            <p class="text-gray-300 text-sm mb-3"><?= nl2br(htmlspecialchars(substr($row['job_description'], 0, 120))) ?>...</p>
            <p class="text-sm text-gray-400">Posted on <?= htmlspecialchars($row['created_at']) ?></p>

            <div class="mt-4 flex gap-2">
              <a href="admin_job_details.php?id=<?= $row['job_id'] ?>" class="px-3 py-1 bg-blue-600 rounded-lg text-sm hover:bg-blue-700">View</a>
              
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="p-6 bg-slate-800 rounded-lg text-center text-slate-400">
        No jobs found for this company.
      </div>
    <?php endif; ?>

    <div class="mt-6">
      <a href="manage_companies.php" class="px-4 py-2 bg-gray-600 rounded-lg hover:bg-gray-700">← Back</a>
    </div>
  </div>
</body>
</html>
