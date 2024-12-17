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
if (isset($_POST["action"]) && $_POST["action"] === "refreshKey") {
    $newAESKey = generateAESKey();
    $_SESSION['lastGeneratedAESKey'] = $newAESKey;
    $filePath = 'qrcodes/' . $newAESKey . '.png';
    QRcode::png($newAESKey, $filePath, QR_ECLEVEL_L, 4);
    echo json_encode(['newAESKey' => $newAESKey, 'filePath' => $filePath]);
    exit;
} else {
    $newAESKey = $_SESSION['lastGeneratedAESKey'];
}

// Generate QR code and save it as an image
$filePath = 'qrcodes/' . $newAESKey . '.png'; // Ensure this directory is writable by the server
QRcode::png($newAESKey, $filePath, QR_ECLEVEL_L, 4);

// Function to send email with QR code attachment
function sendEmailWithQR($email, $filePath, $filename) {
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
    $mail->Body = 'Attached is your AES Key QR Code. Please keep it safe!';
    $mail->addAttachment($filePath, $filename . '_key.png'); // Attach QR Code file with the correct filename

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

// Process email sending request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sendQRCode') {
    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'aes_key_qrcode';
    $userEmail = $_SESSION['user_email'];
    sendEmailWithQR($userEmail, $filePath, $filename);
    exit;
}

$userEmail = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt File</title>
    <link rel="stylesheet" type="text/css" href="styles1.css">
    <style>
    .logo {
        height: 80px; /* Adjust the size as needed */
        margin-bottom: 20px; /* Space between the logo and the tabs */
    }
    .button-group {
        display: flex; /* This will make the buttons align horizontally */
        gap: 10px; /* Adds a small space between the buttons */
    }
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .button-container {
        text-align: center;
        margin-top: 10px;
    }
    .aes-key-input {
        width: 100%; /* Adjust the width as needed */
        max-width: 600px;
        display: inline-block;
    }
    .key-container {
        text-align: center;
        margin-top: 20px;
    }
    .key-container strong {
        display: block;
        margin-bottom: 5px;
    }
    .key-container input {
        margin: 0 auto; /* Center the input element */
    }
</style>



</head>
<body>
    <img src="uitmlogo.png" alt="UiTM Logo" class="logo">
    <h2>Encrypt & Upload File using AES</h2>

    <form id="encryptForm" method="post" action="encrypt.php" enctype="multipart/form-data" onsubmit="onSubmit(event)">

        <label for="encryptionKey">Enter Encryption Key:</label>
        <input type="password" id="encryptionKey" name="encryptionKey" required placeholder="Copy and paste your key here">

        <label for="fileToEncrypt">Choose File:</label>
        <input type="file" id="fileToEncrypt" name="fileToEncrypt" required>

        <div class="button-group">
            <button type="submit" id="encryptButton">Encrypt</button>
        </div>
    </form>

    <div id="sendEmailContainer" style="display: none;">
        <button type="button" onclick="showTerms()">Send Email</button>
    </div>
    
    <!-- Display the generated AES key for the user to copy -->
    <p class="key-container">
        <strong>Your AES Key:</strong>
        <input type="text" id="aesKey" class="aes-key-input" value="<?php echo $newAESKey; ?>" readonly>
    </p>
    <div class="button-container">
        <button type="button" onclick="copyKey()">Copy to Clipboard</button>
        <button type="button" onclick="refreshKey()">Generate Key</button>
    </div>
    
    <!-- Display the QR code for the user -->
    <img src="<?php echo $filePath; ?>" alt="AES Key QR Code" id="qrCodeImage">
    <a href="<?php echo $filePath; ?>" download="aes_key_qrcode.png" id="downloadLink">
        <button type="button">Download AES Key QR Code</button>
    </a>

    <a href="home.php">Back to Home</a>

    <!-- Terms and Policies Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Terms and Policies</h2>
            <p>Please read and accept the terms and policies before sending the email.</p>
            <p>
                <strong>1. Third-Party Usage Disclaimer</strong><br>
                By receiving the AES key via email, you acknowledge that the transmission of the key involves third-party services (email providers) which may have their own security protocols. While we take reasonable measures to ensure secure transmission, we cannot guarantee the absolute security of the key during transit.
            </p>
            <p>
                <strong>2. User Responsibility</strong><br>
                You, the user, are solely responsible for maintaining the confidentiality of the AES key provided. Do not share the key with unauthorized persons. Any misuse or unauthorized access resulting from the sharing or mishandling of the key will be your responsibility.
            </p>
            <p>
                <strong>3. Encryption and Decryption</strong><br>
                The provided AES key is intended for use within our secure file storage system to encrypt and decrypt your files. Ensure that you store the key in a safe location and only use it for its intended purpose.
            </p>
            <p>
                <strong>4. Data Integrity</strong><br>
                While we use advanced encryption standards (AES) to protect your files, we do not hold liability for any data corruption, loss, or unauthorized access that may occur. Ensure you maintain backup copies of your files.
            </p>
            <p>
                <strong>5. Service Limitations</strong><br>
                Our secure file storage service is provided "as is" without any warranties, express or implied. We do not guarantee the continuous availability of the service and reserve the right to modify or discontinue the service at any time without prior notice.
            </p>
            <p>
                <strong>6. Compliance with Laws</strong><br>
                You agree to comply with all applicable local, national, and international laws and regulations in connection with your use of our secure file storage service.
            </p>
            <p>
                <strong>7. Amendments</strong><br>
                We reserve the right to update these terms and policies at any time. Continued use of the service after any such changes shall constitute your consent to such changes.
            </p>
            <button id="acceptTerms">I Agree</button>
        </div>
    </div>

    <script src="spark-md5.min.js"></script>
    <script src="script.js"></script>
    <script>
        function refreshKey() {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "qrcode.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    document.getElementById('aesKey').value = response.newAESKey;
                    document.getElementById('qrCodeImage').src = response.filePath;
                    document.getElementById('downloadLink').href = response.filePath;
                    updateDownloadLink();
                }
            }
            xhr.send('action=refreshKey');
        }

        function validateAESKey(key) {
            // Check if the key is 64 characters long and consists of only hexadecimal characters
            const hexRegex = /^[0-9a-fA-F]{64}$/;
            return hexRegex.test(key);
        }

        function onSubmit(event) {
            const aesKey = document.getElementById('encryptionKey').value;
            if (!validateAESKey(aesKey)) {
                event.preventDefault();
                alert('Please enter a valid 256-bit AES key (64 hexadecimal characters long).');
            }
        }

        function showTerms() {
            var modal = document.getElementById("termsModal");
            modal.style.display = "block";
        }

        function sendQRCode() {
            const fileInput = document.getElementById('fileToEncrypt');
            const filePath = fileInput.value;
            const fileName = filePath.split('\\').pop().split('/').pop();
            const baseName = fileName.split('.').slice(0, -1).join('.');
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "qrcode.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    alert('QR Code sent to your email.');
                }
            }
            xhr.send('action=sendQRCode&filename=' + encodeURIComponent(baseName));
        }

        function copyKey() {
            var copyText = document.getElementById("aesKey");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("AES Key copied to clipboard: " + copyText.value);
        }

        function updateDownloadLink() {
            const fileInput = document.getElementById('fileToEncrypt');
            const filePath = fileInput.value;
            const fileName = filePath.split('\\').pop().split('/').pop();
            const baseName = fileName.split('.').slice(0, -1).join('.');
            const downloadLink = document.getElementById('downloadLink');
            downloadLink.download = baseName + '_key.png';
        }

        // Update download link when file is selected
        document.getElementById('fileToEncrypt').addEventListener('change', updateDownloadLink);

        // Get the modal
        var modal = document.getElementById("termsModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // Get the button that accepts the terms
        var acceptTermsBtn = document.getElementById("acceptTerms");

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks on the "I Agree" button, close the modal and send the email
        acceptTermsBtn.onclick = function() {
            modal.style.display = "none";
            sendQRCode();
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
