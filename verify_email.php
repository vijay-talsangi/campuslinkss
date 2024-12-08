<?php
include 'partials/_dbconnect.php';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    // Retrieve the hashed code from the database
    $sql = "SELECT `verification_code` FROM `users` WHERE `email` = ? AND `is_verified` = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $hashed_code = $user['verification_code'];

        // Verify the code
        if (password_verify($code, $hashed_code)) {
            // Mark email as verified
            $sql_update = "UPDATE `users` SET `is_verified` = 1, `verification_code` = NULL WHERE `email` = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $email);
            $stmt_update->execute();

            echo "<script>alert('Email verified successfully! You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Invalid or expired verification link.'); window.location.href='signup.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired verification link.'); window.location.href='signup.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='signup.php';</script>";
}
?>
