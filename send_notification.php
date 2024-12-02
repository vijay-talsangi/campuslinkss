<?php
include 'partials/_dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];

    // Insert a new notification for the receiver
    $sql = "INSERT INTO notifications (sender, receiver, message_id) 
            SELECT ?, ?, LAST_INSERT_ID()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $sender, $receiver);
    $stmt->execute();
}
?>
