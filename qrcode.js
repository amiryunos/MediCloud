document.getElementById('qrInput').addEventListener('change', function(event) {
    if (event.target.files && event.target.files[0]) {
        // Create a new instance of Html5Qrcode (#reader is the ID of the div).
        let qrCodeScanner = new Html5Qrcode("reader");

        // Use the correct method to scan the file.
        qrCodeScanner.scanFile(event.target.files[0], true)
            .then(function(decodedText) {
                // Display the AES key from the QR code
                document.getElementById('aesKeyOutput').textContent = decodedText;
                // Also, automatically populate the decryption key input if it's needed
                document.getElementById('decryptionKey').value = decodedText;
            })
            .catch(function(err) {
                // Handle errors for when the QR code could not be decoded
                console.error(err);
                alert('Error scanning QR Code. Please make sure you are uploading a valid QR image.');
            })
            .finally(function() {
                // Cleanup code
                try {
                    qrCodeScanner.clear();
                } catch (error) {
                    console.warn("QR Scanner cleanup failed. ", error);
                }
            });
            
    }
});
