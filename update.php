<?php
session_start(); // Pastikan ini di awal skrip

if (!isset($_SESSION['user_id'])) {
    die('ID Pengguna tidak diset dalam sesi.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = new mysqli("localhost", "admin", "admin", "secure_file_storage");
    
    // Periksa sambungan
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    // Mengambil data dari borang
    $first_name = $_POST['firstname'];
    $last_name = $_POST['lastname'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone'];

    // Mengemaskini data pengguna dalam database
    $query = "UPDATE users SET first_name=?, last_name=?, email=?, phone_number=? WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone_number, $_SESSION['user_id']);
    if ($stmt->execute()) {
        // Memeriksa jika ada baris yang terpengaruh
        if ($stmt->affected_rows > 0) {
            $_SESSION['notification'] = "Changes saved successfully.";
            header("Location: profile.php"); // Assuming you want to redirect to profile.php
            exit;
        } else {
            $_SESSION['notification'] = "No changes made or update failed.";
            header("Location: profile.php");
            exit;
        }
    } else {
        $_SESSION['notification'] = "Error updating record: " . $stmt->error;
        header("Location: profile.php");
        exit;
    }
    
    $stmt->close();
    $mysqli->close();
} else {
    // Jika bukan POST request, arahkan kembali ke profile.php
    header("Location: profile.php");
    exit;
}
?>
