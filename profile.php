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
    $query = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $email = $row['email'];
            $phone_number = $row['phone_number'];
            $is_authorized = $row['is_authorized'];
        } else {
            echo "No user found with that ID.";
        }
        $stmt->close();
    } else {
        echo "Error in SQL statement execution.";
    }
} else {
    echo "User ID not set in session.";
    $username = "Guest"; // Default username if not logged in
}

// Query to fetch user data
$user_query = "SELECT * FROM users";
$user_result = $mysqli->query($user_query);

// Query to fetch file data with uploader's information
$file_query = "SELECT files.id, files.filename, files.description, files.user_id, users.username
               FROM files
               INNER JOIN users ON files.user_id = users.id";
$file_result = $mysqli->query($file_query);


// Close MySQL connection
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css" integrity="sha256-2XFplPlrFClt0bIdPgpz8H7ojnk10H69xRqd9+uTShA=" crossorigin="anonymous" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"/>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"/>

<head>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
.logo{
            height: 80px; /* Adjust the size as needed */
            margin-bottom: 20px; /* Space between the logo and the tabs */
    }
    .logo-container {
    display: flex;
    justify-content: center;
    width: 100%; /* Mengambil lebar penuh dari container untuk memusatkan logo */
    margin: 20px 0; /* Menambahkan margin atas dan bawah untuk logo */
}


body{
    font-family: 'Poppins', sans-serif;
    background: url(hero-bg.png) no-repeat;
}

.wrapper{
    padding: 30px 50px;
    border: 1px solid #ddd;
    border-radius: 15px;
    margin: 10px auto;
    max-width: 600px;
}
h4{
    letter-spacing: -1px;
    font-weight: 400;
}
.img{
    width: 70px;
    height: 70px;
    border-radius: 6px;
    object-fit: cover;
}
#img-section p,#deactivate p{
    font-size: 12px;
    color: #777;
    margin-bottom: 10px;
    text-align: justify;
}
#img-section b,#img-section button,#deactivate b{
    font-size: 14px; 
}

label{
    margin-bottom: 0;
    font-size: 14px;
    font-weight: 500;
    color: #777;
    padding-left: 3px;
}

.form-control{
    border-radius: 10px;
}

input[placeholder]{
    font-weight: 500;
}
.form-control:focus{
    box-shadow: none;
    border: 1.5px solid #0779e4;
}
select{
    display: block;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 10px;
    height: 40px;
    padding: 5px 10px;
    /* -webkit-appearance: none; */
}

select:focus{
    outline: none;
}
.button{
    background-color: #fff;
    color: #0779e4;
}
.button:hover{
    background-color: #0779e4;
    color: #fff;
}
.btn-primary{
    background-color: #0779e4;
}
.danger{
    background-color: #fff;
    color: #e20404;
    border: 1px solid #ddd;
}
.danger:hover{
    background-color: #e20404;
    color: #fff;
}
@media(max-width:576px){
    .wrapper{
        padding: 25px 20px;
    }
    #deactivate{
        line-height: 18px;
    }
}
    </style>
</head>
<div class="wrapper bg-white mt-sm-5">
    <h4 class="pb-4 border-bottom">Account settings</h4>
    <form action="update.php" method="POST">
        <div class="d-flex align-items-start py-3 border-bottom">
            <img src="cloud.gif" class="img" alt="">
            <div class="pl-sm-4 pl-2" id="img-section">
                <b>Profile Photo</b>
                <p><?= htmlspecialchars($username); ?></p>
            </div>
        </div>
        <div class="py-2">
            <div class="row py-2">
                <div class="col-md-6">
                    <label for="firstname">First Name</label>
                    <input type="text" class="bg-light form-control" name="firstname" placeholder="First Name" value="<?= htmlspecialchars($first_name); ?>">
                </div>
                <div class="col-md-6 pt-md-0 pt-3">
                    <label for="lastname">Last Name</label>
                    <input type="text" class="bg-light form-control" name="lastname" placeholder="Last Name" value="<?= htmlspecialchars($last_name); ?>">
                </div>
            </div>
            <div class="row py-2">
                <div class="col-md-6">
                    <label for="email">Email Address</label>
                    <input type="email" class="bg-light form-control" name="email" placeholder="Email" value="<?= htmlspecialchars($email); ?>">
                </div>
                <div class="col-md-6 pt-md-0 pt-3">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="bg-light form-control" name="phone" placeholder="601+" value="<?= htmlspecialchars($phone_number); ?>">
                </div>
            </div>
            <div class="row py-2">
                <div class="col-md-6">
                    <label for="status">Authorization Status</label>
                    <input type="text" class="bg-light form-control" name="status" value="<?= $is_authorized ? 'Authorized' : 'Not Authorized'; ?>" readonly>
                </div>
            </div>
        </div>
        <div class="py-3 pb-4 border-bottom">
            <button type="submit" class="btn btn-primary mr-3">Save Changes</button>
            <!-- <button type="button" class="btn border button">Cancel</button> -->
            <a href="home.php" class="btn border button">Cancel</a>
        </div>
    </form>
    <form action="change.php" method="POST">
    <div class="form-group">
        <label for="current_password">Current Password:</label>
        <input type="password" class="form-control" name="current_password" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password:</label>
        <input type="password" class="form-control" name="new_password" required>
    </div>
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>
</div>
<div class="logo-container">
    <img src="uitmlogo.png" alt="UiTM Logo" class="logo">
    <?php
        session_start();
        if (isset($_SESSION['notification'])) {
            echo '<div class="alert alert-info">' . $_SESSION['notification'] . '</div>';
            unset($_SESSION['notification']);  // Clear the message after displaying it
        }
    ?>
</div>
</body>
</html>