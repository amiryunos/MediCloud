document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('DecryptButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting

        // Get the values from the input fields
        var decryptionKey = document.getElementById('decryptionKey').value;
        var fileBase64 = document.getElementById('filebase64').value;
        var fileContentType = document.getElementById('fileType').value; // Ensure this is the correct ID for your content type
        var fileIdValue = document.getElementById('fileId').value; // Fetch the file ID

        // Check if the decryption key is provided
        if (!decryptionKey) {
            alert('Please Enter Decrpytion Key.');
            return;
        }
        // Perform an AJAX request to validate the decryption key
        //var xhr = new XMLHttpRequest();
        //xhr.open('POST', 'validate_dkey.php'); // PHP script to validate the key
        //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //xhr.onload = function() {
            //if (xhr.status === 200) {
                //var response = xhr.responseText;
                //if (response === 'valid') {
        // Decrypt the file
        decryptFile(decryptionKey, fileBase64, fileContentType, fileIdValue);
        //} else {
                    //alert('Kunci dekripsi tidak sah.');
                //}
            //} else {
                //alert('Kesalahan semasa mengesahkan kunci dekripsi.');
            //}
        //};
        //xhr.send('DecryptionKey=' + encodeURIComponent(decryptionKey));
        //decryptFile(fileBase64, fileContentType);
    });
});

async function decryptFile(decryptionKey, base64String, fileContentType, fileIdValue) {
    try {
        var arrayBuffer = base64ToArrayBuffer(base64String);
        var derivedKey = await deriveKey(decryptionKey);
        var decryptedData = await decryptAESGCM(arrayBuffer, derivedKey);
        var oriMd5sum = document.getElementById('md5sum').value; // Original MD5 sum
        var md5sum = await SparkMD5.ArrayBuffer.hash(decryptedData); // Calculate MD5 sum of decrypted data

        if (md5sum === oriMd5sum) {

            var blob = new Blob([decryptedData], { type: fileContentType });
            // Modified part to display the file in the browser
            //var fileName = 'decrypted_file'; // Set a default or use actual file name if you have it
            //downloadBlob(blob, fileName);
            deleteDataFromDatabase(fileIdValue, md5sum); // Delete data if MD5 sums match
        } else {
            console.log('MD5 checksum mismatch');
            alert('MD5 checksum mismatch, failed to verify decrypted data.');
        }
    } catch (e) {
        console.error(e);
        alert('Terdapat kesalahan semasa mendekripsi fail.');
    }
}

function deleteDataFromDatabase(fileIdValue, md5sum) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_file.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Data Has Been Deleted.');
        } else {
            alert('Data Deletion Failed.');
        }
    };
    xhr.send('fileId=' + encodeURIComponent(fileIdValue) + '&md5sum=' + encodeURIComponent(md5sum));
}

function base64ToArrayBuffer(base64) {
    var binary_string = window.atob(base64);
    var len = binary_string.length;
    var bytes = new Uint8Array(len);
    for (var i = 0; i < len; i++) {
        bytes[i] = binary_string.charCodeAt(i);
    }
    return bytes.buffer;
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
