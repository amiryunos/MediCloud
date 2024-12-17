<?php
// encrypt_functions.php

function encryptFile($file, $key) {
    // Buka fail untuk dibaca
    $fileContent = file_get_contents($file);

    // Encrypt data guna AES-256-GCM
    $cipher = "aes-256-gcm";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $tag = null;

    $encryptedData = openssl_encrypt($fileContent, $cipher, $key, $options=0, $iv, $tag);
    if ($encryptedData === false) {
        return false;
    }

    // Gabungkan IV, tag, dan encrypted data
    return base64_encode($iv . $tag . $encryptedData);
}

function saveEncryptedFile($filePath, $encryptedData) {
    // Simpan data encrypted ke fail baru
    $encryptedFilePath = $filePath . '_encrypted';
    if (file_put_contents($encryptedFilePath, $encryptedData) === false) {
        return false;
    }

    return $encryptedFilePath;
}
?>
