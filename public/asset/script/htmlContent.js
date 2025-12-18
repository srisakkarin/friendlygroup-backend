// public/asset/script/htmlContent.js
$(document).ready(function () {
    const htmlEditer = $('#html-content').trumbowyg().on('tbwinit tbwfocus tbwblur tbwchange tbwresize tbwpaste tbwopenfullscreen tbwclosefullscreen tbwclose', function (e) {
        console.log(e.type);
    });

});

document.addEventListener('DOMContentLoaded', function () {
    const uploadButton = document.getElementById('uploadButton');
    const fileInput = document.getElementById('fileInput');
    const fileManagerContainer = document.querySelector('.file-manager-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;

    if (!csrfToken) {
        console.error('CSRF token not found. Make sure it is set in a meta tag.');
    }

    if (uploadButton) {
        uploadButton.addEventListener('click', () => {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                uploadFile(file);
            }
        });
    }

    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        uploadButton.disabled = true;
        uploadButton.innerHTML = '<i class="ion ion-md-refresh"></i>&nbsp; Uploading...';

        try {
            // IMPORTANT: Replace '/upload-file-endpoint' with your actual backend endpoint URL
            const response = await fetch('/upload-file-endpoint', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `Upload failed with status: ${response.status}`);
            }

            if (result.success && result.filePath && result.fileName) {
                addFileToView(result.fileName, result.filePath, result.fileType, result.fileUrl);
                fileInput.value = ''; // Clear the file input
            } else {
                throw new Error(result.message || 'Failed to process uploaded file.');
            }

        } catch (error) {
            console.error('Upload error:', error);
            alert('Error uploading file: ' + error.message);
        } finally {
            uploadButton.disabled = false;
            uploadButton.innerHTML = '<i class="ion ion-md-cloud-upload"></i>&nbsp; Upload';
        }
    }

    function addFileToView(fileName, serverFilePath, fileType, publicFileUrl) {
        let fileDisplay;
        // Use the publicFileUrl provided by the server for display
        const displayUrl = publicFileUrl;

        if (fileType && fileType.startsWith('image/')) {
            fileDisplay = `<div class="file-item-img" style="background-image: url(${displayUrl});"></div>`;
        } else {
            let iconClass = 'ion ion-md-document'; // Default icon
            if (fileType === 'application/pdf') iconClass = 'ion ion-md-document'; // Could be more specific, e.g. 'ion ion-logo-adobe' if available
            else if (fileType && (fileType.includes('word') || fileType.includes('document'))) iconClass = 'ion ion-md-document'; // Example
            else if (fileType && (fileType.includes('excel') || fileType.includes('spreadsheet'))) iconClass = 'ion ion-md-document'; // Example
            fileDisplay = `<div class="file-item-icon d-flex justify-content-center align-items-center w-100 h-100"><i class="${iconClass}" style="font-size: 2.5rem;"></i></div>`;
        }

        const fileItemHTML = `
            <div class="file-item">
                <div class="file-item-select-bg bg-primary"></div>
                <label class="file-item-checkbox custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" />
                    <span class="custom-control-label"></span>
                </label>
                <div style="width: 4rem; height: 4rem; margin: 0 auto 0.75rem auto;"> <!-- Container for image or icon -->
                    ${fileDisplay}
                </div>
                <a href="${displayUrl}" target="_blank" class="file-item-name">
                    ${fileName}
                </a>
                <div class="file-item-changed">${new Date().toLocaleDateString()}</div>
                <div class="file-item-actions btn-group">
                    <button type="button" class="btn btn-default btn-sm rounded-pill icon-btn borderless md-btn-flat hide-arrow dropdown-toggle" data-toggle="dropdown"><i class="ion ion-ios-more"></i></button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:void(0)" onclick="copyStaticFilePath('${displayUrl}')">Copy URL</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="deleteUploadedFileItem(this, '${serverFilePath}')">Remove</a>
                    </div>
                </div>
            </div>
        `;
        if (fileManagerContainer) {
            // Insert before the first static item or at the end if no static items
            const firstStaticItem = fileManagerContainer.querySelector('.file-item:not(.dynamically-added)'); // Add a class to distinguish if needed
            if (firstStaticItem) {
                fileManagerContainer.insertBefore(document.createRange().createContextualFragment(fileItemHTML), firstStaticItem);
            } else {
                fileManagerContainer.insertAdjacentHTML('beforeend', fileItemHTML);
            }
            // Add a class to the new item to mark it as dynamic if you want to differentiate
            const addedItem = fileManagerContainer.lastElementChild; // Or find it more robustly
            if (addedItem) addedItem.classList.add('dynamically-added');
        }
    }

    // Make functions globally accessible for onclick handlers
    window.copyStaticFilePath = function (filePath) {
        navigator.clipboard.writeText(filePath).then(() => {
            alert('File URL copied to clipboard!');
        }).catch(err => {
            alert('Failed to copy URL.');
            console.error('Could not copy text: ', err);
        });
    }

    window.deleteUploadedFileItem = async function (element, serverFilePath) {
        if (confirm('Are you sure you want to remove this file? This will also attempt to delete it from the server.')) {
            const fileItem = element.closest('.file-item');

            try {
                // IMPORTANT: Replace '/delete-file-endpoint' with your actual backend endpoint
                const response = await fetch('/delete-file-endpoint', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ filePath: serverFilePath }) // Send the server-side path for deletion
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || `Deletion failed with status: ${response.status}`);
                }

                if (result.success) {
                    if (fileItem) {
                        fileItem.remove();
                    }
                    alert('File removed successfully.');
                } else {
                    throw new Error(result.message || 'Server could not delete the file.');
                }

            } catch (error) {
                console.error('Delete error:', error);
                alert('Error removing file: ' + error.message);
            }
        }
    }
});
