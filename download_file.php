<?php
// Database configuration
$host = 'localhost';
$dbname = 'secure_file_storage'; // Replace with your database name
$username = 'admin';
$password = 'admin';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the filename from the query string
    if(isset($_GET['file'])) {
        $filename = $_GET['file'];

        // Fetch file path from database
        $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = :filename");
        $stmt->execute(['filename' => $filename]);
        $file = $stmt->fetch();

        if($file) {
            $filepath = $file['filepath'];
            // Check if file exists
            if(file_exists($filepath)) {
                // Set headers to download file rather than displayed
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                flush(); // Flush system output buffer
                readfile($filepath);
                exit;
            }
        }
    }
    echo "File not found.";
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
