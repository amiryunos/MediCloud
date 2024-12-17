<?php
// Start session
session_start();

// Check if user is logged in
$userId = NULL;

$fileBase64 = NULL;
$fileContentType = NULL;
$fileName = "";

$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if(isset($_GET['file']) && !empty($_GET['file'])) { 
    $sql = "SELECT * FROM files WHERE id = ? AND user_id = ?";
    $prep = $conn->prepare($sql);
    $prep->bind_param("si", $_GET['file'], $userId);
    $prep->execute();

    $result = $prep->get_result();

    if ($result->num_rows > 0) {
        // File found, fetch its details
        $row = $result->fetch_assoc();
        $filePath = $row['filepath'];
        $fileName = $row['filename'];
        $md5sum = $row['file_md5'];
    
        // Check if the file exists and output its content
        if (file_exists($filePath)) {
            // Determine the MIME type based on the file extension
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
                     // readfile($filePath);
                //     break;
                case 'png':
                //     header('Content-Type: image/png');
                    $fileContentType = "image/png";
                    $encryptedContent = file_get_contents($filePath);
                    $fileBase64 = base64_encode($encryptedContent);
                    break;
                //     readfile($filePath);
                //     break;
                // case 'gif':
                //     header('Content-Type: image/gif');
                //     readfile($filePath);
                //     break;
                // case 'pdf':
                case 'pdf':
                    $fileContentType = "application/pdf";
                    $encryptedContent = file_get_contents($filePath);
                    $fileBase64 = base64_encode($encryptedContent);
                    break;
                //     header('Content-Type: application/pdf');
                //     header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
                //     readfile($filePath);
                //     break;
                default:
                    echo "Unsupported file format.";
                    exit;
            }
        } else {
            echo 'File not found.';
        }
    } else {
        echo 'File not found or unauthorized access.';
    }
    
    // Define your decryption function here
    function decryptFileContent($content) {
        // Your decryption logic here
        return $decryptedContent;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decrypt File</title>
    <link rel="stylesheet" type="text/css" href="styles1.css">
</head>
<body>
    <h2>Decrypt File using AES</h2>

    <form id="decryptForm" method="post" action="decrypt.php">
        <label for="decryptionKey">Enter Decryption Key:</label>
        <input type="password" id="decryptionKey" name="decryptionKey" required>

        <label for="fileToDecrypt">Choose File:</label>
        <input type="text" id="fileToDecrypt" value="<?php echo $fileName ?>" disabled>
        <input type="hidden" id="filebase64" value="<?php echo $fileBase64 ?>" />
        <input type="hidden" id="fileType" value="<?php echo $fileContentType ?>" />
        <input type="hidden" id="md5sum" value="<?php echo $md5sum ?>" />
        <button type="submit" id="decryptButton">Decrypt</button>

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
    <script src="script1.js"></script>
</body>
</html>
