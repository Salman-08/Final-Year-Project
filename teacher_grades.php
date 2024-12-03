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

// Check if the user is logged in and is a teacher
if (!isset($_SESSION["username"]) || !isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "Teacher") {
    // If not logged in or not a teacher, redirect to the login page
    header("Location: login.php");
    exit();
}

// Update grade if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["grade_id"]) && isset($_POST["new_grade"])) {
    $grade_id = $_POST["grade_id"];
    $new_grade = $_POST["new_grade"];

    $update_sql = "UPDATE grades SET grade = ? WHERE grade_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_grade, $grade_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to the same page to display updated grades
    header("Location: teacher_grades.php");
    exit();
}

// Retrieve assignment IDs associated with the logged-in teacher
$sql_assignments = "SELECT assignment_id FROM assignments WHERE user_id = ?";
$stmt_assignments = $conn->prepare($sql_assignments);
$stmt_assignments->bind_param("i", $_SESSION["user_id"]);
$stmt_assignments->execute();
$result_assignments = $stmt_assignments->get_result();
$assignment_ids = [];
if ($result_assignments->num_rows > 0) {
    while($row = $result_assignments->fetch_assoc()) {
        $assignment_ids[] = $row['assignment_id'];
    }
}
$stmt_assignments->close();

// If no assignments found, set an empty array to avoid SQL errors
if (empty($assignment_ids)) {
    $assignment_ids[] = 0;
}

// Convert the assignment IDs array to a comma-separated string for SQL query
$assignment_ids_str = implode(',', $assignment_ids);

// Retrieve grades for the specific assignment IDs
$sql_grades = "
    SELECT grades.grade_id, grades.submission_id, grades.user_id, grades.grade, grades.similarity_score, grades.report_path,
           submissions.submission_name, assignments.assignment_name, users.fullname
    FROM grades
    INNER JOIN submissions ON grades.submission_id = submissions.submission_id
    INNER JOIN assignments ON submissions.assignment_id = assignments.assignment_id
    INNER JOIN users ON grades.user_id = users.id
    WHERE assignments.assignment_id IN ($assignment_ids_str)
";
$result_grades = $conn->query($sql_grades);
$grades = $result_grades->fetch_all(MYSQLI_ASSOC);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Teacher Grade View</title>
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
        <a class="navbar-brand" href="teacher.php">AAE</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a href="T_dash.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="submit_assignment.php" class="nav-link">Submit Assignment</a></li>
                <li class="nav-item"><a href="check_submissions.php" class="nav-link">Check Submissions</a></li>
                <li class="nav-item active"><a href="teacher_grades.php" class="nav-link">Grades</a></li>
                <li class="nav-item"><a href="teacher_profile.php" class="nav-link">Profile</a></li>
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
                        <h1 class="mb-4 bread">Manage Grades</h1>
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
                <h2>All Grades</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Assignment Name</th>
                                <th>Grade</th>
                                <th>Similarity Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($grades)) : ?>
                                <?php foreach ($grades as $grade) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade["fullname"]); ?></td>
                                        <td><?php echo htmlspecialchars($grade["assignment_name"]); ?></td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="number" name="new_grade" value="<?php echo htmlspecialchars($grade["grade"]); ?>" required>
                                                <input type="hidden" name="grade_id" value="<?php echo $grade["grade_id"]; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($grade["similarity_score"]); ?>%</td>
                                        <td>
                                                <button type="submit" class="btn btn-primary">Update Grade</button>
                                            </form>
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
                                    <td colspan="5">No grades found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button onclick="exportGrades()" class="btn btn-primary">Export Grades to PDF</button>
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

<!-- Export to PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
<script>
    function exportGrades() {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF();
        doc.text("Grade Report", 10, 10);
        doc.autoTable({
            head: [['Student Name', 'Assignment Name', 'Grade', 'Similarity Score']],
            body: [
                <?php foreach ($grades as $grade) : ?>
                    ['<?php echo htmlspecialchars($grade["fullname"]); ?>', '<?php echo htmlspecialchars($grade["assignment_name"]); ?>', '<?php echo htmlspecialchars($grade["grade"]); ?>', '<?php echo htmlspecialchars($grade["similarity_score"]); ?>%'],
                <?php endforeach; ?>
            ]
        });
        doc.save("grades.pdf");
    }
</script>
</body>
</html>
