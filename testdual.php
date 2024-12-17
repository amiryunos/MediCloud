<?php
// Start session
session_start();

// Function to generate a 256-bit AES key
function generateAESKey() {
    $key = openssl_random_pseudo_bytes(32); // 256 bit
    return bin2hex($key); // Convert to hex format for easy handling
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit;
}

// Generate a new AES key for user interaction
$newAESKey = generateAESKey();

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

    // Here, we compare directly to the posted key
    if ($encryptionKey === $newAESKey) {
        // Proceed with encryption logic
        echo "File encrypted successfully.";
        // Delay the redirection for 3 seconds
        sleep(3);
        header("location: upload.html");
        exit;
    } else {
        echo "Invalid encryption key.";
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
    <h2>Encrypt & Upload File using AES</h2>

    <form id="encryptForm" method="post" action="encrypt.php">
        <label for="encryptionKey">Enter Encryption Key:</label>
        <input type="password" id="encryptionKey" name="encryptionKey" required placeholder="Copy and paste your key here">

        <label for="fileToEncrypt">Choose File:</label>
        <input type="file" id="fileToEncrypt" name="fileToEncrypt" required>

        <button type="submit" id="encryptButton">Encrypt</button>
    </form>

    <!-- Display the generated AES key for user to copy -->
    <p><strong>Your AES Key:</strong> <span id="aesKey"><?php echo $newAESKey; ?></span></p>

    <script src="spark-md5.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
