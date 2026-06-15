<?php
session_start();
include 'db.php';

// Allow only admin or company to view job details
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'company'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($jobId <= 0) {
    header('Location: manage_jobs.php');
    exit();
}

// Fetch job + company info (use correct column names from your DB)
$sql = "SELECT 
            j.job_id,
            j.username AS job_owner,
            COALESCE(NULLIF(j.company_name, ''), cp.company_name) AS company_name,
            j.job_title,
            j.job_type,
            j.job_location,
            j.salary,
            j.experience_required,
            j.job_description,
            j.responsibilities,
            j.qualifications,
            j.contact_no AS job_contact,
            j.email AS job_email,
            COALESCE(NULLIF(j.company_logo, ''), cp.logo) AS logo,
            j.number_of_openings,
            j.work_mode,
            j.benefits,
            j.created_at
        FROM company_jobs j
        LEFT JOIN company_profiles cp ON j.username = cp.username
        WHERE j.job_id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // helpful debug message (you can remove in production)
    die('DB prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param('i', $jobId);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();
$stmt->close();

if (!$job) {
    echo "<p class='text-red-400 p-6'>Job not found.</p>";
    exit();
}

// If the viewer is a company, make sure they own this job
if ($_SESSION['role'] === 'company' && ($_SESSION['username'] !== $job['job_owner'])) {
    http_response_code(403);
    exit('Unauthorized - this job does not belong to your company');
}

// choose a logo
$logoFile = !empty($job['logo']) ? $job['logo'] : 'default.png';

// back link: admin should return to company_jobs_admin.php, company to myjobs.php
if ($_SESSION['role'] === 'admin') {
    $backLink = 'company_jobs_admin.php?company=' . urlencode($job['job_owner']);
} else {
    $backLink = 'myjobs.php';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($job['job_title']) ?> — Job Details</title>
  <script src="https://kit.fontawesome.com/a2e0e9c6b1.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-6">
  <div class="max-w-4xl mx-auto bg-slate-800 p-6 rounded-xl shadow-lg">
    <div class="flex items-center gap-4 mb-4">
      <img src="uploads/<?= htmlspecialchars($logoFile) ?>" alt="logo" class="w-16 h-16 rounded-full object-cover border">
      <div>
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($job['job_title']) ?></h1>
        <p class="text-gray-400">Company: <?= htmlspecialchars($job['company_name'] ?? $job['job_owner']) ?></p>
        <p class="text-gray-400 text-sm">Posted: <?= htmlspecialchars($job['created_at']) ?></p>
      </div>
    </div>

    <div class="space-y-4 text-gray-300">
      <div>
        <h3 class="font-semibold">Description</h3>
        <div class="text-sm"><?= nl2br(htmlspecialchars($job['job_description'] ?? '')) ?></div>
      </div>

      <?php if (!empty($job['responsibilities'])): ?>
      <div>
        <h3 class="font-semibold">Responsibilities</h3>
        <div class="text-sm"><?= nl2br(htmlspecialchars($job['responsibilities'])) ?></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($job['qualifications'])): ?>
      <div>
        <h3 class="font-semibold">Qualifications</h3>
        <div class="text-sm"><?= nl2br(htmlspecialchars($job['qualifications'])) ?></div>
      </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-400">
        <div><strong>Location:</strong> <?= htmlspecialchars($job['job_location'] ?? 'N/A') ?></div>
        <div><strong>Salary:</strong> <?= htmlspecialchars($job['salary'] ?? 'N/A') ?></div>
        <div><strong>Experience Required:</strong> <?= htmlspecialchars($job['experience_required'] ?? 'N/A') ?></div>
        <div><strong>Openings:</strong> <?= htmlspecialchars($job['number_of_openings'] ?? '1') ?></div>
        <div><strong>Work Mode:</strong> <?= htmlspecialchars($job['work_mode'] ?? 'N/A') ?></div>
        <div><strong>Benefits:</strong> <?= nl2br(htmlspecialchars($job['benefits'] ?? 'N/A')) ?></div>
      </div>

      <div class="mt-4 text-sm text-gray-300">
        <strong>Contact:</strong>
        <div><?= htmlspecialchars($job['job_contact'] ?? $job['job_email'] ?? 'N/A') ?></div>
      </div>
    </div>

    <div class="mt-6 flex gap-2">
      <a href="manage_company_jobs.php" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700">← Back</a>

      <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Admin can delete job -->

      <?php elseif ($_SESSION['role'] === 'company' && $_SESSION['username'] === $job['job_owner']): ?>
        <!-- Company that owns the job can edit or delete -->
        <a href="edit_job.php?id=<?= htmlspecialchars($job['job_id']) ?>" class="px-4 py-2 bg-amber-600 rounded-lg hover:bg-amber-700">Edit Job</a>
        <form action="delete_job.php" method="post" onsubmit="return confirm('Delete this job?');">
          <input type="hidden" name="id" value="<?= htmlspecialchars($job['job_id']) ?>">
          
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
