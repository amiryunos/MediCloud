<?php
// Database configuration
$host = 'localhost';
$dbname = 'secure_file_storage'; // Replace with your actual database name
$username = 'admin';
$password = 'admin';

// Attempt to connect to MySQL database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Start the session
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve file description from form
    //$fileDescription = $_POST['fileDescription'];
    // Retrieve file name and prepare target path
    $fileName = basename($_FILES["fileToUpload"]["name"]);
    $targetDir = "uploads/";
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $status = false;
    // Check if file is selected and then proceed to upload
    if(!empty($_FILES["fileToUpload"]["name"])) {
        // Specify file upload path
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf','txt','_encrypted.txt'); // Add or remove file types as needed
        if(in_array($fileType, $allowTypes)){
            // Upload file to server
            $md5sum = $_POST['md5'];
            //$fileContent = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
            if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFilePath)){
                // Insert file info into the database
                $insert = $pdo->prepare("INSERT INTO files (filename, description, filepath, user_id, file_md5) VALUES (:filename, :description, :filepath, :user_id, :md5)");
                $insert->bindParam(':filename', $fileName);
                $insert->bindParam(':description', $fileDescription);
                $insert->bindParam(':filepath', $targetFilePath);
                $insert->bindParam(':md5', $md5sum);
                // Retrieve user ID from the session
                $user_id = $_SESSION['user_id'];
                $insert->bindParam(':user_id', $user_id);

                if($insert->execute()){
                    $statusMsg = "The file ".$fileName. " has been uploaded successfully.";
                    $status = true;
                } else {
                    $statusMsg = "File upload failed, please try again.";
                } 
            } else {
                $statusMsg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $statusMsg = 'Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed to upload.';
        }
    } else {
        $statusMsg = 'Please select a file to upload.';
    }

    // Display status message
    $respond=array('message' => $statusMsg,'success' => $status );
    header("Content-Type: application/json");
    echo json_encode($respond);
}
?>


