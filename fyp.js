async function encryptFile() {
    var encryptionKeyHex = document.getElementById('encryptionKey').value;
    var fileInput = document.getElementById('fileToEncrypt');

    if (!encryptionKeyHex || !fileInput.files.length) {
        alert('Please enter an encryption key and choose a file.');
        return;
    }

    var fileToEncrypt = fileInput.files[0];
    var fileName = fileToEncrypt.name;
    var encryptedFileName = fileName.split('.').slice(0, -1).join('.') + '_encrypted.' + fileName.split('.').pop();
    var arrayBuffer = await readFileAsArrayBuffer(fileToEncrypt);
    var md5sum = await SparkMD5.ArrayBuffer.hash(arrayBuffer);
    console.log('MD5 Hash of the original file:', md5sum);
    var derivedKey = await deriveKeyFromHex(encryptionKeyHex);

    var startTime = performance.now();
    var encryptedData = await encryptAESGCM(arrayBuffer, derivedKey);
    var endTime = performance.now();
    var encryptionTime = endTime - startTime;
    console.log('Encryption Time: ' + encryptionTime + ' milliseconds');

    var blob = new Blob([encryptedData], { type: 'application/octet-stream' });
    console.log('Encrypted Blob:', blob);
    console.log('Encrypted File Name:', encryptedFileName);

    uploadEncryptedFile(blob, encryptedFileName, md5sum);
}

function uploadEncryptedFile(blob, fileName, md5sum) {
    var formData = new FormData();
    formData.append('fileToUpload', blob, fileName);
    formData.append('md5', md5sum);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload_file.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
                alert('File telah berjaya di-encrypt dan di-upload.');
                document.getElementById('sendEmailContainer').style.display = 'block';
            } else {
                alert(resp.message);
            }
        } else {
            alert('Gagal meng-upload file. Sila cuba lagi.');
        }
    };
    xhr.onerror = function() {
        alert('Terjadi kesalahan dalam meng-upload file. Sila pastikan sambungan internet anda stabil.');
    };
    xhr.send(formData);
}
