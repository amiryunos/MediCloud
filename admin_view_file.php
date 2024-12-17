<?php
// Start session to retrieve admin login status
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to admin login page if not logged in
    header("Location: admin_login.php");
    exit();
}

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the filename from the URL parameter
$filename = $_GET['file'];

// Query to retrieve file details
$sql = "SELECT * FROM files WHERE filename = '$filename'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // File found, fetch its details
    $row = $result->fetch_assoc();
    $fileDescription = $row['description'];
    $filePath = $row['filepath'];

    // Check if the file exists
    if (file_exists($filePath)) {
        // Set the appropriate content type for the file
        $contentType = mime_content_type($filePath);
        header("Content-type: $contentType");
        header("Content-Disposition: inline; filename=$filename");

        // Output the file content
        readfile($filePath);
    } else {
        // File not found, display an error message
        echo 'File not found.';
    }
} else {
    // File not found or unauthorized access, display an error message
    echo 'File not found or unauthorized access.';
}

// Close the connection
$conn->close();
?>
