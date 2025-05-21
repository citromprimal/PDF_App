document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registrationform');
    const apiKeyForm = document.getElementById('apiKeyForm');

    const mergePdfForm = document.getElementById('merge-form');
    const deletePageForm = document.getElementById('delete-pages-form');
    const reorderPagesForm = document.getElementById('reorder-pages-form');
    const rotatePagesForm = document.getElementById('rotate-pages-form');
    const extractPagesForm = document.getElementById('extract-pages-form');
    const imagesToPdfForm = document.getElementById('images-to-pdf-form');
    const pdfToTextForm = document.getElementById('pdf-to-text-form');
    const addWatermarkForm = document.getElementById('add-watermark-form');
    const compressPdfForm = document.getElementById('compress-pdf-form');
    const splitPdfForm = document.getElementById('split-pdf-form');

    const getApiKey = () => {
        return localStorage.getItem('api_key');
    };

    if (document.getElementById('message')) {
        const messageDiv = document.getElementById('message');

        // Helper function to display messages
        const displayMessage = (text, type = 'info') => {
            if (messageDiv) {
                messageDiv.innerHTML = text;
                messageDiv.className = `alert alert-${type}`;
            } else {
                console.log(`Message (${type}): ${text}`);
            }
        };

        // Helper function to clear messages
        const clearMessage = () => {
            if (messageDiv) {
                messageDiv.innerHTML = '';
                messageDiv.className = '';
            }
        };

        const apiKey = getApiKey();
        const headers = {
            'Accept': 'application/json',
            "X-Requested-Source": "frontend"
        };

        // Add API key header ONLY if a key exists (skip for login/register if handlePdfToolSubmit is used there)
        if (apiKey) {
            headers['X-API-Key'] = apiKey;
        } else {
            // If a secured endpoint is called without a key, redirect to login or show an error
            if (!['login', 'register'].includes(endpoint)) { // Check if the endpoint requires auth
                window.location.href = 'login.php';
                return; // Stop the submission
            }
        }

        if (apiKeyForm) {
            apiKeyForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearMessage();

                const user_id = apiKeyForm.user_id.value;

                displayMessage('Regenerating API key...', 'info');

                try {
                    const response = await fetch('https://node35.webte.fei.stuba.sk/pdf_app/api/v0/regenerate-api-key', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({user_id}),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        displayMessage(result.error || result.message || `API key regeneration failed (Status: ${response.status})`, 'danger');
                        return;
                    }

                    displayMessage(result.message || 'API key regenerated successfully!', 'success');
                    localStorage.setItem('api_key', result.api_key);
                } catch (error) {
                    console.error('API key regeneration fetch error:', error);
                    displayMessage('An error occurred during API key regeneration: ' + error.message, 'danger');
                }
            });
        }

        const handlePdfToolSubmit = async (event, endpoint, successCallback) => {
            event.preventDefault();
            clearMessage();

            const form = event.target;
            const formData = new FormData(form);

            displayMessage('Processing PDF...', 'info');

            try {
                const response = await fetch(`https://node35.webte.fei.stuba.sk/pdf_app/api/v0/${endpoint}`, {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                // For file downloads, the response is the file itself, not JSON
                const contentType = response.headers.get('Content-Type');
                if (response.ok && (contentType === 'application/pdf' || contentType === 'application/zip')) {
                    const blob = await response.blob();
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'processed_output'; // Default filename base

                    // Attempt to extract filename from Content-Disposition
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1];
                        } else {
                            // Fallback filename if header is present but filename=".." is not found
                            filename = endpoint + (contentType === 'application/pdf' ? '.pdf' : '.zip');
                        }
                    } else {
                        // Fallback filename if Content-Disposition header is missing
                        filename = endpoint + (contentType === 'application/pdf' ? '.pdf' : '.zip');
                    }

                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    displayMessage('Operation completed successfully! Download started.', 'success');

                } else {
                    // Handle JSON response for success (e.g., pdf-to-text, split) or error
                    const result = await response.json();

                    if (!response.ok) {
                        displayMessage(result.error || result.message || `Operation failed (Status: ${response.status})`, 'danger');
                        return;
                    }

                    // Call the specific success handler for this tool
                    if (successCallback) {
                        successCallback(result);
                    } else {
                        // Default success message if no specific callback is provided
                        displayMessage(result.message || 'Operation completed successfully.', 'success');
                    }
                }


            } catch (error) {
                console.error(`Workspace error for ${endpoint}:`, error);
                displayMessage(`An error occurred: ${error.message}`, 'danger');
            }
        };

        if (mergePdfForm) {
            mergePdfForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'merge-pdf', (result) => {
                    if (result.file_path) {
                        displayMessage(`PDFs merged successfully! <a href="${result.file_path}" target="_blank" class="alert-link">Download Merged PDF</a>`, 'success');
                    } else {
                        displayMessage(result.message || 'PDFs merged successfully, but no file path received.', 'success');
                    }
                });
            });
        }

        if (deletePageForm) {
            deletePageForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'delete-pages');
            });
        }

        if (reorderPagesForm) {
            reorderPagesForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'reorder-pages');
            });
        }

        if (rotatePagesForm) {
            rotatePagesForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'rotate-pages');
            });
        }

        if (extractPagesForm) {
            extractPagesForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'extract-pages');
            });
        }

        if (imagesToPdfForm) {
            imagesToPdfForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'images-to-pdf');
            });
        }

        if (pdfToTextForm) {
            pdfToTextForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'pdf-to-text', (result) => {
                    if (result.text) {
                        displayMessage(`Text extracted successfully:<pre>${result.text}</pre>`, 'success');
                    } else {
                        displayMessage(result.message || 'Text extracted successfully, but no text was returned.', 'success');
                    }
                });
            });
        }

        if (addWatermarkForm) {
            addWatermarkForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'add-watermark');
            });
        }

        if (compressPdfForm) {
            compressPdfForm.addEventListener('submit', (e) => {
                handlePdfToolSubmit(e, 'compress-pdf');
            });
        }

        if (splitPdfForm) {
            splitPdfForm.addEventListener('submit', (e) => {
                // The handlePdfToolSubmit will automatically detect the Content-Type (application/zip)
                // and trigger a file download, similar to merge.
                handlePdfToolSubmit(e, 'split-pdf');
            });
        }
    } else {
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const username = loginForm.username.value;
                const password = loginForm.password.value;

                try {
                    const response = await fetch('https://node35.webte.fei.stuba.sk/pdf_app/api/v0/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            "X-Requested-Source": "frontend"
                        },
                        body: JSON.stringify({username, password}),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        return;
                    }

                    // Successful login: API returns 'api_key' and flat user details
                    if (result.api_key) {
                        localStorage.setItem('api_key', result.api_key);

                        // Construct a user object to store, or store individual items
                        const userData = {
                            user_id: result.user_id,
                            username: result.username,
                            role: result.role
                        };
                        localStorage.setItem('user', JSON.stringify(userData));

                        window.location.href = 'dashboard.php';
                    }

                } catch (error) {
                    console.error('Login fetch error:', error);
                }
            });
        }

        if (registerForm) {
            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const username = registerForm.username.value;
                const email = registerForm.email.value;
                const password = registerForm.password.value;

                try {
                    const response = await fetch('https://node35.webte.fei.stuba.sk/pdf_app/api/v0/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            "X-Requested-Source": "frontend"
                        },
                        body: JSON.stringify({username, email, password}),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        return;
                    }

                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);

                } catch (error) {
                    console.error('Registration fetch error:', error);
                }
            });
        }
    }

});