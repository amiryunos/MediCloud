<?php
session_start(); // Start the session at the beginning of the script

// Debugging: Display session data
// echo "<pre>Session: ";
// // print_r($_SESSION);
// echo "</pre>";

// Existing database connection code
$mysqli = new mysqli("localhost", "admin", "admin", "secure_file_storage");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT username, email FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            
        } else {
            echo "No user found with that ID.";
        }
        $stmt->close();
    } else {
        echo "Error in SQL statement execution.";
    }
} else {
    echo "User ID not set in session.";
    $username = "Guest";
    $email = ""; // Default username if not logged in
}

// Query to fetch user data
$user_query = "SELECT * FROM users";
$user_result = $mysqli->query($user_query);

// Query to fetch file data with uploader's information
$file_query = "SELECT files.id, files.filename, files.description, files.user_id, users.username
               FROM files
               INNER JOIN users ON files.user_id = users.id";
$file_result = $mysqli->query($file_query);

unset($_SESSION["lastGeneratedAESKey"]);
// Close MySQL connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        .logo{
            height: 80px; /* Adjust the size as needed */
            margin-bottom: 20px; /* Space between the logo and the tabs */
        }
        /* Your styling for the main page goes here */
        body {
            font-family: Arial, sans-serif;
            background: url(hero-bg.png) no-repeat;
            background-size: cover; /* Cover the entire viewport */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2 {
            color: #333;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .button {
            margin: 10px;
            padding: 10px;
            background-color: #4c8baf;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #4c8baf;
        }
    </style>
</head>
<body>
    <img src="uitmlogo.png" alt="UiTM Logo" class="logo">
    <h2>Welcome to the AES cloud <?php echo htmlspecialchars($username); ?>!</h2>
    

    <div class="buttons">

        <form action="profile.php">
            <button type="submit" class="button">Profile</button>
        </form>

        <form action="qrcode.php">
            <button type="submit" class="button">Encrypt File</button>
        </form>

        <form action="view.php">
            <button type="submit" class="button">View Files</button>
        </form>

        <!-- Logout Button -->
        <form action="logout.php" method="post">
            <button type="submit" class="button">Logout</button>
        </form>

    </div>
    
</body>
</html>
