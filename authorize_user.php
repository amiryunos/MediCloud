<?php
require_once "phpqrcode/qrlib.php"; // Ensure this path is correct
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '/etc/PHPMailer/src/Exception.php';
require '/etc/PHPMailer/src/PHPMailer.php';
require '/etc/PHPMailer/src/SMTP.php';

// Connect to your MySQL database
$mysqli = new mysqli("localhost", "admin", "admin", "secure_file_storage");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get user ID from URL
$user_id = $_GET['id'];

// Fetch user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $mysqli->query($user_query);
$user_data = $user_result->fetch_assoc();

$email = $user_data['email'];
$first_name = $user_data['first_name'];
$last_name = $user_data['last_name'];

// Function to send authorization email
function sendAuthorizationEmail($email, $first_name, $last_name) {
    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'wanzayn1611@gmail.com'; // Replace with your SMTP username
        $mail->Password = 'nddtfpwlyqvedylj'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('wanzayn1611@gmail.com', 'Secure File Storage');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Account is Authorized';
        $mail->Body = "Hello $first_name $last_name,<br><br>Your account has been authorized. You can now access the secure file storage system.<br><br>Best regards,<br>Admin";

        $mail->send();
        echo 'Authorization email sent successfully.';
    } catch (Exception $e) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

// Send authorization email
sendAuthorizationEmail($email, $first_name, $last_name);

// Update user status to authorized
$update_query = "UPDATE users SET is_authorized = TRUE WHERE id = $user_id";
$mysqli->query($update_query);

// Close MySQL connection
$mysqli->close();

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
