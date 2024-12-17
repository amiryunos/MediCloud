<?php
// Connect to the database
$mysqli = new mysqli("localhost", "admin", "admin", "secure_file_storage");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the ID parameter is set
if (isset($_GET['id'])) {
    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $id);

        // Set parameters
        $id = $_GET['id'];

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Redirect to the admin dashboard after successful deletion
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Error deleting record: " . $mysqli->error;
        }
    }

    // Close statement
    $stmt->close();
}

// Close connection
$mysqli->close();
?>
