document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('DecryptButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting

        var decryptionKey = document.getElementById('DecryptionKey').value;
        var fileBase64 = document.getElementById('filebase64').value;
        var fileContentType = document.getElementById('fileId').value;

        // Check if the decryption key is providedddd
        if (!decryptionKey) {
            alert('Please Enter Decryption Key.');
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
                    // If the key is valid, proceed with decryption logic
                    decryptFile(decryptionKey, fileBase64, fileContentType);
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

async function decryptFile(decryptionKey, base64String, fileId) {
    try {
        var arrayBuffer = base64ToArrayBuffer(base64String);
        var derivedKey = await deriveKey(decryptionKey);
        var decryptedData = await decryptAESGCM(arrayBuffer, derivedKey);
        console.log(md5sum);
        var oriMd5sum = document.getElementById('md5sum').value;

        var md5sum = await SparkMD5.ArrayBuffer.hash(decryptedData);


        if(md5sum == oriMd5sum) {

            //var blob = new Blob([decryptedData], { type: fileContentType });
            // Modified part to display the file in the browser
            //var fileName = 'decrypted_file'; // Set a default or use actual file name if you have it
            //downloadBlob(blob, fileName);
            deleteDataFromDatabase(fileId);
        }
        else{
            console.log(md5sum);
            alert("Failed to delete");
        }
        // Assuming the file name is stored in a data attribute or you can set a default
    } catch (e) {
        console.error(e);
        alert('Error in Decryption Process.');
    }
}

function deleteDataFromDatabase(fileId) {

    if (!fileId) {
        alert('Error: fileId is Not Valid.');
        return; // Hentikan eksekusi fungsi jika fileId tidak valid
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_file.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Data Sucessfully Deleted.');
        } else {
            alert('Error In File Deletion.');
        }
    };
    xhr.send('fileId=' + encodeURIComponent(fileId));
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

function downloadBlob(blob, fileName) {
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
