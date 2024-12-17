<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $mysqli = new mysqli("localhost", "admin", "admin", "secure_file_storage");
    
    // Periksa sambungan
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $user_id = $_SESSION['user_id'];

    // Fetch the current password hash from the database
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $current_password_hash = $row['password'];

        // Verify the current password
        if (password_verify($current_password, $current_password_hash)) {
            // Current password is correct, proceed to update with new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("si", $new_password_hash, $user_id);
            $update_stmt->execute();

            if ($update_stmt->affected_rows > 0) {
                $_SESSION['notification'] =  "Password updated successfully.";
            } else {
                $_SESSION['notification'] = "Failed to update password.";
            }
            $update_stmt->close();
        } else {
            $_SESSION['notification'] = "Current password is incorrect.";
        }
    } else {
        $_SESSION['notification'] = "Failed to retrieve user.";
    }
    $stmt->close();
    $mysqli->close();
    // Redirect back to the login or home page if not logged in or form is not posted
    header("Location: profile.php");
    exit;
}
?>
