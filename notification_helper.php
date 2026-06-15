<?php 
include 'db.php'; // ✅ keep this if needed for DB connection

function addNotification(mysqli $conn, string $receiver, string $message, ?string $link = null, ?string $status = null): void
{
    // Optional status formatting
    if ($status === 'Approved') {
        $status_text = "<span class='text-green-400 font-semibold'>Status: Approved</span>";
    } elseif ($status === 'Rejected') {
        $status_text = "<span class='text-red-400 font-semibold'>Status: Rejected</span>";
    } else {
        $status_text = "";
    }

    // Combine message if status is provided
    $formatted_message = "
        <div class='space-y-1 leading-6'>
            $message
            $status_text
        </div>
    ";

    $stmt = $conn->prepare("INSERT INTO notifications (receiver_username, message, link) VALUES (?,?,?)");
    $stmt->bind_param("sss", $receiver, $formatted_message, $link);
    $stmt->execute();
}
?>