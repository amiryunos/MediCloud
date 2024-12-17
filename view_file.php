<?php
// Start session to retrieve user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Establish database connection
$conn = new mysqli('localhost', 'admin', 'admin', 'secure_file_storage');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the filename from the URL parameter and sanitize it
$filename = basename($_GET['file']);

// Prepare SQL statement to retrieve file details
$sql = $conn->prepare("SELECT * FROM files WHERE filename = ? AND user_id = ?");
$sql->bind_param("si", $filename, $user_id);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    // File found, fetch its details
    $row = $result->fetch_assoc();
    $filePath = $row['filepath'];

    // Check if the file exists and output its content
    if (file_exists($filePath)) {
        // Determine the MIME type based on the file extension
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($fileExtension) {
            case 'txt':
                // Assuming you have a decryption function available
                // You would decrypt the content here before echoing it
                $encryptedContent = file_get_contents($filePath);
                $decryptedContent = decryptFileContent($encryptedContent); // Make sure you have this function defined to decrypt
                header('Content-Type: text/plain');
                echo $decryptedContent;
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                readfile($filePath);
                break;
            case 'png':
                header('Content-Type: image/png');
                readfile($filePath);
                break;
            case 'gif':
                header('Content-Type: image/gif');
                readfile($filePath);
                break;
            case 'pdf':
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
                readfile($filePath);
                break;
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

// Close the connection
$conn->close();

// Define your decryption function here
function decryptFileContent($content) {
    // Your decryption logic here
    return $decryptedContent;
}
?>
