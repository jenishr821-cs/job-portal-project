<?php
session_start();
include 'db.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

// Handle close job request (admin side)
if (isset($_POST['close_job'])) {
    $job_id = intval($_POST['job_id']);
    $conn->query("UPDATE company_jobs SET status = 'closed' WHERE id = $job_id");
}

// Fetch all jobs with company info
$sql = "SELECT company_jobs.*, users.username AS company_name 
        FROM company_jobs
        JOIN users ON company_jobs.job_id = users.id
        ORDER BY company_jobs.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Jobs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Manage Jobs</h2>

  <table class="table table-bordered table-hover bg-white shadow-sm">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Job Title</th>
        <th>Company</th>
        <th>Status</th>
        <th>Posted At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['job_id'] ?></td>
            <td><?= htmlspecialchars($row['job_title']) ?></td>
            <td><?= htmlspecialchars($row['company_name']) ?></td>
            <td>
              <?php if ($row['status'] === 'active'): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Closed</span>
              <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <?php if ($row['status'] === 'active'): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="job_id" value="<?= $row['id'] ?>">
                  <button type="submit" name="close_job" class="btn btn-danger btn-sm">Close</button>
                </form>
              <?php else: ?>
                <button class="btn btn-secondary btn-sm" disabled>Closed</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No jobs found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>

</body>
</html>
