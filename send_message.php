<?php
include 'partials/_dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];

    $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();

    // After the message is inserted into the messages table
    $sql = "INSERT INTO notifications (sender, receiver, message_id) 
    VALUES (?, ?, LAST_INSERT_ID())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $sender, $receiver);
    $stmt->execute();

    $sql_notification = "INSERT INTO notifications (sender, receiver, message, seen) 
                     VALUES (?, ?, ?, 0)";
    $stmt_notification = $conn->prepare($sql_notification);
    $stmt_notification->bind_param("sss", $sender, $receiver, $message);
    $stmt_notification->execute();
}
?>
