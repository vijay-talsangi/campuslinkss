<?php
// Include PHPMailer files
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

$showalert = false;
$showerror = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'partials/_dbconnect.php';

    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];

    // Validate email domain
    if (substr($email, -14) !== "@mitwpu.edu.in") {
        $showerror = "Email must end with '@mitwpu.edu.in'.";
    } elseif (empty($username)) {
        $showerror = "Username is required.";
    } else {
        // Check if email or username already exists
        $sql_check = "SELECT * FROM `users` WHERE `email` = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        $sql_check_un = "SELECT * FROM `users` WHERE `username` = ?";
        $stmt_check_un = $conn->prepare($sql_check_un);
        $stmt_check_un->bind_param("s", $username);
        $stmt_check_un->execute();
        $result_check_un = $stmt_check_un->get_result();

        if ($result_check->num_rows > 0) {
            $showerror = "Email already exists.";
        } elseif ($result_check_un->num_rows > 0) {
            $showerror = "Username already exists.";
        } elseif ($password != $cpassword) {
            $showerror = "Passwords do not match.";
        } else {
            // Hash password and generate hashed verification code
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = bin2hex(random_bytes(16)); // Random string
            $hashed_code = password_hash($verification_code, PASSWORD_DEFAULT);

            // Insert user with verification code
            $sql = "INSERT INTO `users` (`email`, `username`, `password`, `verification_code`, `is_verified`, `dt`) VALUES (?, ?, ?, ?, 0, current_timestamp())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $email, $username, $hash, $hashed_code);
            $result = $stmt->execute();

            if ($result) {
                // Send verification email using PHPMailer
                $verification_link = "http://localhost/campuslinkss/verify_email.php?email=" . urlencode($email) . "&code=" . urlencode($verification_code);

                // Initialize PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // SMTP server
                    $mail->SMTPAuth = true;

                    
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587; // SMTP port for TLS

                    // Recipients
                    
                    $mail->addAddress($email); // Recipient's email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Email Address';
                    $mail->Body    = "Hello $username,<br><br>
                                      Please click the link below to verify your email address and complete your registration:<br>
                                      <a href='$verification_link'>Verify Email</a><br><br>
                                      If you did not request this, please ignore this email.<br><br>
                                      Best regards,<br>Your App Team";

                    $mail->send();
                    $showalert = true;
                } catch (Exception $e) {
                    $showerror = "Error sending email: {$mail->ErrorInfo}";
                }
            } else {
                $showerror = "Error in registration. Please try again.";
            }
        }
    }
}
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SignUp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
  <?php include 'partials/_nav.php';?>
  <?php if($showalert): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Success!</strong> Your account is now created, please verify your account using mail sent to you.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <?php if($showerror): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Error!</strong> <?= $showerror ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

    <div class="container" style="margin-top: 10vh; width: 65%; box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19); border-radius: 20px;">
      <h1 class="text-center">SignUp</h1>
      <form action="/campuslinkss/signup.php" method="post" style="display: flex; flex-direction: column; align-items: center;">
      <div class="mb-3 col-md-8">
        <label for="email" class="form-label">Email address</label>
        <input type="email" autofocus autocomplete="off" placeholder="@mitwpu.edu.in" class="form-control" id="email" name="email" aria-describedby="emailHelp">
      </div>
      <div class="mb-3 col-md-8">
        <label for="username" class="form-label">User Name</label>
        <input type="text" autocomplete="off" placeholder="You will be called with this name." class="form-control" id="username" name="username" required aria-describedby="emailHelp">
      </div>
      <div class="mb-3 col-md-8">
        <label for="password" class="form-label">Make a Password</label>
        <input type="password" class="form-control" id="password" name="password">
        <div id="emailHelp" class="form-text">Password will be encrypted, We take care of your security</div>
      </div>
      <div class="mb-3 col-md-8">
        <label for="cpassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="cpassword" name="cpassword">
        <div id="emailHelp" class="form-text">Type same password as above.</div>
      </div>
      <p>Already registered? <a href="/campuslinkss/login.php" style="text-decoration-color: rgb(91, 206, 91); color: rgb(91, 206, 91);">Login</a></p>
      <button type="submit" class="btn btn-primary mb-2">Signup</button>

    </form>
  </div>
  <script>
    function validateForm() {
      const username = document.getElementById('username').value;
      if (!username.endsWith('@mitwpu.edu.in')) {
        alert("Username must end with '@mitwpu.edu.in'");
        return false;
      }
      return true;
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
