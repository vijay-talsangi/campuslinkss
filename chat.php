<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'partials/_dbconnect.php';

$username = $_SESSION['username'];
$friend = htmlspecialchars($_GET['user']);

// Ensure a valid chat session
if (!$friend) {
    echo "Invalid friend.";
    exit;
}

// Fetch chat history
$sql = "SELECT sender, receiver, message, timestamp 
        FROM messages 
        WHERE (sender = ? AND receiver = ?)
           OR (sender = ? AND receiver = ?)
        ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $friend, $friend, $username);
$stmt->execute();
$messages = $stmt->get_result();

// Mark notifications as "seen" when the user opens the chat
$update_notification_sql = "UPDATE notifications SET seen = 1 
                             WHERE receiver = ? AND sender = ? AND seen = 0";
$update_stmt = $conn->prepare($update_notification_sql);
$update_stmt->bind_param("ss", $username, $friend);
$update_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <title>Chat with <?php echo $friend; ?></title>
    <style>
        .chat-container {
            max-width: 600px;
            margin: auto;
            margin-top: 50px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .chat-header {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            background-color: #ffffff;
        }
        .chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex: 1;
            border: none;
            padding: 10px;
        }
        .chat-input button {
            border: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include 'partials/_lnav.php';?>
<div class="chat-container">
    <div class="chat-header">
        Chat with <?php echo $friend; ?>
        <button id="delete-chat" class="btn btn-danger btn-sm float-end">Clear Chat</button>
    </div>
    <div id="chat-messages" class="chat-messages">
        <!-- Messages will be loaded here -->
    </div>
    <div class="chat-input">
        <input type="text" id="message" placeholder="Type a message">
        <button id="send-button">Send</button>
    </div>
    
</div>

<script>
    $(document).ready(function () {
        const chatMessages = $("#chat-messages");

        // Function to load chat messages
        function loadMessages() {
            $.ajax({
                url: "fetch_messages.php",
                type: "GET",
                data: { friend: "<?php echo $friend; ?>" },
                success: function (data) {
                    chatMessages.html(data);
                    chatMessages.scrollTop(chatMessages.prop("scrollHeight")); // Scroll to bottom
                }
            });
        }

        // Load messages on page load
        loadMessages();

        // Send message
        $("#send-button").click(function () {
            const message = $("#message").val();
            if (message.trim() !== "") {
                $.ajax({
                    url: "send_message.php",
                    type: "POST",
                    data: {
                        sender: "<?php echo $username; ?>",
                        receiver: "<?php echo $friend; ?>",
                        message: message
                    },
                    success: function () {
                        $("#message").val("");
                        loadMessages();
                    }
                });

                // After sending a message, insert a notification
                $.ajax({
                    url: "send_notification.php",
                    type: "POST",
                    data: {
                        sender: "<?php echo $username; ?>",
                        receiver: "<?php echo $friend; ?>",
                    }
                });
            }
        });

        // Auto-refresh messages every 2 seconds
        setInterval(loadMessages, 2000);

        $("#delete-chat").click(function () {
        if (confirm("Are you sure you want to delete this chat?")) {
            $.ajax({
                url: "delete_chat.php",
                type: "POST",
                data: { friend: "<?php echo $friend; ?>" },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        alert("Chat deleted successfully!");
                        loadMessages(); // Reload the chat (it should be empty)
                    } else {
                        alert("Failed to delete chat: " + data.error);
                    }
                }
            });
        }
    });
    });
</script>
</body>
</html>
