<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the recipient username and message from POST
    $recipient = $_POST['recipient'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($recipient) && !empty($message)) {
        // Insert into email_logs table
        $stmt = $conn->prepare("INSERT INTO email_logs (recipient, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $recipient, $message);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Message logged successfully.";
        } else {
            echo "Failed to log message.";
        }

        $stmt->close();
    } else {
        echo "Recipient or message is empty!";
    }
}
?>
