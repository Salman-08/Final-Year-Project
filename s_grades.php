<?php
// Start session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "aae";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION["username"]) || !isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "Student") {
    // If not logged in or not a student, redirect to the login page
    header("Location: login.php");
    exit();
}

// Get the logged-in student's ID
$user_id = $_SESSION["user_id"];

// Retrieve grades from the database
$sql = "
    SELECT grades.grade_id, grades.submission_id, grades.user_id, grades.grade, grades.similarity_score, grades.report_path,
           submissions.submission_name, assignments.assignment_name, users.fullname
    FROM grades
    INNER JOIN submissions ON grades.submission_id = submissions.submission_id
    INNER JOIN assignments ON submissions.assignment_id = assignments.assignment_id
    INNER JOIN users ON grades.user_id = users.id
    WHERE grades.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Grades</title>
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
                <li class="nav-item"><a href="view_assignment.php" class="nav-link">Assignment</a></li>
                <li class="nav-item active"><a href="s_grades.php" class="nav-link">Grades</a></li>
                <li class="nav-item"><a href="s_profile.php" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- END nav -->
<section>
    <div class="hero-wrap" style="background-image: url('images/bg_8.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <h1 class="mb-4 bread">My Grades</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Grades section -->
<section class="ftco-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Your Grades</h2>
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Assignment Name</th>
                            <th>Grade</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grades)) : ?>
                            <?php foreach ($grades as $grade) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade["fullname"]); ?></td>
                                    <td><?php echo htmlspecialchars($grade["assignment_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($grade["grade"]); ?></td>
                                    <td>
                                        <?php if (!empty($grade["report_path"])) : ?>
                                            <a href="download_report.php?path=<?php echo urlencode($grade['report_path']); ?>" class="btn btn-primary">Download Report</a>
                                        <?php else : ?>
                                            No report available
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">No grades found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

</body>
</html>
