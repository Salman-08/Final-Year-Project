<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "aae";

// Establishing connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input function
function sanitizeInput($input) {
    global $conn;
    $input = htmlspecialchars(trim($input));
    return $conn->real_escape_string($input);
}

// Function to validate email format
function isValidEmail($email) {
    return preg_match('/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/', $email);
}

// Function to check if email exists
function isEmailExists($email) {
    global $conn;
    $email = sanitizeInput($email);
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["count"] > 0;
}

// Function to check if username exists
function isUsernameExists($username) {
    global $conn;
    $username = sanitizeInput($username);
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["count"] > 0;
}

// Registration process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $fullname = sanitizeInput($_POST["fullname"]);
    $email = sanitizeInput($_POST["email"]);
    $username = sanitizeInput($_POST["username"]);
    $password = sanitizeInput($_POST["password"]);
    $userType = sanitizeInput($_POST["userType"]);

    if (!isValidEmail($email)) {
        $_SESSION["registration_error"] = "Invalid email format";
    } elseif (isEmailExists($email)) {
        $_SESSION["registration_error"] = "Email already registered";
    } elseif (isUsernameExists($username)) {
        $_SESSION["registration_error"] = "Username already exists";
    } else {
        // Hashing password
        $password = password_hash($password, PASSWORD_DEFAULT);
        
        // Inserting user into database
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $username, $password, $userType);
        
        try {
            if ($stmt->execute()) {
                $_SESSION["registration_success"] = "Registration successful. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION["registration_error"] = "Registration failed. Please try again later.";
            }
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() == 1062) { // Error code for duplicate entry
                $_SESSION["registration_error"] = "Email or username already exists. Please choose different ones.";
            } else {
                $_SESSION["registration_error"] = "Registration failed. Please try again later.";
            }
        }
    }
}

// Closing connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registration</title>
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
    .container {
        margin: 10px;
    }
    .invalid-feedback {
        display: none;
    }
    .is-invalid + .invalid-feedback {
        display: block;
    }
    </style>
</head>
<body>
    <br>
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header">Registration</div>
                    <div class="card-body">
                        <form id="registrationForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo isset($_POST["fullname"]) ? $_POST["fullname"] : ""; ?>" required>
                                <div id="fullnameError" class="invalid-feedback">Full name must not contain numbers</div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div id="emailError" class="invalid-feedback">Please enter a valid email address in the format of name@example.com</div>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div id="usernameError" class="invalid-feedback">Username must contain letters and either numbers or underscores</div>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="passwordError" class="invalid-feedback">Password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, and one number</div>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                <div id="confirmPasswordError" class="invalid-feedback">Passwords do not match</div>
                            </div>
                            <div class="form-group">
                                <label for="userType">Register As:</label>
                                <select class="form-control" id="userType" name="userType" required>
                                    <option value="Teacher">Teacher</option>
                                    <option value="Student">Student</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary register-btn" name="register">Register</button>
                            <div class="mt-3">
                                Already have an account? <a href="login.php">Login</a>
                            </div>
                        </form>
                        <?php
                        if (isset($_SESSION["registration_success"])) {
                            echo '<div id="successMessage" class="alert alert-success mt-3">' . $_SESSION["registration_success"] . '</div>';
                            unset($_SESSION["registration_success"]);
                        }

                        if (isset($_SESSION["registration_error"])) {
                            echo '<div class="alert alert-danger mt-3">' . $_SESSION["registration_error"] . '</div>';
                            unset($_SESSION["registration_error"]);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const fullnameInput = document.getElementById("fullname");
            const emailInput = document.getElementById("email");
            const usernameInput = document.getElementById("username");
            const passwordInput = document.getElementById("password");
            const confirmPasswordInput = document.getElementById("confirmPassword");

            fullnameInput.addEventListener("input", () => {
                if (!/^[a-zA-Z\s]*$/.test(fullnameInput.value)) {
                    fullnameInput.classList.add("is-invalid");
                } else {
                    fullnameInput.classList.remove("is-invalid");
                }
            });

            emailInput.addEventListener("input", () => {
                if (!/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/.test(emailInput.value)) {
                    emailInput.classList.add("is-invalid");
                } else {
                    emailInput.classList.remove("is-invalid");
                }
            });

            usernameInput.addEventListener("input", () => {
                if (!/^(?=.*[a-zA-Z])(?=.*[0-9_])[a-zA-Z0-9_]+$/.test(usernameInput.value)) {
                    usernameInput.classList.add("is-invalid");
                } else {
                    usernameInput.classList.remove("is-invalid");
                }
            });

            passwordInput.addEventListener("input", () => {
                if (passwordInput.value.length < 8 || !/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(passwordInput.value)) {
                    passwordInput.classList.add("is-invalid");
                } else {
                    passwordInput.classList.remove("is-invalid");
                }
            });

            confirmPasswordInput.addEventListener("input", () => {
                if (confirmPasswordInput.value !== passwordInput.value) {
                    confirmPasswordInput.classList.add("is-invalid");
                } else {
                    confirmPasswordInput.classList.remove("is-invalid");
                }
            });
        });

        function validateForm() {
            const fullnameInput = document.getElementById("fullname");
            const emailInput = document.getElementById("email");
            const usernameInput = document.getElementById("username");
            const passwordInput = document.getElementById("password");
            const confirmPasswordInput = document.getElementById("confirmPassword");

            let isValid = true;

            if (!/^[a-zA-Z\s]*$/.test(fullnameInput.value)) {
                fullnameInput.classList.add("is-invalid");
                isValid = false;
            }

            if (!/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/.test(emailInput.value)) {
                emailInput.classList.add("is-invalid");
                isValid = false;
            }

            if (!/^(?=.*[a-zA-Z])(?=.*[0-9_])[a-zA-Z0-9_]+$/.test(usernameInput.value)) {
                usernameInput.classList.add("is-invalid");
                isValid = false;
            }

            if (passwordInput.value.length < 8 || !/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(passwordInput.value)) {
                passwordInput.classList.add("is-invalid");
                isValid = false;
            }

            if (confirmPasswordInput.value !== passwordInput.value) {
                confirmPasswordInput.classList.add("is-invalid");
                isValid = false;
            }

            return isValid;
        }
    </script>
</body>
</html>
