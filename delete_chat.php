<?php
session_start();
include 'partials/_dbconnect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$username = $_SESSION['username'];
$friend = htmlspecialchars($_POST['friend']);

// Ensure that both the sender and receiver are valid
if (!$friend || ($username === $friend)) {
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

// First, delete the related notifications
$delete_notifications_sql = "DELETE FROM notifications 
                             WHERE (receiver = ? AND sender = ?) 
                                OR (receiver = ? AND sender = ?)";
$delete_notifications_stmt = $conn->prepare($delete_notifications_sql);
$delete_notifications_stmt->bind_param("ssss", $username, $friend, $friend, $username);
$delete_notifications_stmt->execute();

// Now, delete the messages
$sql = "DELETE FROM messages WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Error preparing the query"]);
    exit;
}

$stmt->bind_param("ssss", $username, $friend, $friend, $username);

// Execute the query and check for errors
if ($stmt->execute()) {
    echo json_encode(["success" => "Chat deleted successfully"]);
} else {
    // Get error message from MySQL
    $error = $stmt->error;
    echo json_encode(["error" => "Failed to delete chat: " . $error]);
}
?>
