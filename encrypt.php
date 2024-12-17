<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
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
    $sql = "SELECT aes_key FROM users WHERE id = $userId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User found, check AES key
        $row = $result->fetch_assoc();
        $userAESKey = $row['aes_key'];

        // Verify if the provided encryption key matches the user's AES key
        if ($encryptionKey === $userAESKey) {
            // Proceed with encryption logic
            // This is where you implement your encryption code using the provided AES key
            // You can access the file to encrypt using $_FILES['fileToEncrypt']

            // For demonstration purposes, let's assume the file is successfully encrypted
            echo "File encrypted successfully.";
            //delay the redirection for 3 seconds
            sleep(3);
            header("location: upload.html");
            exit;
        } else {
            echo "Invalid encryption key.";
        }
    } else {
        echo "User not found.";
    }

    // Close the connection
    $conn->close();
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt File</title>
    <link rel="stylesheet" type="text/css" href="styles1.css">
</head>
<body>
    <h2>Encrypt & Upload File using AES1</h2>

    <form id="encryptForm" method="post" action="encrypt.php">
        <label for="encryptionKey">Enter Encryption Key:</label>
        <input type="password" id="encryptionKey" name="encryptionKey" required>

        <label for="fileToEncrypt">Choose File:</label>
        <input type="file" id="fileToEncrypt" name="fileToEncrypt" required>

        <button type="submit" id="encryptButton">Encrypt</button>
    
    </form>

    <script src="spark-md5.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
