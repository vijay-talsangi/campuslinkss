<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'partials/_dbconnect.php';

$username = $_SESSION['username'];

// Fetch friends
$sql = "SELECT user2 AS friend FROM friends WHERE user1 = ? 
        UNION 
        SELECT user1 AS friend FROM friends WHERE user2 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

include 'partials/_lnav.php';

// Handle friend removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_friend'])) {
    $friend_username = $_POST['friend_username'];

    // Delete friendship from the database
    $remove_sql = "DELETE FROM friends 
                   WHERE (user1 = ? AND user2 = ?) 
                      OR (user1 = ? AND user2 = ?)";
    $remove_stmt = $conn->prepare($remove_sql);
    $remove_stmt->bind_param("ssss", $username, $friend_username, $friend_username, $username);
    
    if ($remove_stmt->execute()) {
        echo "<script>alert('Friend removed successfully!'); window.location.href='welcome.php';</script>";
    } else {
        echo "<script>alert('Error removing friend. Please try again.');</script>";
    }
}

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
<div class="container mt-5">
    <h1>Welcome, ' . htmlspecialchars($username) . '!</h1>
    <div>
      <a href="./suggest_friends.php" class="btn btn-success">New Friend Suggestions</a>
    </div>
    <h2 class="mt-4">Your Friends</h2>
    <div class="row">';

// Display friends and their notifications
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $friend_username = htmlspecialchars($row['friend']);

        // Fetch unread notifications for each friend
        $notification_sql = "SELECT COUNT(*) AS unread_count 
                             FROM notifications 
                             WHERE receiver = ? 
                               AND sender = ? 
                               AND seen = 0";
        $notification_stmt = $conn->prepare($notification_sql);
        $notification_stmt->bind_param("ss", $username, $friend_username);
        $notification_stmt->execute();
        $notification_result = $notification_stmt->get_result();
        $notification_row = $notification_result->fetch_assoc();
        $unread_count = $notification_row['unread_count'];

        // Display friend's profile card with notifications
        echo '<div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="./person.jpg" class="card-img-top" alt="person">
                        <h5 class="card-title">' . $friend_username . '</h5>';
        
        // If there are unread messages, display a notification
        if ($unread_count > 0) {
            echo '<span class="badge bg-danger mb-2">' . $unread_count . ' new message(s)</span><br>';
        }

        echo '      <a href="chat.php?user=' . $friend_username . '" class="btn btn-primary">Chat</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="friend_username" value="' . $friend_username . '">
                            <button type="submit" name="remove_friend" class="btn btn-danger">Remove Friend</button>
                        </form>
                    </div>
                </div>
              </div>';
    }
} else {
    echo '<p>You have no friends yet.</p>';
}

echo '  </div>
</div>
</body>
</html>';
?>
