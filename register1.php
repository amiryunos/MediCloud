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

// Function to generate secret key
function generateSecretKey($words, $numWords) {
    shuffle($words);
    return implode('-', array_slice($words, 0, $numWords));
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

// Generate a random secret key
$words = ["bicycle", "roll", "ice", "cream", "sun", "moon", "star"];
$secret_key = generateSecretKey($words, 3);

// Insert user data into 'users' table and get the last inserted user_id
$sql = "INSERT INTO users (username, password, first_name, last_name, phone_number, email) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $username, $hashed_password, $first_name, $last_name, $phone_number, $email);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id; // Get the last inserted id for the user

    // Prepare SQL statement to insert into 'aes_keys' table
    $sql_aes = "INSERT INTO aes_keys (user_id, secret_key) VALUES (?, ?)";
    $stmt_aes = $conn->prepare($sql_aes);
    $stmt_aes->bind_param("is", $user_id, $secret_key);
    $stmt_aes->execute();

    // Send the AES key to the user's email
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'wanzayn1611@gmail.com'; // SMTP username
        $mail->Password   = 'nddtfpwlyqvedylj'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('admin@boredzz.cloud', 'boredzz');
        $mail->addAddress($email, $username); // Add a recipient

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = 'Welcome to AES CLOUD';
        $mail->Body    = "Dear $username,\n\nThank you for registering with Secure File Storage. Your Secret key is: $secret_key\n\nYour email will be approved shortly.";

        $mail->send();
        echo "Registration successful. Admin will review your request shortly. Redirecting to login page...";
        echo "<script>setTimeout(function() { window.location.href = 'index.html'; }, 3000);</script>";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();
?>
