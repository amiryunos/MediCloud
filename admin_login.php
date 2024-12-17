<?php
session_start();

// Check if the admin is already logged in, if yes, redirect them to the admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = $_POST['admin_username'];
    $admin_password = $_POST['admin_password'];

    // Define the correct admin username and password
    $correct_admin_username = 'admin';
    $correct_admin_password = 'superadmin';

    // Check if the entered credentials match the correct ones
    if ($admin_username === $correct_admin_username && $admin_password === $correct_admin_password) {
        // Admin authenticated, start session
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $login_err = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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

