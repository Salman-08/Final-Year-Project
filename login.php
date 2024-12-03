<?php
// Start session
session_start();

// Database configuration
$servername = "localhost"; // Change this if your MySQL server is running on a different host
$username = "root"; // Change this if your MySQL username is different
$password = ""; // Change this if your MySQL password is different
$database = "aae"; // Change this to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user inputs
function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

// Function to verify login credentials
function verifyLogin($username, $password) {
    global $conn;
    
    // Sanitize inputs
    $username = sanitizeInput($username);
    $password = sanitizeInput($password);

    // Prepare and execute SQL statement to retrieve user data
    $stmt = $conn->prepare("SELECT id, password, user_type FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify password and return user type
    if ($user && password_verify($password, $user["password"])) {
        return array("user_id" => $user["id"], "user_type" => $user["user_type"]);
    } else {
        return false;
    }
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Verify login credentials
    $userData = verifyLogin($username, $password);

    if ($userData) {
        // Authentication successful, set user_id session variable
        $_SESSION["user_id"] = $userData["user_id"];
        $_SESSION["username"] = $username;
        $_SESSION["user_type"] = $userData["user_type"];
        if ($userData["user_type"] == "Teacher") {
            header("Location: T_dash.php"); // Redirect to teacher dashboard
        } else {
            header("Location: student.php"); // Redirect to student dashboard
        }
        exit();
    } else {
        // Authentication failed, redirect to login page with error message
        $_SESSION["login_error"] = "Invalid username or password";
        header("Location: login.php");
        exit();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Login</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i" rel="stylesheet">
<link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/owl.carousel.min.css">
<link rel="stylesheet" href="css/owl.theme.default.min.css">
<link rel="stylesheet" href="css/magnific-popup.css">
<link rel="stylesheet" href="css/aos.css">
<link rel="stylesheet" href="css/ionicons.min.css">
<link rel="stylesheet" href="css/bootstrap-datepicker.css">
<link rel="stylesheet" href="css/jquery.timepicker.css">
<link rel="stylesheet" href="css/flaticon.css">
<link rel="stylesheet" href="css/icomoon.css">
<link rel="stylesheet" href="css/style.css">
<style>
    body {
        background-image: url('images/bg_12.jpg'); /* Replace with the path to your background image */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .card {
        background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
        border: none;
    }
    .card-header {
        background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent background */
        color: #fff;
        border-bottom: none;
    }
    .forgot {
        text-align: right;
    }
</style>
</head>
<body>
<br>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Login</div>
                <div class="card-body">
                    <!-- Display registration success message -->
                    <?php
                    if (isset($_SESSION["registration_success"])) {
                        echo '<div class="alert alert-success">' . $_SESSION["registration_success"] . '</div>';
                        unset($_SESSION["registration_success"]);
                    }

                    // Display login error message
                    if (isset($_SESSION["login_error"])) {
                        echo '<div class="alert alert-danger mt-3">' . $_SESSION["login_error"] . '</div>';
                        unset($_SESSION["login_error"]);
                    }
                    ?>
                    <form method="POST" action="login.php">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="forgot">
                            <a href="forgot_password.php">Forgot Password?</a>
                        </div>
                        <!-- Hidden input field for userType -->
                        <input type="hidden" name="userType" value="Teacher">
                        <button type="submit" class="btn btn-primary register-btn" name="login">Login</button>
                        <div class="mt-3">
                            Don't have an Account? Register <a href="register.php">Here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
