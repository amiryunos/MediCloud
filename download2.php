<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Initialize variables
$fileBase64 = NULL;
$fileContentType = NULL;
$fileName = "";

// Create MySQL connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['file']) && !empty($_GET['file'])) { 
    $sql = "SELECT * FROM files WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $_GET['file'], $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // File found, fetch its details
        $row = $result->fetch_assoc();
        $filePath = $row['filepath'];
        $fileName = $row['filename'];
        $md5sum = $row['file_md5'];
    
        // Check if the file exists on the server
        if (file_exists($filePath)) {
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            switch ($fileExtension) {
                case 'txt':
                    // Assuming you have a decryption function available
                    // You would decrypt the content here before echoing it
                    $fileContentType = "text/plain";
                    $encryptedContent = file_get_contents($filePath);
                    // $decryptedContent = decryptFileContent($encryptedContent); // Make sure you have this function defined to decrypt
                    // header('Content-Type: text/plain');
                    // echo $decryptedContent;
                    $fileBase64 = base64_encode($encryptedContent);
                    break;
                case 'jpg':
                case 'jpeg':
                    $fileContentType = "image/jpeg";
                    $encryptedContent = file_get_contents($filePath);
                    $fileBase64 = base64_encode($encryptedContent);
                    break;
                case 'png':
                    $fileContentType = "image/png";
                    $encryptedContent = file_get_contents($filePath);
                    $fileBase64 = base64_encode($encryptedContent);
                case 'pdf':
                    $fileContentType = mimeType($fileExtension);
                    $encryptedContent = file_get_contents($filePath);
                    $fileBase64 = base64_encode($encryptedContent); // Encode file content as base64
                    break;
                default:
                    echo "Unsupported file format.";
                    exit;
            }
        } else {
            echo 'File not found.';
            exit;
        }
    } else {
        echo 'File not found or unauthorized access.';
        exit;
    }
    $stmt->close();
}

$conn->close();

// Function to return MIME type based on file extension
function mimeType($fileExtension) {
    switch ($fileExtension) {
        case 'txt': return "text/plain";
        case 'jpg':
        case 'jpeg': return "image/jpeg";
        case 'png': return "image/png";
        case 'pdf': return "application/pdf";
        default: return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download File</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <h2>Download Decrypted File</h2>

    <form id="decryptForm" method="post" action="download_file.php">
        <label for="DecryptionKey">Enter Decryption Key:</label>
        <input type="password" id="decryptionKey" name="decryptionKey" required>

        <label for="fileToDecrypt">File to Decrypt:</label>
        <input type="text" id="fileToDecrypt" value="<?php echo $fileName ?>" disabled>
        <input type="hidden" id="filebase64" value="<?php echo $fileBase64 ?>" />
        <input type="hidden" id="fileType" value="<?php echo $fileContentType ?>" />
        <input type="hidden" id="md5sum" value="<?php echo $md5sum ?>" />
        <button type="submit" id="DecryptButton" >Download</button>

        <label for="qrInput">Upload QR Code:</label>
        <input type="file" id="qrInput" accept="image/*">

        <!-- Area to display the AES key extracted from the QR code -->
        <p>AES Key from QR: <span id="aesKeyOutput"></span></p>
        <div id="reader" style="display: none;"></div>

    </form>
    <script src="html5-qrcode.min.js"></script>
    <script src="qrcode.js"></script>
    <script src="spark-md5.min.js"></script>
    <script src="base64-binary.js"></script>
    <script src="download.js"></script>
   <!-- Ensure you have the necessary JavaScript files and logic to handle decryption -->
</body>
</html>
