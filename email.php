<?php
require_once "phpqrcode/qrlib.php"; // Ensure this path is correct
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '/etc/PHPMailer/src/Exception.php';
require '/etc/PHPMailer/src/PHPMailer.php';
require '/etc/PHPMailer/src/SMTP.php';
session_start();

//Check if the AES Key is already generated and stored in the session
// if (!isset($_SESSION['lastGeneratedAESKey'])) {
//     // Generate a new AES key if not present
//     $_SESSION['lastGeneratedAESKey'] = bin2hex(openssl_random_pseudo_bytes(32)); // 256-bit key
// }

$newAESKey = $_SESSION['lastGeneratedAESKey'];
// $_SESSION['newAESKey'] = $newAESKey;

// Function to send email with QR code attachment
function sendEmailWithQR($email, $filePath, $aesKey) {
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
        $mail->Subject = 'Your AES Key QR Code';
        $mail->Body = 'Attached is your AES Key QR Code. Please keep it safe!';
        
        // Generate and attach QR code
        $qrFilePath = 'qrcodes/' . $aesKey . '.png';
        QRcode::png($aesKey, $qrFilePath, 'L', 4, 2);
        $mail->addAttachment($qrFilePath);

        $mail->send();
        echo 'Email sent with QR Code.';
    } catch (Exception $e) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

// Check if the 'sendQRCode' action is triggered
if (isset($_POST['action']) && $_POST['action'] == 'sendQRCode' && isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email']; // Fetch the email from session
    sendEmailWithQR($email, null, $newAESKey); // Call the email function
}
?>
