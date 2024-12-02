<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'partials/_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $interests = $_POST['interests'];

    if (!empty($interests)) {
        // Clear old interests (optional if user is first time logging in)
        //$delete_sql = "DELETE FROM user_interests WHERE username = ?";
        //$stmt = $conn->prepare($delete_sql);
        //$stmt->bind_param("s", $username);
        //$stmt->execute();

        // Insert new interests
        $insert_sql = "INSERT INTO user_interests (username, interest) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);

        foreach ($interests as $interest) {
            $stmt->bind_param("ss", $username, $interest);
            $stmt->execute();
        }

        // Update `is_first_login` flag
        $update_sql = "UPDATE users SET is_first_login = 0 WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        header("Location: welcome.php");
    } else {
        echo "Please select at least one interest.";
    }
}
?>
