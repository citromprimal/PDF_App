<?php

session_start();

require_once '../vendor/autoload.php';
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';

use Dompdf\Dompdf;

$auth = new Auth();

// Check if the user is logged in.  If not, redirect them to the login page.
if (!$auth->checkSession()) {
    echo "checkSession() returned false. Redirecting...<br>";
    redirect('login.php', 'Please log in to access the dashboard.', 'error');
    exit;
}

// Get user data
$user = $auth->getUserById($_SESSION['user_id']);
if (!$user) {
    echo "getUserById() returned false.  User data not found.<br>";
    redirect('login.php', 'User data not found. Please log in again.', 'error');
    exit;
}

$dompdf = new Dompdf();

// Build HTML dynamically with English content and detailed API info
$manualHtml = '<html><head><meta charset="UTF-8"><title>PDF App User Manual</title>';
// Basic styling for the PDF
$manualHtml .= '<style>
                    body { font-family: "DejaVu Sans", sans-serif; line-height: 1.6; margin: 20mm; }
                    h1, h2, h3, h4 { color: #333; margin-top: 1em; margin-bottom: 0.5em; }
                    h1 { font-size: 24pt; text-align: center; }
                    h2 { font-size: 18pt; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                    h3 { font-size: 14pt; margin-top: 1em; }
                    h4 { font-size: 12pt; margin-top: 1em; margin-bottom: 0.5em; font-weight: bold;}
                    code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 4px; font-family: Consolas, Monaco, "Andale Mono", "Ubuntu Mono", monospace; }
                    pre code { display: block; padding: 10px; overflow-x: auto; }
                    ul { margin-bottom: 1em; }
                    li { margin-bottom: 0.5em; }
                    strong { font-weight: bold; }
                    .api-param-list li { margin-bottom: 0.2em; }
                </style>';
$manualHtml .= '</head><body>';

// Introduction Section
$manualHtml .= '<h1>PDF App User Manual</h1>';
$manualHtml .= '<h2>1. Introduction</h2>';
$manualHtml .= '<p>Welcome to the PDF App User Manual. This document provides a comprehensive guide on how to use both the web-based frontend interface and the powerful API endpoints of the PDF App. Whether you\'re a standard user performing common PDF operations or a developer integrating our services into your own applications, this manual will help you get started.</p>';

// Frontend Usage Section (General User)
$manualHtml .= '<h2>2. Frontend Usage (General User)</h2>';
$manualHtml .= '<p>The frontend provides a user-friendly interface to access the core functionalities of the PDF App.</p>';

// Account Management Subsection (Frontend)
$manualHtml .= '<h3>2.1 Account Management</h3>';
$manualHtml .= '<p><b>Registration:</b> To create a new account, navigate to the registration page. You will need to provide a unique username, a valid email address, and a secure password.</p>';
$manualHtml .= '<p><b>Login:</b> If you already have an account, go to the login page and enter your username and password. Successful login will grant you access to the PDF manipulation tools and your API key.</p>';
$manualHtml .= '<p><b>Logout:</b> To securely end your session, use the logout function.</p>';

// PDF Operations Subsection (Frontend)
$manualHtml .= '<h3>2.2 PDF Operations (Frontend)</h3>'; // Clarify this is frontend usage
$manualHtml .= '<p>Once logged in, you can perform various operations on your PDF files:</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>Merge PDFs:</b> Combine two or more PDF files into a single document.</li>';
$manualHtml .= '<li><b>Delete Pages:</b> Remove specific pages from a PDF document.</li>';
$manualHtml .= '<li><b>Reorder Pages:</b> Change the order of pages within a PDF.</li>';
$manualHtml .= '<li><b>Rotate Pages:</b> Rotate pages in a PDF by a specified angle (90, 180, or 270 degrees).</li>';
$manualHtml .= '<li><b>Extract Pages:</b> Pull out selected pages from a PDF to create a new document.</li>';
$manualHtml .= '<li><b>Add Watermark:</b> Add a text watermark to your PDF pages.</li>';
$manualHtml .= '<li><b>Compress PDF:</b> Reduce the file size of a PDF document.</li>';
$manualHtml .= '<li><b>Images to PDF:</b> Convert one or more image files into a PDF document.</li>';
$manualHtml .= '<li><b>Split PDF:</b> Divide a single PDF file into two separate PDFs at a specified page.</li>';
$manualHtml .= '<li><b>PDF to Text:</b> Extract the text content from a PDF file.</li>';
$manualHtml .= '</ul>';

// Admin Panel Frontend Usage Section (NEW SECTION)
$manualHtml .= '<h2>3. Admin Panel Frontend Usage</h2>'; // Updated section number
$manualHtml .= '<p>The Admin Panel provides administrators with tools to monitor user activity and manage the application.</p>';

// Admin Login Subsection
$manualHtml .= '<h3>3.1 Admin Login</h3>';
$manualHtml .= '<p>Access the Admin Panel login page (typically located at a specific URL like <code>/admin/login.php</code>). You must log in with an account that has the <strong>Admin role</strong>.</p>';
$manualHtml .= '<p>Enter your Admin username and password to gain access to the Admin Dashboard.</p>';

// Admin Dashboard Subsection
$manualHtml .= '<h3>3.2 Admin Dashboard</h3>';
$manualHtml .= '<p>After logging in, you will be directed to the Admin Dashboard (<code>/admin/index.php</code>). This page provides an overview of key application statistics:</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>Total Users:</b> The total number of registered users.</li>';
$manualHtml .= '<li><b>Total Actions:</b> The total number of logged user activities.</li>';
$manualHtml .= '<li><b>Actions Today:</b> The number of user activities recorded on the current day.</li>';
$manualHtml .= '<li><b>API Usage vs Frontend Usage:</b> A visual representation (e.g., a chart) showing the proportion of actions performed via the API versus the frontend.</li>';
$manualHtml .= '<li><b>Recent Activity:</b> A list of the most recent user actions. You can click "View All Activity" to go to the full history page.</li>';
$manualHtml .= '</ul>';
$manualHtml .= '<p>The dashboard also includes a sidebar for navigation to other admin sections (like User Activity History).</p>';

// User Activity History Subsection
$manualHtml .= '<h3>3.3 User Activity History</h3>';
$manualHtml .= '<p>Navigate to the User Activity History page (<code>/admin/history.php</code>) from the Admin Dashboard sidebar. This page allows you to view a detailed log of all user actions.</p>';
$manualHtml .= '<p>Key features on this page include:</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>Filtering:</b> Use the filter form at the top to narrow down the history records. You can filter by:';
$manualHtml .= '<ul>';
$manualHtml .= '<li>User</li>';
$manualHtml .= '<li>Action Type (e.g., login, pdf_merge)</li>';
$manualHtml .= '<li>Access Type (Frontend or API)</li>';
$manualHtml .= '<li>Date Range (From and To)</li>';
$manualHtml .= '</ul>';
$manualHtml .= '<li><b>Pagination:</b> History records are displayed in pages. Use the pagination controls at the bottom to navigate through the records.</li>';
$manualHtml .= '<li><b>Export to CSV:</b> Click the "Export to CSV" button to download the currently filtered history data as a CSV file (<code>/admin/export_csv.php</code>).</li>';
$manualHtml .= '<li><b>Delete All History:</b> Click the "Delete All History" button to permanently remove all activity logs. A confirmation modal will appear before deletion (this action is handled by <code>/admin/delete_history.php</code>).</li>';
$manualHtml .= '</ul>';
$manualHtml .= '<p>Each history entry typically shows the User, Action, Details, Access Type, Location (City, Country), and Timestamp.</p>';


// API Usage Section (Original Section 3, now 4)
$manualHtml .= '<h2>4. API Usage</h2>'; // Updated section number
$manualHtml .= '<p>The PDF App provides a RESTful API that allows developers to integrate its PDF processing capabilities into their own applications.</p>';

// API Base URL Subsection
$manualHtml .= '<h3>4.1 API Base URL</h3>'; // Updated section number
$manualHtml .= '<p>The base URL for all API endpoints is: <code>https://node35.webte.fei.stuba.sk/pdf_app/api/v0</code></p>';

// Authentication Subsection
$manualHtml .= '<h3>4.2 Authentication</h3>'; // Updated section number
$manualHtml .= '<p>API requests are authenticated using an <strong>API Key</strong>.</p>';
$manualHtml .= '<p>Obtain your API Key by logging into the frontend interface or upon successful registration.</p>';
$manualHtml .= '<p>Include your API Key in the <code>X-API-Key</code> header of every API request that requires authentication.</p>';

// Endpoints Subsection (Detailed API Endpoint Information)
$manualHtml .= '<h3>4.3 Endpoints</h3>'; // Updated section number
$manualHtml .= '<p>Here is a summary of the available API endpoints:</p>';

// --- Add Detailed API Endpoint Descriptions (Content remains the same as previous update) ---

// Login Endpoint
$manualHtml .= '<h4>POST /login</h4>';
$manualHtml .= '<p><strong>Summary:</strong> User login</p>';
$manualHtml .= '<p><strong>Description:</strong> Authenticates a user using username and password. Returns user details and API key on success.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> None required.</p>';
$manualHtml .= '<p><strong>Request Body:</strong> JSON object with:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>username</code> (string)</li><li><code>password</code> (string)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code>, <code>user_id</code>, <code>username</code>, <code>role</code>, <code>api_key</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Register Endpoint
$manualHtml .= '<h4>POST /register</h4>';
$manualHtml .= '<p><strong>Summary:</strong> User registration</p>';
$manualHtml .= '<p><strong>Description:</strong> Registers a new user. Requires username, email, and password. Returns new user details and API key on success.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> None required.</p>';
$manualHtml .= '<p><strong>Request Body:</strong> JSON object with:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>username</code> (string)</li><li><code>email</code> (string, format: email)</li><li><code>password</code> (string)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code>, <code>user_id</code>, <code>username</code>, <code>email</code>, <code>api_key</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 500 (Internal Server Error).</p>';

// Logout Endpoint
$manualHtml .= '<h4>POST /logout</h4>';
$manualHtml .= '<p><strong>Summary:</strong> User logout</p>';
$manualHtml .= '<p><strong>Description:</strong> Invalidates the user\'s session. Requires API Key authentication.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> None required.</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized).</p>';

// Regenerate API Key Endpoint
$manualHtml .= '<h4>POST /regenerate-api-key</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Regenerate API key</p>';
$manualHtml .= '<p><strong>Description:</strong> Regenerates the API key for the user who authenticates the request using their current API key. The new API key is returned.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> None required (regenerates key for the authenticated user).</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code> and <code>api_key</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Get User Data Endpoint
$manualHtml .= '<h4>GET /user/{user_id}</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Get user data by ID</p>';
$manualHtml .= '<p><strong>Description:</strong> Retrieves user data by ID. Requires API Key authentication and potentially admin role depending on target user_id.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Path Parameters:</strong></p>';
$manualHtml .= '<ul class="api-param-list"><li><code>user_id</code> (integer) - ID of the user to retrieve.</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with user details (<code>user_id</code>, <code>username</code>, <code>email</code>, <code>role</code>, <code>api_key</code>, <code>created_at</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 401 (Unauthorized), 403 (Forbidden), 404 (User not found).</p>';

// Get History Endpoint
$manualHtml .= '<h4>GET /history</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Get user history</p>';
$manualHtml .= '<p><strong>Description:</strong> Retrieves user activity history with optional filters. Requires API Key authentication. By default shows authenticated user\'s history, admins can filter by user_id.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Query Parameters:</strong></p>';
$manualHtml .= '<ul class="api-param-list">';
$manualHtml .= '<li><code>limit</code> (integer, optional): Maximum number of entries (default: 100).</li>';
$manualHtml .= '<li><code>offset</code> (integer, optional): Offset for pagination (default: 0).</li>';
$manualHtml .= '<li><code>user_id</code> (integer, optional): Filter by User ID (Admin only).</li>';
$manualHtml .= '<li><code>action</code> (string, optional): Filter by action type.</li>';
$manualHtml .= '<li><code>access_type</code> (string, optional): Filter by access type (<code>frontend</code> or <code>api</code>).</li>';
$manualHtml .= '<li><code>date_from</code> (string, optional, YYYY-MM-DD): Filter by date from.</li>';
$manualHtml .= '<li><code>date_to</code> (string, optional, YYYY-MM-DD): Filter by date to.</li>';
$manualHtml .= '</ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON array of history entries.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 403 (Forbidden), 500 (Internal Server Error).</p>';

// Delete All History Endpoint
$manualHtml .= '<h4>DELETE /delete-all-history</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Delete all user history</p>';
$manualHtml .= '<p><strong>Description:</strong> Deletes all activity history logs. Requires API Key authentication and requires <strong>Admin role</strong>.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header) and requires <strong>Admin role</strong>.</p>';
$manualHtml .= '<p><strong>Request Body:</strong> None required.</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 401 (Unauthorized), 403 (Forbidden), 500 (Internal Server Error).</p>';


// PDF Methods Endpoints (Detailed API Endpoint Information)
$manualHtml .= '<h2>5. PDF Operations (API)</h2>'; // Updated section number and clarified API usage

// Merge PDFs Endpoint
$manualHtml .= '<h4>POST /merge-pdf</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Merge PDFs</p>';
$manualHtml .= '<p><strong>Description:</strong> Merges multiple PDF files into a single PDF. Returns the merged PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with a field named <code>files[]</code> (array of binary PDF files, min 2).</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the merged PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Delete Pages Endpoint
$manualHtml .= '<h4>POST /delete-pages</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Delete Pages from PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Deletes specified pages from a PDF file. Returns the modified PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>pages</code> (string, comma-separated page numbers/ranges, e.g., "1,3,5-7". 1-based indexing)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the modified PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Reorder Pages Endpoint
$manualHtml .= '<h4>POST /reorder-pages</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Reorder PDF Pages</p>';
$manualHtml .= '<p><strong>Description:</strong> Reorders the pages of a PDF file according to a specified order. Returns the modified PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>order</code> (string, comma-separated list of page numbers in the desired order, e.g., "3,1,2,4". 1-based indexing)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the modified PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Rotate Pages Endpoint
$manualHtml .= '<h4>POST /rotate-pages</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Rotate PDF Pages</p>';
$manualHtml .= '<p><strong>Description:</strong> Rotates all pages of a PDF file by a specified angle. Returns the modified PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>rotation</code> (integer, angle in degrees: 90, 180, or 270)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the modified PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Extract Pages Endpoint
$manualHtml .= '<h4>POST /extract-pages</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Extract Pages from PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Extracts specified pages from a PDF file into a single new PDF. Returns the new PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>pages</code> (string, comma-separated page numbers/ranges, e.g., "1,3,5-7". 1-based indexing)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the new PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Add Watermark Endpoint
$manualHtml .= '<h4>POST /add-watermark</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Add Text Watermark to PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Adds a text watermark to a PDF file. Returns the modified PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>text</code> (string, the watermark text)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the modified PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Compress PDF Endpoint
$manualHtml .= '<h4>POST /compress-pdf</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Compress PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Compresses a PDF file. Returns the compressed PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with a field named <code>file</code> (binary PDF).</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the compressed PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Images to PDF Endpoint
$manualHtml .= '<h4>POST /images-to-pdf</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Convert Images to PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Converts multiple image files into a single PDF. Returns the resulting PDF file as a download.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with a field named <code>images[]</code> (array of binary image files, min 1).</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns the resulting PDF file as a download (<code>application/pdf</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// Split PDF Endpoint
$manualHtml .= '<h4>POST /split-pdf</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Split PDF</p>';
$manualHtml .= '<p><strong>Description:</strong> Splits a PDF file into two parts at a specified page number and returns a ZIP archive containing the two resulting PDFs.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with fields:</p>';
$manualHtml .= '<ul class="api-param-list"><li><code>file</code> (binary PDF)</li><li><code>split_page</code> (integer, the 1-based page number to split after. The first output will be pages 1 to split_page, the second will be split_page + 1 to the end)</li></ul>';
$manualHtml .= '<p><strong>Success Response (200):</strong> Returns a ZIP file containing the two split PDFs as a download (<code>application/zip</code>).</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';

// PDF to Text Endpoint
$manualHtml .= '<h4>POST /pdf-to-text</h4>';
$manualHtml .= '<p><strong>Summary:</strong> Convert PDF to Text</p>';
$manualHtml .= '<p><strong>Description:</strong> Extracts text from a PDF file. Returns the extracted text in a JSON response.</p>';
$manualHtml .= '<p><strong>Authentication:</strong> Required (API Key in <code>X-API-Key</code> header).</p>';
$manualHtml .= '<p><strong>Request Body:</strong> <code>multipart/form-data</code> with a field named <code>file</code> (binary PDF).</p>';
$manualHtml .= '<p><strong>Success Response (200):</strong> JSON object with <code>message</code> and <code>text</code>.</p>';
$manualHtml .= '<p><strong>Errors:</strong> 400 (Bad Request), 401 (Unauthorized), 500 (Internal Server Error).</p>';


// PDF Export Implementation Section (Optional - you might remove this from the public manual)
$manualHtml .= '<h2>6. Dynamic PDF Export Implementation (for Developers)</h2>'; // Updated section number
$manualHtml .= '<p>This section describes how this PDF manual is dynamically generated. You can adapt these steps if you need to generate similar PDF documents programmatically.</p>';
$manualHtml .= '<p>To dynamically export content as a PDF from a PHP backend, you typically use a PHP PDF generation library like Dompdf.</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>Choose a PHP PDF Library:</b> Libraries like Dompdf, TCPDF, or FPDF can be used. Dompdf is used for this manual as it converts HTML/CSS to PDF.</li>';
$manualHtml .= '<li><b>Install the Library:</b> Use Composer (e.g., <code>composer require dompdf/dompdf</code>).</li>';
$manualHtml .= '<li><b>Create a PHP Endpoint:</b> A script (like this <code>user_manual.php</code>) is needed to handle the request for the PDF.</li>';
$manualHtml .= '<li><b>Generate the Content:</b> Build the content as an HTML string within the PHP script. This can be hardcoded or pulled from a database/file, potentially incorporating dynamic data.</li>';
$manualHtml .= '<li><b>Use the PDF Library to Generate the PDF:</b> Instantiate the library, load the HTML content, render it, and stream the output with appropriate headers for download. Ensure correct character encoding (UTF-8) and specify a font that supports the required characters.</li>';
$manualHtml .= '</ul>';



$manualHtml .= '</body></html>';

// Load HTML into Dompdf
$dompdf->loadHtml($manualHtml);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();
// Output the generated PDF to Browser
// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="user_manual.pdf"');
// Calculate content length - Dompdf's output method with true returns the string
$pdfOutput = $dompdf->output(); // Get the raw PDF output string
header('Content-Length: ' . strlen($pdfOutput)); // Use strlen for string length

// Stream the PDF content
echo $pdfOutput; // Output the PDF string directly

// Exit to prevent any further output
exit;
?>
