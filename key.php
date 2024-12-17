<?php
session_start();

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if login data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $secretKey = $_POST['secret_key'];

    // Prepare and execute the login check
    $sql = "SELECT id, username FROM users WHERE username = ? AND secret_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $secretKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Login successful
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];  // Store user id in session

        // Retrieve AES keys for the user
        $sql_keys = "SELECT aes_key FROM aes_keys WHERE user_id = ?";
        $stmt_keys = $conn->prepare($sql_keys);
        $stmt_keys->bind_param("i", $user['id']);
        $stmt_keys->execute();
        $result_keys = $stmt_keys->get_result();

        echo "Welcome, " . $username . "<br>";
        echo "Your AES Keys:<br>";
        while ($key = $result_keys->fetch_assoc()) {
            echo "AES Key: " . $key['aes_key'] . "<br>";
        }
    } else {
        echo "Invalid username or secret key.";
    }
}

// Close the connection
$conn->close();
?>

<!-- HTML form for user login -->
<form method="post" action="key.php">
    Secret Key: <input type="text" name="secret_key"><br>
    <input type="submit" value="Login">
</form>
