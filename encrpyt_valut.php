<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establish database connection
    $conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $encryptionKey = $_POST['encryptionKey'];
    $userId = $_SESSION['user_id'];

    // Prepare SQL statement to retrieve user's AES key
    $sql = "SELECT aes_key FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userAESKey = $row['aes_key'];

        if ($encryptionKey === $userAESKey) {
            if (isset($_FILES['fileToEncrypt'])) {
                $files = $_FILES['fileToEncrypt'];

                foreach ($files['tmp_name'] as $key => $tmp_name) {
                    if ($files['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $files['name'][$key];
                        $file_tmp = $files['tmp_name'][$key];
                        $upload_dir = 'uploads/' . $userId . '/';

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        // Move uploaded file to the designated directory
                        move_uploaded_file($file_tmp, $upload_dir . $file_name);

                        // Encrypt file logic would go here
                    } else {
                        echo "Error uploading file: " . $files['name'][$key];
                    }
                }
                echo "Files encrypted successfully.";
            } else {
                echo "No files were uploaded.";
            }
        } else {
            echo "Invalid encryption key.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
} 
?>
