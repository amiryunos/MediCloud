<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return an error response if the user is not logged in
    http_response_code(403);
    exit;
}

function validateKey() {
    // Establish database connection
    $conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

    // Check connection
    if ($conn->connect_error) {
        // Return an error response if unable to connect to the database
        http_response_code(500);
        exit;
    }

    // Collect form data
    $deletionKey = $_POST['DecryptionKey'];
    $userId = $_SESSION['user_id'];

    // Prepare SQL statement to retrieve user's AES key
    $sql = "SELECT aes_key FROM users WHERE id = ?";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, check AES key
        $row = $result->fetch_assoc();
        $userAESKey = $row['aes_key'];

        // Verify if the provided encryption key matches the user's AES key
        if ($deletionKey === $userAESKey) {
            // Return a success response if the key is valid
            echo "valid";
            return TRUE;
        } else {
            // Return an error response if the key is invalid
            http_response_code(400);
            return FALSE;
        }
    } else {
        // Return an error response if the user is not found
        http_response_code(404);
        echo "user_not_found";
        return FALSE;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}

// Check if the encryption key is provided via POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['DecryptionKey'])) {
    validateKey();
} else {
    // Return an error response if the encryption key is not provided or the request method is not POST
    http_response_code(400);
    echo "missing_encryption_key";
}
?>
