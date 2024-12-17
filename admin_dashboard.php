<?php
// Connect to your MySQL database
$mysqli = new mysqli("localhost", "root", "root", "secure_file_storage");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Query to fetch user data
$user_query = "SELECT * FROM users";
$user_result = $mysqli->query($user_query);

// Query to fetch file data with uploader's information
$file_query = "SELECT files.id, files.filename, files.description, files.user_id, files.created_at, files.file_md5 , users.username
               FROM files
               INNER JOIN users ON files.user_id = users.id";
$file_result = $mysqli->query($file_query);

// Close MySQL connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .logout {
            text-align: right;
            margin-bottom: 20px;
        }

        .logout a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .logout a:hover {
            color: #555;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
        }

        .card table th,
        .card table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .card table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Admin Dashboard</h1>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
        <div class="card">
            <h2>File Data</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Filename</th>
                    <!-- <th>Description</th> -->
                    <th>Uploader</th>
                    <th>Upload Time</th>
                    <th>MD5</th>
                    <th>Action</th>
                </tr>
                <?php
                while ($row = $file_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['filename']."</td>";
                    // echo "<td>".$row['description']."</td>";
                    echo "<td>".$row['username']."</td>"; // Display uploader's username
                    echo "<td>".$row['created_at']."</td>"; // Display upload time
                    echo "<td>".$row['file_md5']."</td>"; // Display upload time
                    // Inside the while loop for file data in the admin dashboard HTML
                    echo "<td><a href='admin_view_file.php?file=" . $row['filename'] . "'>View</a> | <a href='remove_file.php?id=".$row['id']."'>Remove</a></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
        <div class="card">
            <h2>User Data</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone Number</th>
                    <!-- <th>AES Key</th> -->
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                while ($row = $user_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['username']."</td>";
                    echo "<td>".$row['email']."</td>";
                    echo "<td>".$row['first_name']."</td>";
                    echo "<td>".$row['last_name']."</td>";
                    echo "<td>".$row['phone_number']."</td>";
                    // echo "<td>".$row['aes_key']."</td>";
                    // Add a remove button for each user entry
                    $status = $row['is_authorized'] ? 'Authorized' : 'Not Authorized';
                    echo "<td>".$status."</td>";
                    // Menambah butang "Authorize" jika pengguna belum diberi kuasa
                    if (!$row['is_authorized']) {
                        echo "<td><a href='remove_user.php?id=".$row['id']."'>Remove</a> | <a href='authorize_user.php?id=".$row['id']."'>Authorize</a></td>";
                    } else {
                        echo "<td><a href='remove_user.php?id=".$row['id']."'>Remove</a></td>";
                    }
                    echo "</tr>";
                        }
                ?>
            </table>
        </div>
    </div>
    <div class="footer">
        &copy; <?php echo date("Y"); ?> Admin Dashboard
    </div>
</body>
</html>
