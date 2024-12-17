<?php
// Start session
session_start();

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare SQL statement to retrieve user information
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found, check password
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Password matches, set session variables
        $_SESSION['user_id'] = $row['id']; // Set user ID in session
        $_SESSION['username'] = $row['username']; // Set username in session

        // Redirect to home page
        header("Location: home.php");
        exit;
    } else {
        // Password does not match
        echo "Invalid username or password.";
    }
} else {
    // User not found
    echo "User not found.";
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <?php if (!empty($login_err)) { ?>
        <p><?php echo $login_err; ?></p>
    <?php } ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
</head>
<body>
    <script>
        // JavaScript redirect after 3 seconds
        setTimeout(function() {
            window.location.href = '/cloud';
        }, 3000);
    </script>
</body>
</html>