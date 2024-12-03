<?php
session_start();

// Check if the user is logged in and is either a student or a teacher
if (!isset($_SESSION["username"]) || !isset($_SESSION["user_type"]) || ($_SESSION["user_type"] !== "Student" && $_SESSION["user_type"] !== "Teacher")) {
    // If not logged in or not a student/teacher, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the report path is provided
if (!isset($_GET['path'])) {
    die("No report specified.");
}

// Get the report path
$report_path = urldecode($_GET['path']);

// Sanitize the path to prevent directory traversal attacks
$report_path = basename($report_path);

// Set the full path to the report
$full_path = __DIR__ . '/reports/' . $report_path;

// Check if the file exists
if (!file_exists($full_path)) {
    die("Report not found.");
}

// Send the file to the browser for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
header('Content-Length: ' . filesize($full_path));
readfile($full_path);
exit();
?>
