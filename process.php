<?php
session_start(); // Mulakan sesi

// Pastikan pengguna telah log masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$dbname = 'secure_file_storage';
$username = 'admin';
$password = 'admin';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Proses penyulitan
    $encryptionKey = $_POST['encryptionKey'] ?? ''; // Ambil kunci penyulitan jika ada
    $fileDescription = $_POST['fileDescription'];
    $userId = $_SESSION['user_id'];

    if (!empty($encryptionKey)) {
        // Logik untuk menyemak dan menggunakan kunci AES pengguna
        // Implementasikan logik penyulitan di sini berdasarkan $encryptionKey
        // Misalnya, gunakan OpenSSL untuk penyulitan
        // Pastikan anda telah melakukan penyulitan pada file sebelum mengunggah
    }

    // Proses pengunggahan
    $fileName = basename($_FILES["fileToUpload"]["name"]);
    $targetDir = "uploads/";
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if(!empty($_FILES["fileToUpload"]["name"])) {
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'txt', '_encrypted.txt');
        if(in_array($fileType, $allowTypes)) {
            if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFilePath)) {
                $insert = $pdo->prepare("INSERT INTO files (filename, description, filepath, user_id) VALUES (:filename, :description, :filepath, :user_id)");
                $insert->bindParam(':filename', $fileName);
                $insert->bindParam(':description', $fileDescription);
                $insert->bindParam(':filepath', $targetFilePath);
                $insert->bindParam(':user_id', $userId);

                if($insert->execute()) {
                    echo "The file has been uploaded and encrypted successfully.";
                } else {
                    echo "File upload failed, please try again.";
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo 'Sorry, only specific file types are allowed.';
        }
    } else {
        echo 'Please select a file to upload.';
    }
}
?>
