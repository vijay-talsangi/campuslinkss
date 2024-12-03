<?php
session_start();
include 'partials/_dbconnect.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Clear any previous output
ob_clean();

// Verify user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];

// Check if the friend parameter exists
$friend = isset($_GET['friend']) ? $_GET['friend'] : null;

if (!$friend) {
    echo json_encode(['error' => 'Friend username is required']);
    exit;
}

try {
    // Fetch unread notifications
    $sql = "SELECT COUNT(*) AS unread_count 
            FROM notifications 
            WHERE receiver = ? 
              AND sender = ? 
              AND seen = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $friend);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['unread_count' => (int)$row['unread_count']]);
    } else {
        echo json_encode(['unread_count' => 0]);
    }
    exit;
} catch (Exception $e) {
    // Handle errors
    error_log('Error fetching notifications: ' . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch notifications']);
    exit;
}
?>
