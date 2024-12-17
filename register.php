
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require '/etc/PHPMailer/src/Exception.php';
require '/etc/PHPMailer/src/PHPMailer.php';
require '/etc/PHPMailer/src/SMTP.php';

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$username = $_POST['register_username'];
$password = $_POST['register_password'];
$first_name = $_POST['first_name']; 
$last_name = $_POST['last_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Generate a random AES key (32 bytes for AES-256)
// $aes_key = bin2hex(random_bytes(32)); // Convert binary to hexadecimal
$words = ["bicycle", "roll", "ice", "cream", "sun", "moon", "star"];
$secret_key = generateSecretKey($words, 3); // Generate a secret key with 3 words

function generateSecretKey($words, $numWords) {
    shuffle($words);
    return implode('-', array_slice($words, 0, $numWords));
}

// Prepare SQL statement
$sql = "INSERT INTO users (username, password, first_name, last_name, phone_number, email, aes_key, secret_key) VALUES ('$username', '$hashed_password', '$first_name', '$last_name', '$phone_number' , '$email', '$aes_key', '$secret_key')";

// Execute the SQL statement
if ($conn->query($sql) === TRUE) {
    // Send the AES key to the user's email
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'amirhazmiedu@gmail.com'; // SMTP username
        $mail->Password   = 'Nasilemak483'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('amirhazmi483@gmail.com', 'hazo_admin');
        $mail->addAddress($email, $username); // Add a recipient

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = 'AES CLOUD';
        $mail->Body    = 'Dear ' . $username . ",\n\n" .
                         'Thank you for registering with Secure File Storage. ' .
                        //   "Your Secret key is: " . $secret_key . "\n\n" .
                         'your email will be approved shortly.';

        $mail->send();
        echo "Registration successful. Admin will review your request shortly . Redirecting to login page...";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.html';
                }, 3000); // 3000 milliseconds = 3 seconds
              </script>";
    } catch (Exception $e) {
        echo "Error: Unable to send email. Please contact support. Error Info: {$mail->ErrorInfo}";
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
