<?php
// Include phpqrcode library
require_once "phpqrcode/qrlib.php"; // Adjust the correct path to the phpqrcode library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/etc/PHPMailer/src/Exception.php';
require '/etc/PHPMailer/src/PHPMailer.php';
require '/etc/PHPMailer/src/SMTP.php';


// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit;
}

// Function to generate a 256-bit AES key
function generateAESKey() {
    $key = openssl_random_pseudo_bytes(32); // 256 bit
    return bin2hex($key); // Convert to hex format for easy handling
}

// Generate a new AES key for user interaction
$newAESKey = generateAESKey();
$_SESSION['lastGeneratedAESKey'] = $newAESKey;  // After generating $newAESKey


// Generate QR code and save it as an image
$filePath = 'qrcodes/' . $newAESKey . '.png'; // Ensure this directory is writable by the server
QRcode::png($newAESKey, $filePath, QR_ECLEVEL_L, 4);

//Function to send email with QR code attachment
function sendEmailWithQR($email, $filePath) {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Change to your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'wanzayn1611@gmail.com'; // Change to your SMTP username
    $mail->Password = 'nddtfpwlyqvedylj'; // Change to your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587;

    $mail->setFrom('wanzayn@gmail.com', 'Secure File Storage');
    $mail->addAddress($email); // Add recipient email address

    $mail->isHTML(true);
    $mail->Subject = 'Your AES Key QR Code';
    $mail->Body    = 'Attached is your AES Key QR Code. Please keep it safe!';
    $mail->addAttachment($filePath); // Attach QR Code file

    if (!$mail->send()) {
        // Log mailer error
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Email sent with QR Code.';
        // Log successful email sending
        error_log("Email successfully sent to: " . $email);
    }
}

$userEmail = $_SESSION['user_email'];

//Handle AJAX request for refreshing the AES key
if (isset($_POST['action']) && $_POST['action'] == 'refreshKey') {
    $newAESKey = generateAESKey();
    $filePath = 'qrcodes/' . $newAESKey . '.png';
    QRcode::png($newAESKey, $filePath, QR_ECLEVEL_L, 4);
    echo json_encode(['newAESKey' => $newAESKey, 'filePath' => $filePath]);
    exit;
} elseif ($_POST['action'] == 'sendQRCode') {
    // Call function to send email
    sendEmailWithQR($userEmail, $filePath);
}

// Check if form data is submitted
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Establish database connection
//     $conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

//     // Check connection
//     if ($conn->connect_error) {
//         die("Connection failed: " . $conn->connect_error);
//     }

//     // Collect form data
//     $encryptionKey = $_POST['encryptionKey'];
//     $userId = $_SESSION['user_id'];

//     // Here, we compare directly to the posted key
//     if ($encryptionKey === $newAESKey) {
//         // Proceed with encryption logic
//         echo "File encrypted successfully.";
//         // Delay the redirection for 3 seconds
//         sleep(3);
//         header("location: upload.html");
//         exit;
//     } else {
//         echo "Invalid encryption key.";
//     }

//     // Close the connection
//     $conn->close();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt File</title>
    <link rel="stylesheet" type="text/css" href="styles1.css">
</head>
<body>
    <img src="uitmlogo.png" alt="UiTM Logo" class="logo">
    <style>
        .logo{
            height: 80px; /* Adjust the size as needed */
            margin-bottom: 20px; /* Space between the logo and the tabs */
        }
    </style>
    <h2>Encrypt & Upload File using AES</h2>

    <form id="encryptForm" method="post" action="encrypt.php" enctype="multipart/form-data">
        <label for="encryptionKey">Enter Encryption Key:</label>
        <input type="password" id="encryptionKey" name="encryptionKey" required placeholder="Copy and paste your key here">

        <label for="fileToEncrypt">Choose File:</label>
        <input type="file" id="fileToEncrypt" name="fileToEncrypt" required>

        <!-- <label for="descrption">Descrption:</label>
        <input type="descrption" id="description" name="description" required placeholder="talk about this file"> -->

        <button type="submit" id="encryptButton">Encrypt</button>
    </form>

    <!-- Display the generated AES key for the user to copy -->
    <p>
    <strong>Your AES Key:</strong>
    <span id="aesKey"><?php echo $newAESKey; ?></span>
    <button type="button" onclick="refreshKey()">Generate Key</button>
    </p>
    <script>
    function refreshKey() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "qrcode.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            const response = JSON.parse(this.responseText);
            document.getElementById('aesKey').innerText = response.newAESKey;
            document.querySelector('img[alt="AES Key QR Code"]').src = response.filePath;
        }
    }
    xhr.send('action=refreshKey');
}
</script>
<br/>
    <!-- Display the QR code for the user -->
    <img src="<?php echo $filePath; ?>" alt="AES Key QR Code">
    <a href="<?php echo $filePath; ?>" download="aes_key_qrcode.png">
    <button type="button">Download AES Key QR Code</button>
    <!-- <button type="button" onclick="sendQRCode()">Send QR Code by Email</button> -->
    <script>
//     function refreshKey() {
//     const xhr = new XMLHttpRequest();
//     xhr.open("POST", "qrcode.php", true);
//     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
//     xhr.onreadystatechange = function() {
//         if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
//             const response = JSON.parse(this.responseText);
//             document.getElementById('aesKey').innerText = response.newAESKey;
//             document.querySelector('img[alt="AES Key QR Code"]').src = response.filePath;
//         }
//     }
//     xhr.send('action=refreshKey');
// }
        function sendQRCode() {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "email.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            alert('QR Code sent to your email.');
        }
    }
    xhr.send('action=sendQRCode');
}
    </script>
    <!-- <button type="button" onclick="sendQRCode()">Send QR Code by Email</button> -->
    </a>
    <a href="home.php">Back to Home</a>

    <script src="spark-md5.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
