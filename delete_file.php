<?php
session_start(); // Mula session, penting untuk pengesahan pengguna

// Konfigurasi database
$host = 'localhost';
$dbname = 'secure_file_storage'; // Gantikan dengan nama database anda
$username = 'admin';
$password = 'admin';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access.'); // Atau redirect ke halaman login
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Contoh fungsi validateKey(). Anda perlu definisikan fungsi ini atau padamkan if statement ini jika tidak digunakan.
    // if(!validateKey()) {
    //    echo "Invalid key!";
    //    exit();
    // }

    if (isset($_POST['fileId'])) {
        $fileId = $_POST['fileId'];
        $userId = $_SESSION['user_id']; // Dapatkan ID pengguna dari session
    
        // Verifikasi file itu milik pengguna yang login
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = :fileId AND user_id = :userId");
        $stmt->execute(['fileId' => $fileId, 'userId' => $userId]);
        $file = $stmt->fetch();
    
        if ($file) {
            $filepath = $file['filepath'];
    
            if($_POST['md5sum'] !== $file['file_md5']) {
                echo "Invalid MD5 Hash!";
                exit;
            }
    
            // Padam fail dari server
            if (file_exists($filepath)) {
                unlink($filepath);
            }
    
            // Padam rekod dari database
            $delete = $pdo->prepare("DELETE FROM files WHERE id = :fileId AND user_id = :userId");
            $delete->execute(['fileId' => $fileId, 'userId' => $userId]);
    
            echo "File deleted successfully.";
        } else {
            echo "File not found or you do not have permission.";
        }
    } else {
        echo "No file specified lol.";
    }    
} catch (PDOException $e) { // Pastikan ini adalah penutup blok `try`
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Redirect ke halaman senarai fail. Pastikan menggunakan absolute path atau URL yang sah.
//header("Location: view.php");
exit;
?>
