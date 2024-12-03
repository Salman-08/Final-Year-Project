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

// Handle assignment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_assignment"])) {
    // Process the submitted assignment
    $assignment_id = $_POST["assignment_id"];
    $user_id = $_SESSION["user_id"]; // Assuming you have stored the user_id in the session
    
    // Check if the student has already submitted the assignment
    $check_sql = "SELECT * FROM submissions WHERE assignment_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $assignment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission_error = "Assignment already submitted.";
    } else {
        // File upload handling
        $target_dir = "uploads/"; // Specify the directory where files will be stored
        $file_name = $_FILES["submission"]["name"];
        $target_file = $target_dir . basename($file_name);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is a valid PDF or DOCX
        if ($fileType != "pdf" && $fileType != "docx") {
            $submission_error = "Sorry, only PDF and DOCX files are allowed.";
            $uploadOk = 0;
        }

        // Check if file was uploaded successfully
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["submission"]["tmp_name"], $target_file)) {
                // Insert the submission into the database
                $file_path = $target_file;
                $sql = "INSERT INTO submissions (submission_name, file_name, file_path, assignment_id, user_id) 
                        VALUES ('$file_name', '$file_name', '$file_path', '$assignment_id', '$user_id')";
                if ($conn->query($sql) === TRUE) {
                    // Submission successful
                    $submission_message = "Assignment submitted successfully.";
                } else {
                    // Submission failed
                    $submission_error = "Error submitting assignment: " . $conn->error;
                }
            } else {
                // File upload failed
                $submission_error = "Sorry, there was an error uploading your file.";
            }
        }
    }
    $stmt->close();
}

// Retrieve assignments from the database
$sql = "SELECT * FROM assignments";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
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
                <li class="nav-item active"><a href="view_assignment.php" class="nav-link">Assignment</a></li>
                <li class="nav-item"><a href="s_grades.php" class="nav-link">Grades</a></li>
                <li class="nav-item"><a href="s_profile.php" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- END nav -->
<section>
    <div class="hero-wrap" style="background-image: url('images/bg_10.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <h1 class="mb-4 bread">Submit Your Assignment</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Assignment section -->
<section class="ftco-section bg-light">
    <div class="container">
        <!-- Display submission message or error -->
        <?php if (isset($submission_message)) : ?>
            <div class="alert alert-success"><?php echo $submission_message; ?></div>
        <?php elseif (isset($submission_error)) : ?>
            <div class="alert alert-danger"><?php echo $submission_error; ?></div>
        <?php endif; ?>
        
        <!-- Display assignments -->
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Assignment Name</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <?php
                            // Ensure assignment_name and due_date fields are set
                            $assignment_name = isset($row["assignment_name"]) ? $row["assignment_name"] : "N/A";
                            $due_date = isset($row["due_date"]) ? $row["due_date"] : "N/A";
                            $current_date = date("Y-m-d");
                            $is_due = strtotime($due_date) >= strtotime($current_date);
                            ?>
                            <tr>
                                <td><?php echo $assignment_name; ?></td>
                                <td><?php echo $due_date; ?></td>
                                <td>
                                    <a href="<?php echo $row["file_path"]; ?>" class="btn btn-primary" download>Download Assignment</a>
                                    <button type="button" class="btn btn-primary" onclick="openSubmissionModal(<?php echo $row['assignment_id']; ?>)" <?php echo $is_due ? '' : 'disabled'; ?>>
                                        Submit Assignment
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3">No assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Submission modal -->
<div id="submissionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <!-- Submission form goes here -->
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" id="assignmentId" name="assignment_id">
            <div class="form-group">
                <label for="submission">Submission (PDF or DOCX)</label>
                <input type="file" name="submission" id="submission" accept=".pdf,.docx" required>
            </div>
            <button type="submit" name="submit_assignment" class="btn btn-primary">Submit</button>
        </form>
        <!-- Submission message or error goes here -->
    </div>
</div>

<footer class="ftco-footer ftco-bg-dark ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <p>Automating the art of Assessment</p>
            </div>
        </div>
    </div>
</footer>
<script>
    // Function to open submission modal
    function openSubmissionModal(assignmentId) {
        document.getElementById("assignmentId").value = assignmentId;
        document.getElementById("submissionModal").style.display = "block";
    }

    // Function to close submission modal
    function closeModal() {
        document.getElementById("submissionModal").style.display = "none";
    }

    // Function to download assignment
    function downloadAssignment(assignmentFile) {
        alert("Downloading: " + assignmentFile);
    }
</script>
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
