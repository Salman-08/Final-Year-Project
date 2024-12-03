<?php
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

// Function to generate a random password reset token
function generateResetToken() {
    return bin2hex(random_bytes(16)); // Generates a 32-character hexadecimal string
}

// Handle forgot password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_password"])) {
    $email = $_POST["email"];

    // Sanitize email
    $email = sanitizeInput($email);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, generate password reset token
        $reset_token = generateResetToken();

        // Update the user's record in the database with the reset token
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $reset_token, $email);
        $stmt->execute();

        // Send password reset link to the user's email
        $reset_link = "http://localhost/Final_Year_Project/reset_password.php?token=" . $reset_token; // Change the URL to your password reset page
        $subject = "Password Reset Link";
        $message = "Dear user,\n\nPlease click on the following link to reset your password:\n$reset_link\n\nBest regards,\nYour Application Team";
        // Set the sender name as "Reset Password"
        $headers = "From: Reset Password <sa1975182@gmail.com>\r\n";
        $headers .= "Reply-To: sa1975182@gmail.com\r\n";
        // Send email
        mail($email, $subject, $message, $headers);

        // Redirect back to the form page with a success message
        $_SESSION["reset_password_message"] = "Password reset link sent to your email.";
        header("Location: forgot_password.php");
        exit();
    } else {
        // Email does not exist in the database
        $_SESSION["reset_password_error"] = "Email not found. Please enter a valid email.";
        header("Location: forgot_password.php");
        exit();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Forgot Password</title>
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
                <div class="card-header">Forgot Password</div>
                <div class="card-body">
                    <form method="POST" action="forgot_password.php">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary register-btn" name="reset_password">Reset Password</button>
                        <div class="mt-3">
                                        Back to <a href="login.php">Login</a>
                                    </div>
                        <!-- Display reset password message -->
                        <?php
                        if (isset($_SESSION["reset_password_message"])) {
                            echo '<div class="alert alert-success mt-3">' . $_SESSION["reset_password_message"] . '</div>';
                            unset($_SESSION["reset_password_message"]);
                        }
                        ?>
                        <!-- Display reset password error message -->
                        <?php
                        if (isset($_SESSION["reset_password_error"])) {
                            echo '<div class="alert alert-danger mt-3">' . $_SESSION["reset_password_error"] . '</div>';
                            unset($_SESSION["reset_password_error"]);
                        }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </div>
</body>
</html>
