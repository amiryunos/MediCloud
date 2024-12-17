<?php
// Start session to retrieve user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement to retrieve files associated with the logged-in user
$sql = "SELECT * FROM files WHERE user_id = $user_id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Files</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url(hero-bg.png) no-repeat;
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

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4c8baf;
            color: white;
        }

        a {
            text-decoration: none;
            color: #4c8baf;
            margin-top: 10px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<img src="uitmlogo.png" alt="UiTM Logo" class="logo">
    <style>
        .logo{
            height: 80px; /* Adjust the size as needed */
            margin-bottom: 20px; /* Space between the logo and the tabs */
        }
    </style>
    <h2>View Files</h2>

    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['filename'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                    echo "<td><a href='decrypt2.php?file=" . $row['id'] . "'>View</a> | <a href='download2.php?file=" . $row['id'] . "'>Download</a> | <a href='delete2.php?file=" . $row['id'] . "'>Delete</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No files found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <a href="qrcode.php">Back to Upload</a>
    
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
