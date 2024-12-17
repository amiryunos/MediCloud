document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('deleteButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting

        var deletionKey = document.getElementById('deletionKey').value;
        //var fileId = document.getElementById('fileToDelete').value; // Assuming you have an input for file ID

        // Check if the deletion key and file ID is provided
        if (!deletionKey) {
            alert('Please enter a deletion key .');
            return;
        }

        // Perform an AJAX request to validate the deletion key
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'validate_delete.php'); // PHP script to validate the key for deletion
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = xhr.responseText;
                if (response === 'valid') {
                    // If the key is valid, proceed with deletion logic
                    deleteFile(fileId);
                } else {
                    alert('Invalid deletion key.');
                }
            } else {
                alert('Request failed. Please try again.');
            }
        };
        //console.log(fileId);
        xhr.send('deletionKey=' + encodeURIComponent(deletionKey));
    });
});

function deleteFile(fileId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_file.php', true); // PHP script to delete the file
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = xhr.responseText;
            if (response === 'success') {
                alert('File has been successfully deleted.');
                // Logic to handle UI changes or redirections after successful deletion
            } else {
                alert('Failed to delete the file. Please try again.');
            }
        } else {
            alert('Request failed. Please try again.');
        }
    };
    xhr.onerror = function() {
        alert('There was a problem with the request. Please check your connection and try again.');
    };
    xhr.send('fileId=' + encodeURIComponent(fileId));
}
