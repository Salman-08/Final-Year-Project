<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "aae";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["username"]) || !isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "Student") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

function sanitizeInput($input) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

function validateFullName($fullname) {
    return preg_match("/^[a-zA-Z\s]*$/", $fullname);
}

function validateEmail($email) {
    return preg_match("/^\w+([.\-+]?\w+)*@\w+([.\-]?\w+)*(\.\w{2,3})+$/", $email);
}

function validateUsername($username) {
    return preg_match("/^(?=.*[a-zA-Z])(?=.*[0-9_])[a-zA-Z0-9_]+$/", $username);
}

function validatePassword($password) {
    return strlen($password) >= 8 && preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/", $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $fullname = sanitizeInput($_POST["fullname"]);
    $email = sanitizeInput($_POST["email"]);
    $username = sanitizeInput($_POST["username"]);

    if (!validateFullName($fullname)) {
        $profile_error = "Invalid full name. Only letters and spaces are allowed.";
    } elseif (!validateEmail($email)) {
        $profile_error = "Invalid email format. Only Pakistani educational institution emails are allowed.";
    } elseif (!validateUsername($username)) {
        $profile_error = "Username must contain letters and either numbers or underscores.";
    } else {
        $sql = "UPDATE users SET fullname = ?, email = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fullname, $email, $username, $user_id);
        if ($stmt->execute()) {
            $profile_message = "Profile updated successfully.";
        } else {
            $profile_error = "Error updating profile: " . $conn->error;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $current_password = sanitizeInput($_POST["current_password"]);
    $new_password = sanitizeInput($_POST["new_password"]);
    $confirm_password = sanitizeInput($_POST["confirm_password"]);

    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($current_password, $user["password"])) {
        if ($new_password === $confirm_password) {
            if (validatePassword($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                if ($stmt->execute()) {
                    $password_message = "Password changed successfully.";
                } else {
                    $password_error = "Error changing password: " . $conn->error;
                }
            } else {
                $password_error = "New password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, and one number.";
            }
        } else {
            $password_error = "New password and confirm password do not match.";
        }
    } else {
        $password_error = "Current password is incorrect.";
    }
}

$sql = "SELECT fullname, email, username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Profile</title>
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
        .hero-wrap {
            width: 100%;
            height: 500px;
            position: relative;
        }
        .invalid-feedback {
            display: none;
            color: red;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="student.php">AAE</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a href="student.php" class="nav-link">Home</a></li>
                <li class="nav-item active"><a href="s_profile.php" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- END nav -->
<section>
    <div class="hero-wrap" style="background-image: url('images/bg_11.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <h1 class="mb-4 bread">My Profile</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Profile section -->
<section class="ftco-section bg-light">
    <div class="container">
        <!-- Display profile message or error -->
        <?php if (isset($profile_message)) : ?>
            <div class="alert alert-success"><?php echo $profile_message; ?></div>
        <?php elseif (isset($profile_error)) : ?>
            <div class="alert alert-danger"><?php echo $profile_error; ?></div>
        <?php endif; ?>
        <!-- Display password message or error -->
        <?php if (isset($password_message)) : ?>
            <div class="alert alert-success"><?php echo $password_message; ?></div>
        <?php elseif (isset($password_error)) : ?>
            <div class="alert alert-danger"><?php echo $password_error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <h2>Profile Information</h2>
                <form method="post" action="" onsubmit="return validateProfileForm()">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        <div id="fullnameError" class="invalid-feedback">Full name must not contain numbers</div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <div id="emailError" class="invalid-feedback">Please enter a valid email address</div>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        <div id="usernameError" class="invalid-feedback">Username must contain letters and either numbers or underscores</div>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="update_profile" value="Update Profile" class="btn btn-primary py-3 px-5">
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <h2>Change Password</h2>
                <form method="post" action="" onsubmit="return validatePasswordForm()">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                        <div id="currentPasswordError" class="invalid-feedback">Please enter your current password</div>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                        <div id="newPasswordError" class="invalid-feedback">Password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, and one number</div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <div id="confirmPasswordError" class="invalid-feedback">Passwords do not match</div>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="change_password" value="Change Password" class="btn btn-primary py-3 px-5">
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="ftco-footer ftco-bg-dark ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <p>Automating the art of Assessment</p>
            </div>
        </div>
    </div>
</footer>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/aos.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/jquery.timepicker.min.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="js/main.js"></script>
<script>
function validateProfileForm() {
    let isValid = true;

    const fullnameInput = document.getElementById("fullname");
    const emailInput = document.getElementById("email");
    const usernameInput = document.getElementById("username");

    const fullnameError = document.getElementById("fullnameError");
    const emailError = document.getElementById("emailError");
    const usernameError = document.getElementById("usernameError");

    if (!/^[a-zA-Z\s]*$/.test(fullnameInput.value)) {
        fullnameInput.classList.add("is-invalid");
        fullnameError.style.display = 'block';
        isValid = false;
    } else {
        fullnameInput.classList.remove("is-invalid");
        fullnameError.style.display = 'none';
    }

    if (!/^\w+([-+.']\w+)*@\w+([-.]\w+)*(\.\w+)+$/.test(emailInput.value)) {
        emailInput.classList.add("is-invalid");
        emailError.style.display = 'block';
        isValid = false;
    } else {
        emailInput.classList.remove("is-invalid");
        emailError.style.display = 'none';
    }

    if (!/^(?=.*[a-zA-Z])(?=.*[0-9_])[a-zA-Z0-9_]+$/.test(usernameInput.value)) {
        usernameInput.classList.add("is-invalid");
        usernameError.style.display = 'block';
        isValid = false;
    } else {
        usernameInput.classList.remove("is-invalid");
        usernameError.style.display = 'none';
    }

    return isValid;
}

function validatePasswordForm() {
    let isValid = true;

    const currentPasswordInput = document.getElementById("current_password");
    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById("confirm_password");

    const currentPasswordError = document.getElementById("currentPasswordError");
    const newPasswordError = document.getElementById("newPasswordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");

    if (currentPasswordInput.value === "") {
        currentPasswordInput.classList.add("is-invalid");
        currentPasswordError.style.display = 'block';
        isValid = false;
    } else {
        currentPasswordInput.classList.remove("is-invalid");
        currentPasswordError.style.display = 'none';
    }

    if (!/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(newPasswordInput.value)) {
        newPasswordInput.classList.add("is-invalid");
        newPasswordError.style.display = 'block';
        isValid = false;
    } else {
        newPasswordInput.classList.remove("is-invalid");
        newPasswordError.style.display = 'none';
    }

    if (confirmPasswordInput.value !== newPasswordInput.value) {
        confirmPasswordInput.classList.add("is-invalid");
        confirmPasswordError.style.display = 'block';
        isValid = false;
    } else {
        confirmPasswordInput.classList.remove("is-invalid");
        confirmPasswordError.style.display = 'none';
    }

    return isValid;
}

$(document).ready(function() {
    $("#fullname").on("blur", function() {
        const fullnameInput = $(this);
        const fullnameError = $("#fullnameError");

        if (!/^[a-zA-Z\s]*$/.test(fullnameInput.val())) {
            fullnameInput.addClass("is-invalid");
            fullnameError.show();
        } else {
            fullnameInput.removeClass("is-invalid");
            fullnameError.hide();
        }
    });

    $("#email").on("blur", function() {
        const emailInput = $(this);
        const emailError = $("#emailError");

        if (!/^\w+([-+.']\w+)*@\w+([-.]\w+)*(\.\w+)+$/.test(emailInput.val())) {
            emailInput.addClass("is-invalid");
            emailError.show();
        } else {
            emailInput.removeClass("is-invalid");
            emailError.hide();
        }
    });

    $("#username").on("blur", function() {
        const usernameInput = $(this);
        const usernameError = $("#usernameError");

        if (!/^(?=.*[a-zA-Z])(?=.*[0-9_])[a-zA-Z0-9_]+$/.test(usernameInput.val())) {
            usernameInput.addClass("is-invalid");
            usernameError.show();
        } else {
            usernameInput.removeClass("is-invalid");
            usernameError.hide();
        }
    });

    $("#current_password").on("blur", function() {
        const currentPasswordInput = $(this);
        const currentPasswordError = $("#currentPasswordError");

        if (currentPasswordInput.val() === "") {
            currentPasswordInput.addClass("is-invalid");
            currentPasswordError.show();
        } else {
            currentPasswordInput.removeClass("is-invalid");
            currentPasswordError.hide();
        }
    });

    $("#new_password").on("blur", function() {
        const newPasswordInput = $(this);
        const newPasswordError = $("#newPasswordError");

        if (!/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(newPasswordInput.val())) {
            newPasswordInput.addClass("is-invalid");
            newPasswordError.show();
        } else {
            newPasswordInput.removeClass("is-invalid");
            newPasswordError.hide();
        }
    });

    $("#confirm_password").on("blur", function() {
        const confirmPasswordInput = $(this);
        const confirmPasswordError = $("#confirmPasswordError");
        const newPasswordInput = $("#new_password");

        if (confirmPasswordInput.val() !== newPasswordInput.val()) {
            confirmPasswordInput.addClass("is-invalid");
            confirmPasswordError.show();
        } else {
            confirmPasswordInput.removeClass("is-invalid");
            confirmPasswordError.hide();
        }
    });
});
</script>
</body>
</html>
