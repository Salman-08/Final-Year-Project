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

// Retrieve assignment IDs associated with the logged-in teacher
$sql_assignments = "SELECT assignment_id FROM assignments WHERE user_id = ?";
$stmt_assignments = $conn->prepare($sql_assignments);
$stmt_assignments->bind_param("i", $_SESSION["user_id"]);
$stmt_assignments->execute();
$result_assignments = $stmt_assignments->get_result();
$assignment_ids = [];
if ($result_assignments->num_rows > 0) {
    while ($row = $result_assignments->fetch_assoc()) {
        $assignment_ids[] = $row['assignment_id'];
    }
}
$stmt_assignments->close();

// If no assignments found, set a flag
$noAssignments = empty($assignment_ids);

// Initialize an empty array for submissions
$submissions = [];

if (!$noAssignments) {
    // Convert the assignment IDs array to a comma-separated string for SQL query
    $assignment_ids_str = implode(',', $assignment_ids);

    // Retrieve submissions for the specific assignment IDs
    $sql_submissions = "
        SELECT s.*, (SELECT COUNT(*) FROM grades g WHERE g.submission_id = s.submission_id) as evaluated
        FROM submissions s
        WHERE s.assignment_id IN ($assignment_ids_str)";
    $result_submissions = $conn->query($sql_submissions);
    if ($result_submissions->num_rows > 0) {
        while ($row = $result_submissions->fetch_assoc()) {
            $submissions[] = $row;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Submissions</title>
    <meta charset="UTF-8">
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
        .custom-checkbox {
            position: relative;
            display: block;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 22px;
            user-select: none;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #eee;
            border-radius: 5px;
        }

        .custom-checkbox:hover input ~ .checkmark {
            background-color: #ccc;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background-color: #2196F3;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
            left: 9px;
            top: 5px;
            width: 6px;
            height: 12px;
            border: solid white;
            border-width: 0 3px 3px 0;
            transform: rotate(45deg);
        }

        .pagination-button {
            margin: 0 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .pagination-button.active {
            background-color: #2196F3;
            color: white;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #2196F3;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
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
                <li class="nav-item"><a href="submit_assignment.php" class="nav-link">Submit Assignment</a></li>
                <li class="nav-item active"><a href="check_submissions.php" class="nav-link">Check Submissions</a></li>
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
                        <h1 class="mb-3">View Submissions</h1>
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
                    <h2 class="mb-3">Submissions</h2>
                </div>
                <button id="evaluate-button" class="btn btn-primary">Evaluate Selected Submissions</button>
                <div id="error-message" class="mt-3" style="color: red;"></div>
                <div id="processing-message" class="mt-3" style="color: blue;"></div>
                <div id="grades-container" class="mt-5"></div>
                <div id="pagination-container"></div>

                <div class="table-responsive">
                    <table id="submissions-table" class="table">
                        <thead>
                            <tr>
                                <th scope='col'><input type="checkbox" id="select-all"></th>
                                <th scope='col'>Submission ID</th>
                                <th scope='col'>Submission File</th>
                                <th scope='col'>Submitted At</th>
                                <th scope='col'>Action</th>
                            </tr>
                        </thead>
                        <tbody id="submissions-tbody">
                            <!-- Data will be loaded here dynamically -->
                        </tbody>
                    </table>
                </div>
                <div id="pagination-controls"></div>
                
            </div>
        </div>
    </div>
</section>

<br>
<footer class="ftco-footer ftco-bg-dark ftco-section">
    <div class="row">
        <div class="col-md-12 text-center">
            <p>Automating the art of Assessment</p>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    const entriesPerPage = 20;
    const submissions = <?php echo json_encode($submissions); ?>;
    const noAssignments = <?php echo json_encode($noAssignments); ?>;
    let showAll = false;

    function displayPage(page) {
        let start, end;
        if (showAll) {
            start = 0;
            end = submissions.length;
        } else {
            start = (page - 1) * entriesPerPage;
            end = start + entriesPerPage;
        }

        const paginatedSubmissions = submissions.slice(start, end);

        $("#submissions-tbody").empty();
        if (noAssignments) {
            $("#submissions-tbody").append(`<tr><td colspan="5" class="text-center">No assignments found.</td></tr>`);
        } else if (paginatedSubmissions.length === 0) {
            $("#submissions-tbody").append(`<tr><td colspan="5" class="text-center">No submissions found.</td></tr>`);
        } else {
            paginatedSubmissions.forEach(submission => {
                $("#submissions-tbody").append(`
                    <tr>
                        <td><label class="custom-checkbox"><input type="checkbox" class="submission-checkbox" data-id="${submission.submission_id}"><span class="checkmark"></span></label></td>
                        <td>${submission.submission_id}</td>
                        <td>${submission.submission_name}</td>
                        <td>${submission.submitted_at}</td>
                        <td><a href="${submission.file_path}" download class="btn btn-primary">Download</a></td>
                    </tr>
                `);
            });
        }

        $("#pagination-controls").empty();
        if (!showAll) {
            for (let i = 1; i <= Math.ceil(submissions.length / entriesPerPage); i++) {
                $("#pagination-controls").append(`<button class="pagination-button ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`);
            }
            $("#pagination-controls").append(`<button class="show-all-button btn btn-primary">Show All</button>`);
        } else {
            $("#pagination-controls").append(`<button class="show-all-button btn btn-primary">Show Paginated</button>`);
        }
    }

    $(document).on("click", ".pagination-button", function() {
        currentPage = $(this).data("page");
        showAll = false;
        displayPage(currentPage);
    });

    $(document).on("click", ".show-all-button", function() {
        showAll = !showAll;
        displayPage(currentPage);
    });

    $("#select-all").click(function() {
        $(".submission-checkbox").prop('checked', $(this).prop('checked'));
    });

    $("#evaluate-button").click(function() {
        const selectedIds = [];
        $(".submission-checkbox:checked").each(function() {
            selectedIds.push($(this).data("id"));
        });

        if (selectedIds.length === 0) {
            $("#error-message").text("No submissions selected for evaluation.");
            return;
        }

        evaluateSubmissions(selectedIds);
    });

    function evaluateSubmissions(submissionIds) {
        $("#processing-message").html('<div class="spinner"></div> Your request is Processing...');
        $.ajax({
            url: 'http://localhost:5000/evaluate',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ submission_ids: submissionIds }),
            success: function(data) {
                $("#processing-message").empty();
                displayGrades(data.results, data.already_evaluated_messages);
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                console.error("Response:", xhr.responseText || xhr.statusText);
                $("#processing-message").empty();
                $("#error-message").text("Error evaluating submissions. Please try again later.");
            }
        });
    }

    function displayGrades(grades, alreadyEvaluatedMessages) {
    let gradesContainer = $("#grades-container");
    gradesContainer.empty();
    $("#error-message").empty();

    alreadyEvaluatedMessages.forEach(message => {
        gradesContainer.append(`<p>${message}</p>`);
    });

    if (grades.error) {
        gradesContainer.append(`<p>${grades.error}</p>`);
    } else {
        for (let submissionId in grades) {
            let result = grades[submissionId];
            let grade = result.grade !== undefined ? result.grade : 'N/A';
            let similarityScore = result.similarity_score !== undefined ? result.similarity_score.toFixed(2) : 'N/A';

            if (result.message) {
                gradesContainer.append(`<p>Submission ${submissionId}: ${result.message}</p>`);
            } else {
                if (grade === 'N/A' && similarityScore === 'N/A') {
                    gradesContainer.append(`<p>Submission ${submissionId} has already been graded.</p>`);
                } else {
                    gradesContainer.append(`<p>Submission ${submissionId}: Grade - ${grade}, Similarity Score - ${similarityScore}</p>`);
                }
            }
        }
    }
}

    displayPage(currentPage);
});
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
