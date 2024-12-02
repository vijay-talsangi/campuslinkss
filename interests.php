<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Select Interests</title>
</head>
<body>
<?php include 'partials/_lnav.php';?>
    <div style="width: 50%;" class="container text-center border shadow-sm p-3 mb-5 bg-body-tertiary rounded position-absolute top-50 start-50 translate-middle">
        <h1 class="mt-2 text-center">Select Your Interests</h1>
        <form action="store_interests.php" method="post">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="Technology" id="tech">
                            <label class="form-check-label" for="tech">Technology</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="Sports" id="sports">
                            <label class="form-check-label" for="sports">Sports</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="Music" id="music">
                            <label class="form-check-label" for="music">Music</label>
                        </div>
                    </div>                  
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="Art" id="art">
                            <label class="form-check-label" for="art">Art</label>
                        </div>
                    </div>  
                    <div class="col">  
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="Dance" id="dance">
                            <label class="form-check-label" for="dance">Dance</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interests[]" value="C" id="C">
                            <label class="form-check-label" for="C">C</label>
                        </div>
                    </div>            
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </form>
    </div>
</body>
</html>
