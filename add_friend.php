<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'partials/_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_user = $_SESSION['username'];
    $friend_username = $_POST['friend_username'];

    // Insert friendship into the database
    $sql = "INSERT INTO friends (user1, user2) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $current_user, $friend_username);
    
    // Execute and check for success
    if ($stmt->execute()) {
        header("Location: welcome.php");
        exit;
    } else {
        echo "Error adding friend: " . $stmt->error;
    }
}
?>
