<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Auth.php';
require_once 'classes/Logger.php';
require_once 'classes/GeoLocation.php';
require_once 'pdf_scripts/pdf_functions.php';

// Enable error reporting (useful during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = Database::getInstance()->getConnection();

$auth = new Auth();
$logger = new Logger();
$geoLocation = new GeoLocation();

$method = $_SERVER['REQUEST_METHOD'];
$route = isset($_GET['route']) ? explode('/', $_GET['route']) : [];

// Check if request is made from the frontend
function isFrontendRequest(): bool {
    $requestedSource = $_SERVER['HTTP_X_REQUESTED_SOURCE'] ?? '';
    return $requestedSource === 'frontend';
}

// Formats an exception message from executePythonScript for user-friendly display.
function formatPythonError(string $rawMessage, string $action): string {
    $pythonErrorPrefix = 'Error running Python script: ';
    $pythonOutputPrefix = ' Output: ';
    $defaultUserFriendlyError = 'An error occurred during PDF ' . $action . '.';

    // Check if the error message contains the Python script error part
    if (strpos($rawMessage, $pythonErrorPrefix) !== false) {
        // Extract the part of the message after "Error running Python script: "
        $pythonErrorMessage = substr($rawMessage, strpos($rawMessage, $pythonErrorPrefix) + strlen($pythonErrorPrefix));

        // Further process the Python error to remove "Output: " if present
        if (strpos($pythonErrorMessage, $pythonOutputPrefix) !== false) {
            $pythonErrorMessage = substr($pythonErrorMessage, 0, strpos($pythonErrorMessage, $pythonOutputPrefix));
        }

        // Trim whitespace and quotes
        $pythonErrorMessage = trim($pythonErrorMessage, " \t\n\r\0\x0B\"'");

        // Use the extracted Python error in the user-friendly message
        // Capitalize the first letter of the Python error for better readability
        $pythonErrorMessage = ucfirst($pythonErrorMessage);

        return 'PDF ' . $action . ' failed: ' . $pythonErrorMessage;

    }
    // If the error message didn't match the expected format, return the default
    return $defaultUserFriendlyError;
}

// --- API Key Authentication Middleware ---

// Define which routes are public (do NOT require an API key)
$publicRoutes = ['login', 'register'];

// Get the API key from the 'X-API-Key' header
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

// Check if the requested route is NOT public and if route[0] is set
if (isset($route[0]) && !in_array($route[0], $publicRoutes)) {
    // This route requires authentication - validate the API key
    $authenticatedUserId = $auth->isValidApiKey($apiKey);

    if (!$authenticatedUserId) {
        // API key is missing or invalid - Deny access
        http_response_code(401); // 401 Unauthorized
        echo json_encode(['error' => 'Unauthorized: Missing or invalid API key.']);

        // Log the failed access attempt
        $access_type = isFrontendRequest() ? 'frontend' : 'api';
        $logger->logAction(0, 'unauthorized_access', "Attempted access to route {$route[0]} with invalid API key: " . ($apiKey ? substr($apiKey, 0, 10) . '...' : 'N/A'), $access_type);

        exit; // Stop script execution
    }

    // If the API key is valid, the script continues execution.
    // Set session user_id for consistency, though $authenticatedUserId can be used directly.
    $_SESSION['user_id'] = $authenticatedUserId;
}

// --- End API Key Authentication Middleware ---

switch ($method) {
    // ----- User endpoints -----
    case 'GET':
        if ($route[0] === 'user') {
            if (count($route) == 2 && is_numeric($route[1])) {
                $access_type = isFrontendRequest() ? 'frontend' : 'api';
                $logger->logAction($_SESSION['user_id'], 'get_user', 'User ' . $_SESSION['username'] . ' requested GET user function.', $access_type);

                http_response_code(200);
                echo json_encode($auth->getUserById($route[1]));
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Bad request: Invalid user ID format.']);
            }
            break;
        } elseif ($route[0] === 'history') {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            $filters = [];

            if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
            if (isset($_GET['action'])) $filters['action'] = $_GET['action'];
            if (isset($_GET['access_type'])) $filters['access_type'] = $_GET['access_type'];
            if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

            $history = $logger->getHistory($limit, $offset, $filters);
            $access_type = isFrontendRequest() ? 'frontend' : 'api';
            $logger->logAction($_SESSION['user_id'], 'get_history', 'User ' . $_SESSION['username'] . ' requested GET history function.', $access_type);
            http_response_code(200);
            echo json_encode($history);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;

    case 'POST':
        if ($route[0] === 'login') {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['username'], $data['password'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Missing username or password']);
                exit;
            }

            $username = $data['username'];
            $password = $data['password'];

            try {
                $login_result = $auth->login($username, $password);
                if ($login_result) {
                    $loggedInUser = $auth->getUserById($_SESSION['user_id']);
                    if ($loggedInUser) {
                        http_response_code(200);
                        $response_data = ([
                            'message' => 'Login successful.',
                            'user_id' => (int)$_SESSION['user_id'],
                            'username' => $_SESSION['username'],
                            'role' => $_SESSION['role'],
                            'api_key' => $loggedInUser['api_key']
                        ]);
                        echo json_encode($response_data);

                        $access_type = isFrontendRequest() ? 'frontend' : 'api';
                        $logger->logAction($_SESSION['user_id'], 'login', 'User ' . $_SESSION['username'] . ' logged in.', $access_type);

                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Login successful, but failed to retrieve user details.']);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid username or password.']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Exception during login: ' . $e->getMessage()]);
            }
        } elseif ($route[0] === 'register') {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['username'], $data['password'], $data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields: username, password, email.']);
                exit;
            }

            $username = $data['username'];
            $password = $data['password'];
            $email = $data['email'];

            if (empty($username) || empty($password) || empty($email)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username, password, and email cannot be empty.']);
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format.']);
                exit;
            }

            $user_id = $auth->register($username, $password, $email);

            if ($user_id) {
                $newUser = $auth->getUserById($user_id);
                if ($newUser) {
                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($user_id, 'register', 'User ' . $_SESSION['username'] . ' registered.', $access_type);

                    http_response_code(200);
                    $response_data = ([
                        'message' => 'User registered successfully.',
                        'user_id' => (int)$user_id,
                        'username' => $newUser['username'],
                        'email' => $newUser['email'],
                        'api_key' => $newUser['api_key']
                    ]);
                    echo json_encode($response_data);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'User registered but could not fetch details.']);
                }
            }
        } elseif ($route[0] === 'logout') {
            $user_id = $_SESSION['user_id'] ?? null;

            if (!$user_id) {
                http_response_code(400);
                echo json_encode(['message' => 'User not logged in']);
                exit;
            }
            $auth->logout();

            $access_type = isFrontendRequest() ? 'frontend' : 'api';
            if ($access_type === 'api') {
                $result = $auth->getUserByApiKey($apiKey);
                $username = $result['username'];
            } else {
                $username = $_SESSION['username'];
            }

            $logger->logAction($user_id, 'logout', 'User ' . $username . ' logged out', $access_type);
            http_response_code(200);
            echo json_encode(['message' => 'Logout successful']);
        } elseif ($route[0] === 'regenerate-api-key') {
            // Get the authenticated user ID from the session set by the middleware
            $authenticatedUserId = $_SESSION['user_id'];
            $result = $auth->getUserById($authenticatedUserId);
            $username = $result['username'];

            // Regenerate the API key for the authenticated user
            $new_api_key = $auth->regenerateApiKey($authenticatedUserId);

            if ($new_api_key) {
                $access_type = isFrontendRequest() ? 'frontend' : 'api';
                $logger->logAction($authenticatedUserId, 'regenerate-api-key', "User {$username} regenerated their API key.", $access_type);

                http_response_code(200);
                echo json_encode([
                    'message' => "API key regenerated successfully. Please use the new API key for future requests.",
                    'api_key' => $new_api_key
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => "Failed to regenerate API key."]);
            }
            exit;

        }
        // ----- PDF endpoints -----
        elseif ($route[0] === 'merge-pdf') {
            try {
                if (!isset($_FILES['files']) || !is_array($_FILES['files']['name']) || empty($_FILES['files']['name'][0])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No files were uploaded.']);
                    exit;
                }

                $files = $_FILES['files'];
                $uploadedFiles = [];
                foreach ($files['error'] as $key => $error) {
                    if ($error == UPLOAD_ERR_OK) {
                        $uploadedFiles[] = [
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key],
                        ];
                    } else {
                        $phpFileUploadErrors = [
                            UPLOAD_ERR_OK => 'No errors.',
                            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                        ];
                        $errorMessage = $phpFileUploadErrors[$error] ?? 'Unknown upload error.';

                        http_response_code(400);
                        echo json_encode(['error' => "Error uploading file '{$files['name'][$key]}': " . $errorMessage]);
                        exit;
                    }
                }

                if (count($uploadedFiles) < 2) {
                    http_response_code(400);
                    echo json_encode(['error' => "Please upload at least two files to merge."]);
                    exit;
                }

                $mergedFile = handlePdfMerge($uploadedFiles);

                if ($mergedFile) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="merged_document.pdf"');
                    header('Content-Length: ' . filesize($mergedFile));

                    readfile($mergedFile);
                    unlink($mergedFile);

                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($_SESSION['user_id'], 'pdf_merge', 'User ' . $_SESSION['username'] . ' merged PDF files', $access_type);

                    exit;

                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to merge PDFs. An internal error occurred.']);
                }

            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'merging');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        } elseif ($route[0] === 'delete-pages') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];

                if (!isset($_POST['pages']) || empty($_POST['pages'])) {
                    throw new Exception("Missing or empty 'pages' parameter.");
                }
                $pages = $_POST['pages'];

                $outputFile = tempnam(sys_get_temp_dir(), 'deleted_pdf_output_');
                if ($outputFile === false) {
                    throw new Exception("Failed to create temporary output file.");
                }

                $resultFile = handlePdfDeletePages($file, $outputFile, $pages);

                if ($resultFile) {
                    if (file_exists($resultFile) && is_readable($resultFile)) {
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment; filename="deleted_pages.pdf"');
                        header('Content-Length: ' . filesize($resultFile));

                        readfile($resultFile);
                        unlink($resultFile);

                        $access_type = isFrontendRequest() ? 'frontend' : 'api';
                        $logger->logAction($_SESSION['user_id'], 'pdf_delete_pages', 'User ' . $_SESSION['username'] . ' deleted pages from PDF', $access_type);

                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Processed file not found or not readable.']);
                        if (file_exists($resultFile)) {
                            @unlink($resultFile);
                        }
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to delete PDF pages. An internal error occurred.']);
                    if (file_exists($outputFile)) {
                        @unlink($outputFile);
                    }
                }
            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'deleting pages');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
                if (isset($outputFile) && file_exists($outputFile)) {
                    @unlink($outputFile);
                }
            }
        } elseif ($route[0] === 'reorder-pages') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];
                if (!isset($_POST['order'])) {
                    throw new Exception("Missing 'order' parameter.");
                }
                $order = $_POST['order'];

                if (!preg_match('/^(\d+,)*\d+$/', $order)) {
                    throw new Exception("Invalid 'order' format. Expected comma-separated page numbers.");
                }

                $outputFile = handlePdfReorderPages($file, $order);
                if($outputFile){
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="reordered_pages.pdf"');
                    readfile($outputFile);
                    unlink($outputFile);

                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($_SESSION['user_id'], 'pdf_reorder_pages', 'User ' . $_SESSION['username'] . ' reordered PDF pages', $access_type);

                    exit;
                }
                else{
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to reorder PDF pages']);
                }

            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'reordering pages');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        }  elseif ($route[0] === 'rotate-pages') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];

                if (!isset($_POST['rotation']) || !is_numeric($_POST['rotation'])) {
                    throw new Exception("Missing or invalid 'rotation' parameter.");
                }
                $rotation = (int)$_POST['rotation'];

                $outputFile = tempnam(sys_get_temp_dir(), 'rotated_pdf_output_');
                if ($outputFile === false) {
                    throw new Exception("Failed to create temporary output file.");
                }

                $resultFile = handlePdfRotate($file, $outputFile, $rotation);

                if ($resultFile) {
                    if (file_exists($resultFile) && is_readable($resultFile)) {
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment; filename="rotated_pdf.pdf"');
                        header('Content-Length: ' . filesize($resultFile));

                        readfile($resultFile);
                        unlink($resultFile);

                        $access_type = isFrontendRequest() ? 'frontend' : 'api';
                        $logger->logAction($_SESSION['user_id'], 'pdf_rotate_pages', 'User ' . $_SESSION['username'] . ' rotated PDF pages', $access_type);

                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Processed file not found or not readable.']);
                        if (file_exists($resultFile)) {
                            @unlink($resultFile);
                        }
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to rotate PDF. An internal error occurred.']);
                    if (file_exists($outputFile)) {
                        @unlink($outputFile);
                    }
                }
            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'rotating pages');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
                if (isset($outputFile) && file_exists($outputFile)) {
                    @unlink($outputFile);
                }
            }
        } elseif ($route[0] === 'extract-pages') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];

                if (!isset($_POST['pages']) || empty($_POST['pages'])) {
                    throw new Exception("Missing or empty 'pages' parameter.");
                }
                $pages = $_POST['pages'];

                $outputFile = tempnam(sys_get_temp_dir(), 'extracted_pdf_output_');
                if ($outputFile === false) {
                    throw new Exception("Failed to create temporary output file.");
                }

                $resultFile = handlePdfExtractPages($file, $outputFile, $pages);

                if ($resultFile) {
                    if (file_exists($resultFile) && is_readable($resultFile)) {
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment; filename="extracted_pages.pdf"');
                        header('Content-Length: ' . filesize($resultFile));

                        readfile($resultFile);
                        unlink($resultFile);

                        $access_type = isFrontendRequest() ? 'frontend' : 'api';
                        $logger->logAction($_SESSION['user_id'], 'pdf_extract_pages', 'User ' . $_SESSION['username'] . ' extracted PDF pages', $access_type);

                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Processed file not found or not readable.']);
                        if (file_exists($resultFile)) {
                            @unlink($resultFile);
                        }
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to extract PDF pages. An internal error occurred.']);
                    if (file_exists($outputFile)) {
                        @unlink($outputFile);
                    }
                }
            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'extracting pages');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
                if (isset($outputFile) && file_exists($outputFile)) {
                    @unlink($outputFile);
                }
            }
        } elseif ($route[0] === 'add-watermark') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];
                if (!isset($_POST['text']) || empty($_POST['text'])) {
                    throw new Exception("Missing or empty 'text' parameter for watermark.");
                }
                $watermarkText = $_POST['text'];

                $outputFile = tempnam(sys_get_temp_dir(), 'watermarked_pdf_');
                $resultFile = handlePdfAddWatermark($file, $watermarkText, $outputFile);

                if($resultFile){
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="watermarked_pdf.pdf"');
                    readfile($resultFile);
                    unlink($resultFile);

                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($_SESSION['user_id'], 'pdf_add_watermark', 'User ' . $_SESSION['username'] . ' added watermark to a PDF', $access_type);

                    exit;
                }
                else{
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to add watermark to PDF']);
                }
            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'adding watermark');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        } elseif ($route[0] === 'compress-pdf') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];

                $outputFile = tempnam(sys_get_temp_dir(), 'compressed_pdf_output_');
                if ($outputFile === false) {
                    throw new Exception("Failed to create temporary output file.");
                }

                $resultFile = handlePdfCompress($file, $outputFile);

                if ($resultFile) {
                    if (file_exists($resultFile) && is_readable($resultFile)) {
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment; filename="compressed_pdf.pdf"');
                        header('Content-Length: ' . filesize($resultFile));

                        readfile($resultFile);
                        unlink($resultFile);

                        $access_type = isFrontendRequest() ? 'frontend' : 'api';
                        $logger->logAction($_SESSION['user_id'], 'pdf_compress', 'User ' . $_SESSION['username'] . ' compressed a PDF', $access_type);

                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Processed file not found or not readable.']);
                        if (file_exists($resultFile)) {
                            @unlink($resultFile);
                        }
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to compress PDF. An internal error occurred.']);
                    if (file_exists($outputFile)) {
                        @unlink($outputFile);
                    }
                }
            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'compressing');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
                if (isset($outputFile) && file_exists($outputFile)) {
                    @unlink($outputFile);
                }
            }
        }
        elseif ($route[0] === 'images-to-pdf') {
            try {
                if (!isset($_FILES['images']) || !is_array($_FILES['images']['tmp_name']) || empty($_FILES['images']['name'][0])) {
                    throw new Exception("No images were uploaded.");
                }
                $images = [];
                if (isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
                    foreach($_FILES['images']['name'] as $key => $name) {
                        // Also check for upload errors for each file here if not done already above
                        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                            // Handle individual file upload errors if necessary, similar to merge-pdf
                            throw new Exception("Error uploading image file '{$name}': Code " . $_FILES['images']['error'][$key]);
                        }
                            $images[] = [
                                'name' => $name,
                                'type' => $_FILES['images']['type'][$key],
                                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                                'error' => $_FILES['images']['error'][$key],
                                'size' => $_FILES['images']['size'][$key],
                            ];
                        }
                    } else {
                        throw new Exception("Invalid format for uploaded images.");
                    }
                $outputFile = handleImagesToPdf($images);
                if($outputFile){
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="images_converted.pdf"');
                    readfile($outputFile);
                    unlink($outputFile);

                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($_SESSION['user_id'], 'pdf_convert_images', 'User ' . $_SESSION['username'] . ' converted images to a PDF', $access_type);

                    exit;
                }
                else{
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to convert images to PDF']);
                }

            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'converting images to PDF');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        } elseif ($route[0] === 'split-pdf') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No PDF file was uploaded.");
                }
                $file = $_FILES['file'];

                if (!isset($_POST['split_page']) || !is_numeric($_POST['split_page'])) {
                    throw new Exception("Missing or invalid 'split_page' parameter.");
                }
                $splitPageNumber = (int)$_POST['split_page'];

                $outputZipFile = handlePdfSplit($file, $splitPageNumber);

                if($outputZipFile && file_exists($outputZipFile)){
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="split_pdf.zip"');
                    header('Content-Length: ' . filesize($outputZipFile));

                    readfile($outputZipFile);
                    unlink($outputZipFile);

                    $access_type = isFrontendRequest() ? 'frontend' : 'api';
                    $logger->logAction($_SESSION['user_id'], 'pdf_split', 'User ' . $_SESSION['username'] . ' split a PDF', $access_type);

                    exit;

                }
                else{
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to split or create ZIP file.']);
                }

            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'splitting');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        } elseif ($route[0] === 'pdf-to-text') {
            try {
                if (!isset($_FILES['file'])) {
                    throw new Exception("No file was uploaded.");
                }
                $file = $_FILES['file'];

                $text = handlePdfToText($file);

                http_response_code(200);
                echo json_encode(['message' => 'PDF converted to text successfully', 'text' => $text]);
                exit;

            } catch (Exception $e) {
                $userFriendlyError = formatPythonError($e->getMessage(), 'converting to text');
                http_response_code(500);
                echo json_encode(['error' => $userFriendlyError]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            exit;
        }
        break;

    case 'PUT':
        // Handle PUT requests
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;

    case 'DELETE':
        if ($route[0] === 'delete-all-history') {
            $user_id = $_SESSION['user_id'] ?? null;

            if (!is_admin()) { // Checks the user's role
                http_response_code(403); // 403 Forbidden for insufficient permissions
                echo json_encode(['message' => 'Forbidden: User is not admin']);
                exit;
            }

            try {
                $logger->deleteAllHistory();
                $logger->logAction($_SESSION['user_id'], 'delete_history', 'Deleted all activity history', 'api');

                http_response_code(200);
                echo json_encode(['message' => 'History deleted successfully']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Exception during history deletion: ' . $e->getMessage()]);
            }
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            exit;
        }

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
}