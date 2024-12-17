<?php
// Include phpqrcode library and start session
require_once "phpqrcode/qrlib.php";
session_start();

// Function to generate a new AES key
function generateAESKey() {
    $key = openssl_random_pseudo_bytes(32); // 256 bit
    return bin2hex($key); // Convert to hex format for easy handling
}

$newAESKey = generateAESKey();
$filePath = 'qrcodes/' . $newAESKey . '.png';
QRcode::png($newAESKey, $filePath, QR_ECLEVEL_L, 4); // Generate new QR code

// Return new key and file path as JSON
echo json_encode(array("newKey" => $newAESKey, "filePath" => $filePath));
?>
