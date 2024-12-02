<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'partials/_dbconnect.php';
include 'partials/_lnav.php';
$username = $_SESSION['username'];

// Fetch suggested friends (users with shared interests, excluding already added friends)
$query = "
    SELECT ui.username, 
           GROUP_CONCAT(DISTINCT ui.interest ORDER BY ui.interest SEPARATOR ', ') AS interests
    FROM user_interests ui
    WHERE ui.username != ?  -- Exclude the logged-in user
      AND ui.username NOT IN (  -- Exclude the existing friends
          SELECT user2 
          FROM friends
          WHERE user1 = ? 
          UNION
          SELECT user1
          FROM friends
          WHERE user2 = ?
      )
      AND ui.username IN (  -- Suggest users with common interests
          SELECT username
          FROM user_interests
          WHERE interest IN (
              SELECT interest
              FROM user_interests
              WHERE username = ?
          )
      )
    GROUP BY ui.username";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $username, $username, $username, $username);
$stmt->execute();
$suggested_result = $stmt->get_result();

// Fetch all members
$all_members_query = "
    SELECT u.username, 
           GROUP_CONCAT(DISTINCT ui.interest ORDER BY ui.interest SEPARATOR ', ') AS interests
    FROM users u
    LEFT JOIN user_interests ui ON u.username = ui.username
    WHERE u.username != ?
    GROUP BY u.username";
$all_members_stmt = $conn->prepare($all_members_query);
$all_members_stmt->bind_param("s", $username);
$all_members_stmt->execute();
$all_members_result = $all_members_stmt->get_result();

// Handle friend addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend'])) {
    $friend_username = $_POST['friend_username'];
    $sql = "INSERT IGNORE INTO friends (user1, user2) VALUES (LEAST(?, ?), GREATEST(?, ?))";
    $add_stmt = $conn->prepare($sql);

    if (!$add_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $add_stmt->bind_param("ssss", $username, $friend_username, $username, $friend_username);

    if ($add_stmt->execute()) {
        echo "<script>alert('Friend added successfully!'); window.location.href='welcome.php';</script>";
    } else {
        die("Execute failed: " . $add_stmt->error);
    }
}

// Display page content
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Suggested Friends</title>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Suggested Friends</h1>
    <div class="row">';

// Suggested Friends Section
if ($suggested_result->num_rows > 0) {
    while ($row = $suggested_result->fetch_assoc()) {
        $friend_username = htmlspecialchars($row['username']);
        $friend_interests = htmlspecialchars($row['interests']);
        echo '<div class="col-md-4 mb-4">
                <div class="card text-center" style="width: 12rem;">
                    <div class="card-body">
                        <img src="./person.jpg" class="card-img-top" alt="person">
                        <h5 class="card-title">' . $friend_username . '</h5>
                        <p class="card-text"><strong>Interests:</strong> ' . $friend_interests . '</p>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="friend_username" value="' . $friend_username . '">
                            <button type="submit" name="add_friend" class="btn btn-success">Add Friend</button>
                        </form>
                    </div>
                </div>
              </div>';
    }
} else {
    echo '<p>No friends with similar interests found.</p>';
}

echo '</div>
    <h1 class="mb-4">All Members</h1>
    <div class="row">';

// All Members Section
if ($all_members_result->num_rows > 0) {
    while ($row = $all_members_result->fetch_assoc()) {
        $member_username = htmlspecialchars($row['username']);
        $member_interests = htmlspecialchars($row['interests']);
        echo '<div class="col-md-4 mb-4">
                <div class="card text-center" style="width: 12rem;">
                    <div class="card-body">
                        <img src="./person.jpg" class="card-img-top" alt="person">
                        <h5 class="card-title">' . $member_username . '</h5>
                        <p class="card-text"><strong>Interests:</strong> ' . $member_interests . '</p>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="friend_username" value="' . $member_username . '">
                            <button type="submit" name="add_friend" class="btn btn-success">Add Friend</button>
                        </form>
                    </div>
                </div>
              </div>';
    }
} else {
    echo '<p>No members found.</p>';
}

echo '</div>
</div>
</body>
</html>';
?>
