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

// Function to verify if token exists and retrieve associated email
function getEmailFromToken($token) {
    global $conn;
    
    // Sanitize token
    $token = sanitizeInput($token);

    // Prepare and execute SQL statement to retrieve email associated with the token
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["email"];
    } else {
        return false;
    }
}

// Handle password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_password"])) {
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    $token = $_POST["token"];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $_SESSION["reset_password_error"] = "Passwords do not match.";
        header("Location: reset_password.php?token=$token");
        exit();
    }

    // Retrieve email associated with the token
    $email = getEmailFromToken($token);

    if ($email) {
        // Sanitize password and generate hash
        $password = sanitizeInput($password);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update user's password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Redirect to login page with success message
        header("Location: login.php");
        $_SESSION["reset_password_message"] = "Password reset successful. You can now login with your new password.";

        exit();
    } else {
        // Token not found or expired, redirect with error message
        $_SESSION["reset_password_error"] = "Invalid or expired reset token.";
        header("Location: reset_password.php");
        exit();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
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
        <div class="row">
            <div class="col-md-8 col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">
                        <form method="POST" action="reset_password.php" onsubmit="return validateForm()">
                            <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="passwordError" class="invalid-feedback"></div>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary register-btn" name="reset_password">Reset Password</button>
                            <div class="mt-3">
                                <a href="login.php">Back to Login</a>
                            </div>
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
    <script>
        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            // Validate password strength
            if (password.length < 8 || !/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(password)) {
                document.getElementById("password").classList.add("is-invalid");
                document.getElementById("passwordError").textContent = "Password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, and one number";
                return false;
            }

            // Validate if password and confirm password match
            if (password !== confirmPassword) {
                document.getElementById("confirmPassword").classList.add("is-invalid");
                document.getElementById("passwordError").textContent = "Passwords do not match";
                return false;
            }

            return true; // Form is valid
        }
    </script>

</body>
</html>
