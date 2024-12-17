document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('decryptButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting

        var decryptionKey = document.getElementById('decryptionKey').value;
        var fileBase64 = document.getElementById("filebase64").value;
        var fileContentType = document.getElementById("fileType").value;

        // Check if the decryption key is provided
        if (!decryptionKey) {
            alert('Please enter a decryption key.');
            return;
        }

        decryptFile(fileBase64, fileContentType);
    });
});

async function decryptFile(base64String, fileContentType) {
    try {
        var decryptionKey = document.getElementById('decryptionKey').value;
        var oriMd5sum = document.getElementById('md5sum').value;
        
        var arrayBuffer = Base64Binary.decodeArrayBuffer(base64String);

        // Derive an AES key from the user's input using PBKDF2
        console.log(decryptionKey);
        var derivedKey = await deriveKey(decryptionKey);

        // Measure the decryption time
        var startTime = performance.now();
        var decryptedData = await decryptAESGCM(arrayBuffer, derivedKey);
        var endTime = performance.now();
        var decryptionTime = endTime - startTime;
        console.log('Decryption Time: ' + decryptionTime + ' milliseconds');

        var md5sum = await SparkMD5.ArrayBuffer.hash(decryptedData);
        console.log('MD5 Hash of the decrypted file:', md5sum);

        if(md5sum == oriMd5sum) {
            // Display the file in the browser
            var blob = new Blob([decryptedData], { type: fileContentType });
            var url = URL.createObjectURL(blob);
            console.log(url);
            window.open(url, '_blank');
        } else {
            alert("Failed to decrypt: MD5 hash mismatch.");
        }
    } catch (error) {
        console.error('Error:', error);
        alert("An error occurred during decryption. Please try again.");
    }
}

function readFileAsArrayBuffer(file) {
    return new Promise((resolve, reject) => {
        var reader = new FileReader();
        reader.onload = function (event) {
            resolve(event.target.result);
        };
        reader.onerror = function (error) {
            reject(error);
        };
        reader.readAsArrayBuffer(file);
    });
}

function getMimeType(fileName) {
    var mimeType = 'application/octet-stream'; // Default, for undetermined or unsupported types
    if (fileName.endsWith('.txt')) {
        mimeType = 'text/plain';
    } else if (fileName.endsWith('.pdf')) {
        mimeType = 'application/pdf';
    } else if (fileName.endsWith('.png')) {
        mimeType = 'image/png';
    } else if (fileName.endsWith('.jpg') || fileName.endsWith('.jpeg')) {
        mimeType = 'image/jpeg';
    }
    return mimeType;
}

async function deriveKey(password) {
    var encoder = new TextEncoder();
    var keyMaterial = await crypto.subtle.importKey(
        'raw',
        encoder.encode(password),
        { name: 'PBKDF2' },
        false,
        ['deriveBits', 'deriveKey']
    );

    return crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: new Uint8Array([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]),
            iterations: 100000,
            hash: 'SHA-256',
        },
        keyMaterial,
        { name: 'AES-GCM', length: 256 },
        true,
        ['encrypt', 'decrypt']
    );
}

async function decryptAESGCM(data, key) {
    var iv = new Uint8Array(data.slice(0, 12));
    var encryptedData = new Uint8Array(data.slice(12));

    var decryptedData = await crypto.subtle.decrypt(
        {
            name: 'AES-GCM',
            iv: iv,
        },
        key,
        encryptedData
    );

    return decryptedData;
}

// Function to initialize the QR code scanner
function startQrCodeScanner() {
    const qrCodeReader = new Html5Qrcode("qr-reader");
    qrCodeReader.start(
        { facingMode: "environment" }, // Use the environment-facing camera
        {
            fps: 10, // Sets the Frames Per Second for QR code scanning
            qrbox: { width: 250, height: 250 } // Specify the QR box dimension
        },
        (decodedText, decodedResult) => {
            // Handle the decoded text here
            document.getElementById('decryptionKey').value = decodedText;
            // Stop the QR code reader after successful scan
            qrCodeReader.stop();
        })
        .catch((err) => {
            // Handle error in case of failure to start the QR code reader
            console.error(`Error starting QR scanner: ${err}`);
        });
}

// Start the QR code scanner when the page has finished loading
startQrCodeScanner();
