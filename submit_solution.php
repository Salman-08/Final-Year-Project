<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if file was uploaded without errors
    if (isset($_FILES["solution_file"]) && $_FILES["solution_file"]["error"] == 0) {
        // Retrieve form data
        $solution_name = $_POST["solution_name"];
        $assignment_id = $_POST["assignment_id"]; // Get the assignment ID from the form

        // File details
        $file_name = $_FILES["solution_file"]["name"];
        $file_tmp = $_FILES["solution_file"]["tmp_name"];
        
        // Define upload directory
        $upload_dir = "solutions/";
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move uploaded file to desired directory
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Database connection
            $conn = new mysqli("localhost", "root", "", "aae");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Insert solution details into the database
            $stmt = $conn->prepare("INSERT INTO solutions (solution_name, file_path, assignment_id, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $solution_name, $file_path, $assignment_id, $user_id);
            
            if ($stmt->execute()) {
                // Set success message
                $_SESSION["submission_message"] = "Solution submitted successfully.";
            } else {
                // Set error message
                $_SESSION["submission_error"] = "Error: " . $stmt->error;
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        } else {
            // Set file upload error message
            $_SESSION["submission_error"] = "Error uploading file.";
        }
    } else {
        // Set error message for file upload
        $_SESSION["submission_error"] = "Error uploading file.";
    }

    // Redirect back to the submit_assignment.php page
    header("Location: submit_assignment.php");
    exit();
}
?>
