<?php
session_start();
include 'db.php';

$username = $_SESSION['username'] ?? '';
$notifications = [];

if ($username !== '') {
    $stmt = $conn->prepare("SELECT id, message, link, is_read, created_at FROM notifications WHERE receiver_username = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-4">Notifications</h1>

        <div class="bg-white shadow-md rounded-lg p-4 space-y-4">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $noti): ?>
                    <div class="border-b pb-2">
                        <!-- ✅ Render raw HTML from DB -->
                        <div class="text-gray-800">
                           <?= htmlspecialchars($noti['message']); ?>
                        </div>

                        <?php if (!empty($noti['link'])): ?>
                            <a href="<?= htmlspecialchars($noti['link']); ?>" class="text-blue-500 text-sm hover:underline">View</a>
                        <?php endif; ?>

                        <p class="text-xs text-gray-400"><?= $noti['created_at']; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">No notifications available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
