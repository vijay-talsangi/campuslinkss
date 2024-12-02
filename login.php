<?php
$showalert = false;
$showerror = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'partials/_dbconnect.php';
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            if ($row['is_first_login']) {
                header("Location: interests.php");
            } else {
                header("Location: welcome.php");
            }
            exit;
        } else {
            $showerror = "Invalid Password";
        }
    } else {
        $showerror = "Invalid Username";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <title>Login-in</title>
</head>
<body>
<?php include 'partials/_nav.php';?>
    <?php if($showerror): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= $showerror ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="container" style="margin-top: 10vh; width: 65%; box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19); border-radius: 20px;">
        <h1 class="text-center">Login</h1>
        <p class="text-center">Not Registered yet? <a href="/campuslinkss/signup" style="text-decoration-color: rgb(91, 206, 91); color: rgb(91, 206, 91);">SignUp</a></p>
        <form action="/campuslinkss/login.php" method="post" style="display: flex; flex-direction: column; align-items: center;">
            <div class="mb-3 col-md-8">
              <label for="exampleInputEmail1" class="form-label">Username</label>
              <input type="text" name="username" autofocus autocomplete="off" placeholder="Enter your username" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
              
            </div>
            <div class="mb-3 col-md-8">
              <label for="exampleInputPassword1" class="form-label">Password</label>
              <input type="password" name="password" autocomplete="off" placeholder="Enter your password" class="form-control" id="exampleInputPassword1">
            </div>
            <!-- <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="exampleCheck1">
              <label class="form-check-label" for="exampleCheck1">Check me out</label>
            </div> -->
            <button type="submit" class="btn btn-success mb-4">Submit</button>
          </form>
    </div>
   
</body>
</html>