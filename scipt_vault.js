document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('encryptButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting

        var encryptionKey = document.getElementById('encryptionKey').value;

        // Validate the encryption key
        if (!validateAESKey(encryptionKey)) {
            alert('Please enter a valid 256-bit AES key (64 hexadecimal characters long).');
            return;
        }

        // Proceed with encryption
        encryptFiles();
    });
});

function validateAESKey(key) {
    // Check if the key is 64 characters long and consists of only hexadecimal characters
    const hexRegex = /^[0-9a-fA-F]{64}$/;
    return hexRegex.test(key);
}

async function encryptFiles() {
    var encryptionKey = document.getElementById('encryptionKey').value;
    var fileInput = document.getElementById('fileToEncrypt');

    if (!encryptionKey || !fileInput.files.length) {
        alert('Please enter an encryption key and choose at least one file.');
        return;
    }

    var filesToEncrypt = fileInput.files;
    var fileNamePrefix = document.getElementById('vaultName').value; // Get the vault name
    var uploadPromises = [];

    // Derive an AES key from the user's input using PBKDF2
    var derivedKey = await deriveKey(encryptionKey);

    for (var i = 0; i < filesToEncrypt.length; i++) {
        var fileToEncrypt = filesToEncrypt[i];
        var fileName = fileToEncrypt.name;
        var encryptedFileName = `${fileNamePrefix}_${fileName.split('.').slice(0, -1).join('.')}_encrypted.${fileName.split('.').pop()}`;

        // Read the file content as an ArrayBuffer
        var arrayBuffer = await readFileAsArrayBuffer(fileToEncrypt);
        var md5sum = await SparkMD5.ArrayBuffer.hash(arrayBuffer);

        console.log('MD5 Hash of the original file:', md5sum);

        // Measure the encryption time
        var startTime = performance.now();
        var encryptedData = await encryptAESGCM(arrayBuffer, derivedKey);
        var endTime = performance.now();
        var encryptionTime = endTime - startTime;

        // Log the encryption time to the console
        console.log('Encryption Time: ' + encryptionTime + ' milliseconds');

        // Create a Blob with the encrypted data
        var blob = new Blob([encryptedData], { type: 'application/octet-stream' });

        // Upload the encrypted file
        uploadPromises.push(uploadEncryptedFile(blob, encryptedFileName, md5sum));
    }

    // Wait for all files to be uploaded
    Promise.all(uploadPromises).then(() => {
        alert('All files have been successfully uploaded.');
        // Show the Send Email button after successful encryption and upload
        document.getElementById('sendEmailContainer').style.display = 'block';
    }).catch(error => {
        console.error('Error uploading files:', error);
        alert('Error uploading some files. Please try again.');
    });
}

function uploadEncryptedFile(blob, fileName, md5sum) {
    return new Promise((resolve, reject) => {
        var formData = new FormData();
        formData.append('fileToUpload', blob, fileName);
        formData.append('md5', md5sum);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload_file.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var resp = JSON.parse(xhr.responseText);
                if (resp['success']) {
                    console.log('File upload success status:', resp['success']);
                    resolve();
                } else {
                    reject(resp['message']);
                }
            } else {
                reject('Fail To Upload Please Try Again.');
            }
        };
        xhr.onerror = function() {
            reject('Error In File Uploading. Please Try Again.');
        };
        xhr.send(formData);
    });
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

async function encryptAESGCM(data, key) {
    var iv = crypto.getRandomValues(new Uint8Array(12));
    var encryptedData = await crypto.subtle.encrypt(
        {
            name: 'AES-GCM',
            iv: iv,
        },
        key,
        data
    );

    // Combine IV and encrypted data into a single array
    var result = new Uint8Array(iv.length + new Uint8Array(encryptedData).length);
    result.set(iv);
    result.set(new Uint8Array(encryptedData), iv.length);

    return result;
}

function refreshKey() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "qrcode.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            const response = JSON.parse(this.responseText);
            document.getElementById('aesKey').value = response.newAESKey;
            document.getElementById('qrCodeImage').src = response.filePath;
            document.getElementById('downloadLink').href = response.filePath;
        }
    }
    xhr.send('action=refreshKey');
}

function showTerms() {
    var modal = document.getElementById("termsModal");
    modal.style.display = "block";
}

function sendQRCode() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "email.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            alert('QR Code sent to your email.');
        }
    }
    xhr.send('action=sendQRCode');
}

function copyKey() {
    var copyText = document.getElementById("aesKey");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");
    alert("AES Key copied to clipboard: " + copyText.value);
}

// Get the modal
var modal = document.getElementById("termsModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// Get the button that accepts the terms
var acceptTermsBtn = document.getElementById("acceptTerms");

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks on the "I Agree" button, close the modal and send the email
acceptTermsBtn.onclick = function() {
    modal.style.display = "none";
    sendQRCode();
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
