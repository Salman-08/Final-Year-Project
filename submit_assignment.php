<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all fields are filled
    if (isset($_POST["assignment_name"]) && isset($_FILES["assignment_file"]) && isset($_POST["due_date"]) && isset($_POST["initial_grade"])) {
        // Assign form data to variables
        $assignment_name = $_POST["assignment_name"];
        $file_name = $_FILES["assignment_file"]["name"];
        $file_tmp = $_FILES["assignment_file"]["tmp_name"];
        $due_date = $_POST["due_date"];
        $initial_grade = $_POST["initial_grade"]; // New field for initial grade

        // Validate file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array("pdf", "docx");
        if (in_array($file_ext, $allowed_extensions)) {
            // Move uploaded file to desired directory
            $upload_dir = "assignments/";
            $file_path = $upload_dir . $file_name;
            move_uploaded_file($file_tmp, $file_path);

            // Connect to the database
            $conn = mysqli_connect("localhost", "root", "", "aae");
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Get the user_id from the session
            $user_id = $_SESSION["user_id"];

            // Insert assignment details into the database
            $sql = "INSERT INTO assignments (user_id, assignment_name, file_name, file_path, due_date, initial_grade) VALUES ($user_id, '$assignment_name', '$file_name', '$file_path', '$due_date', '$initial_grade')";
            if (mysqli_query($conn, $sql)) {
                // Set success message
                $_SESSION["submission_message"] = "Assignment created successfully.";
            } else {
                // Set error message for database insertion
                $_SESSION["submission_error"] = "Error submitting assignment: " . $conn->error;
            }
            
            // Close database connection
            mysqli_close($conn);
        } else {
            // Set error message for file format
            $_SESSION["submission_error"] = "Only PDF or DOCX files are allowed";
        }
    } else {
        // Set error message for missing fields
        $_SESSION["submission_error"] = "All fields are required!";
    }

    // Redirect back to the same page to display the message
    header("Location: submit_assignment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Assignment</title>
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
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
    <a class="navbar-brand" href="T_dash.php">AAE</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
        <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a href="T_dash.php" class="nav-link">Home</a></li>
                <li class="nav-item active"><a href="submit_assignment.php" class="nav-link">Submit Assignment</a></li>
                <li class="nav-item"><a href="check_submissions.php" class="nav-link">Check Submissions</a></li>
                <li class="nav-item"><a href="teacher_grades.php" class="nav-link">Grades</a></li>
                <li class="nav-item"><a href="teacher_profile.php" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="home-slider owl-carousel">
      <div class="slider-item" style="background-image:url(images/bg_3.jpg);">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-12 ftco-animate text-center">
          	<div class="text mb-5 pb-3">
	            <h1 class="mb-3">Automated Assignment Evaluation </h1>
            </div>
          </div>
        </div>
        </div>
        </div>
</section>

<section class="ftco-section ftc-no-pb ftc-no-pt">
    <div class="container">
            <div class="row">
            <div class="col-md-12 ftco-animate text-center">
                <div class="text mb-5 pb-3">
                    <h1 class="mb-3">Submit Assignment</h1>
                    <p>Please upload your assignment in PDF or DOCX format.</p>
                </div>
                <div>
                <!-- Display submission message or error -->
                <?php if (isset($_SESSION["submission_message"])) : ?>
                    <div class="alert alert-success"><?php echo $_SESSION["submission_message"]; ?></div>
                    <?php unset($_SESSION["submission_message"]); ?>
                <?php elseif (isset($_SESSION["submission_error"])) : ?>
                    <div class="alert alert-danger"><?php echo $_SESSION["submission_error"]; ?></div>
                    <?php unset($_SESSION["submission_error"]); ?>
                <?php endif; ?>
                </div>
                <button onclick="showAssignmentCreationForm()" class="btn btn-primary">Create Assignment</button>
                <button onclick="showSolutionUploadForm()" class="btn btn-primary">Upload Solution</button>
                <br><br>
                <button onclick="window.location.href = 'check_submissions.php';" class="btn btn-primary">Check Submissions</button>

            </div>
        </div>
    </div>
</section>

<div id="assignmentCreationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAssignmentCreationForm()">&times;</span>
        <form action="submit_assignment.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="assignment_name">Assignment Name:</label>
                <input type="text" name="assignment_name" id="assignment_name" required>
            </div>
            <div class="form-group">
                <label for="assignment_file">Select Assignment File:</label>
                <input type="file" name="assignment_file" id="assignment_file" accept=".pdf, .docx" required>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date and Time:</label>
                <input type="datetime-local" name="due_date" id="due_date" required>
            </div>
            <div class="form-group">
                <label for="initial_grade">Grade:</label>
                <input type="number" name="initial_grade" id="initial_grade" min="0" max="10" required>
            </div>
            <br>
            <input type="submit" value="Create Assignment" class="btn btn-primary">
            <br>
        </form>
    </div>
</div>

<div id="solutionUploadModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSolutionUploadForm()">&times;</span>
        <?php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];

// Database connection
$conn = new mysqli("localhost", "root", "", "aae");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch assignments for the logged-in user
$result = $conn->query("SELECT assignment_id, assignment_name FROM assignments WHERE user_id = $user_id");

?>

<form action="submit_solution.php" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="solution_name">Solution Name:</label>
        <input type="text" name="solution_name" id="solution_name" required>
    </div>
    <div class="form-group">
        <label for="assignment_id">Select Assignment:</label>
        <select name="assignment_id" id="assignment_id" required>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['assignment_id']}'>{$row['assignment_name']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="solution_file">Select Solution File:</label>
        <input type="file" name="solution_file" id="solution_file" accept=".pdf, .docx" required>
    </div>
    <br>
    <input type="submit" value="Upload Solution" class="btn btn-primary">
    <br>
</form>

<?php
$conn->close();
?>

        
    </div>
</div>

<script>
    var assignmentModal = document.getElementById("assignmentCreationModal");
    var solutionModal = document.getElementById("solutionUploadModal");

    function showAssignmentCreationForm() {
        assignmentModal.style.display = "block";
    }

    function closeAssignmentCreationForm() {
        assignmentModal.style.display = "none";
    }

    function showSolutionUploadForm() {
        solutionModal.style.display = "block";
    }

    function closeSolutionUploadForm() {
        solutionModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == assignmentModal) {
            closeAssignmentCreationForm();
        }
        if (event.target == solutionModal) {
            closeSolutionUploadForm();
        }
    }
</script>


<br>
<footer class="ftco-footer ftco-bg-dark ftco-section">
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
<script src="js/google-map.js"></script>
<script src="js/main.js"></script>

</body>
</html>
