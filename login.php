<?php
// Start session
session_start();

// Establish database connection
$conn = new mysqli('localhost', 'root', 'root', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL statement to retrieve user information including email
    $sql = "SELECT id, username, password, email FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, fetch data
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password matches, set session variables
                $_SESSION['user_id'] = $row['id'];  // Set user ID in session
                $_SESSION['username'] = $row['username'];  // Set username in session
                $_SESSION['user_email'] = $row['email'];  // Set email in session

                // Redirect to home page
                header("Location: home.php");
                exit;
            } else {
                // Password does not match
                $login_err = "Invalid username or password.";
            }
        } else {
            // User not found
            $login_err = "User not found.";
        }
        $stmt->close();
    }
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
