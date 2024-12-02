<?php
include 'partials/_dbconnect.php';
session_start();

$username = $_SESSION['username'];
$friend = htmlspecialchars($_GET['friend']);

// Fetch chat history
$sql = "SELECT sender, message, timestamp 
        FROM messages 
        WHERE (sender = ? AND receiver = ?) 
           OR (sender = ? AND receiver = ?) 
        ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $friend, $friend, $username);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sender = htmlspecialchars($row['sender']);
    $message = htmlspecialchars($row['message']);
    $timestamp = htmlspecialchars($row['timestamp']);

    if ($sender == $username) {
        echo "<div class='text-end mb-2'><strong>You:</strong> $message</div>";
    } else {
        echo "<div class='text-start mb-2'><strong>$sender:</strong> $message</div>";
    }
}
?>
