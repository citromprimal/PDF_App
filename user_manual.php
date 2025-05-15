<?php

require_once '../vendor/autoload.php';
require_once 'backend/manual_content.php';

use Dompdf\Dompdf;

$auth = new Auth();

// --- Language Selection ---
// Get the requested language from a query parameter, e.g., ?lang=sk
$lang = $_GET['lang'] ?? 'en'; // Default to English if no lang parameter is set

// Validate the language to ensure it's supported
$supportedLanguages = ['en', 'sk'];
if (!in_array($lang, $supportedLanguages)) {
    $lang = 'en'; // Fallback to default if language is not supported
}

// --- Get Content for the Selected Language ---
$manualContent = require 'backend/manual_content.php'; // Load the content array

// --- Generate PDF with Selected Language Content ---
$dompdf = new Dompdf();

// Example: Build HTML dynamically using the selected language content
$manualHtml = '<html><head><meta charset="UTF-8"></head><body>';
$manualHtml .= '<h1>' . $manualContent['title'][$lang] . '</h1>';

// Introduction Section
$manualHtml .= '<h2>' . $manualContent['section_introduction_title'][$lang] . '</h2>';
$manualHtml .= '<p>' . $manualContent['section_introduction_text'][$lang] . '</p>';

// Frontend Section
$manualHtml .= '<h2>' . $manualContent['section_frontend_title'][$lang] . '</h2>';
$manualHtml .= '<p>' . $manualContent['section_frontend_intro'][$lang] . '</p>';

// Account Management Subsection
$manualHtml .= '<h3>' . $manualContent['subsection_account_title'][$lang] . '</h3>';
$manualHtml .= '<p><b>' . $manualContent['text_registration'][$lang] . '</b> ' . $manualContent['desc_registration'][$lang] . '</p>';
$manualHtml .= '<p><b>' . $manualContent['text_login'][$lang] . '</b> ' . $manualContent['desc_login'][$lang] . '</p>';
$manualHtml .= '<p><b>' . $manualContent['text_logout'][$lang] . '</b> ' . $manualContent['desc_logout'][$lang] . '</p>';


// PDF Operations Subsection
$manualHtml .= '<h3>' . $manualContent['subsection_pdf_ops_title'][$lang] . '</h3>';
$manualHtml .= '<p>' . $manualContent['desc_pdf_ops_intro'][$lang] . '</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>' . $manualContent['op_merge_pdfs'][$lang] . '</b> ' . $manualContent['desc_merge_pdfs'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_delete_pages'][$lang] . '</b> ' . $manualContent['desc_delete_pages'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_reorder_pages'][$lang] . '</b> ' . $manualContent['desc_reorder_pages'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_rotate_pages'][$lang] . '</b> ' . $manualContent['desc_rotate_pages'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_extract_pages'][$lang] . '</b> ' . $manualContent['desc_extract_pages'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_add_watermark'][$lang] . '</b> ' . $manualContent['desc_add_watermark'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_compress_pdf'][$lang] . '</b> ' . $manualContent['desc_compress_pdf'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_images_to_pdf'][$lang] . '</b> ' . $manualContent['desc_images_to_pdf'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_split_pdf'][$lang] . '</b> ' . $manualContent['desc_split_pdf'][$lang] . '</li>';
$manualHtml .= '<li><b>' . $manualContent['op_pdf_to_text'][$lang] . '</b> ' . $manualContent['desc_pdf_to_text'][$lang] . '</li>';

$manualHtml .= '</ul>';

// API Usage Section
$manualHtml .= '<h2>' . $manualContent['section_api_title'][$lang] . '</h2>';
$manualHtml .= '<p>' . $manualContent['section_api_intro'][$lang] . '</p>';

// API Base URL Subsection
$manualHtml .= '<h3>' . $manualContent['subsection_api_base_url_title'][$lang] . '</h3>';
$manualHtml .= '<p>' . $manualContent['desc_api_base_url'][$lang] . ' <code>https://node35.webte.fei.stuba.sk/pdf_app/api/v0</code></p>'; // Keep the URL static

// Authentication Subsection
$manualHtml .= '<h3>' . $manualContent['subsection_authentication_title'][$lang] . '</h3>';
$manualHtml .= '<p><b>' . $manualContent['desc_authentication_intro'][$lang] . '</b> ' . $manualContent['desc_authentication_obtain'][$lang] . ' ' . $manualContent['desc_authentication_header'][$lang] . '</p>';

// Endpoints Subsection
$manualHtml .= '<h3>' . $manualContent['subsection_endpoints_title'][$lang] . '</h3>';
$manualHtml .= '<p>' . $manualContent['desc_endpoints_summary'][$lang] . '</p>';

// List each endpoint (dynamically or hardcoded based on your preference)
$manualHtml .= '<ul>';
// Example for login endpoint
$manualHtml .= '<li><b>POST /login</b>: ' . $manualContent['endpoint_login_summary'][$lang] . '. ' . $manualContent['endpoint_login_desc'][$lang] . '</li>';
// Add list items for all other endpoints similarly using the content array
$manualHtml .= '<li><b>POST /register</b>: ' . $manualContent['endpoint_register_summary'][$lang] . '. ' . $manualContent['endpoint_register_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /logout</b>: ' . $manualContent['endpoint_logout_summary'][$lang] . '. ' . $manualContent['endpoint_logout_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /regenerate-api-key</b>: ' . $manualContent['endpoint_regenerate_api_key_summary'][$lang] . '. ' . $manualContent['endpoint_regenerate_api_key_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>GET /user/{user_id}</b>: ' . $manualContent['endpoint_user_summary'][$lang] . '. ' . $manualContent['endpoint_user_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>GET /history</b>: ' . $manualContent['endpoint_history_summary'][$lang] . '. ' . $manualContent['endpoint_history_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>DELETE /delete-all-history</b>: ' . $manualContent['endpoint_delete_all_history_summary'][$lang] . '. ' . $manualContent['endpoint_delete_all_history_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /merge-pdf</b>: ' . $manualContent['endpoint_merge_pdf_summary'][$lang] . '. ' . $manualContent['endpoint_merge_pdf_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /delete-pages</b>: ' . $manualContent['endpoint_delete_pages_summary'][$lang] . '. ' . $manualContent['endpoint_delete_pages_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /reorder-pages</b>: ' . $manualContent['endpoint_reorder_pages_summary'][$lang] . '. ' . $manualContent['endpoint_reorder_pages_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /rotate-pages</b>: ' . $manualContent['endpoint_rotate_pages_summary'][$lang] . '. ' . $manualContent['endpoint_rotate_pages_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /extract-pages</b>: ' . $manualContent['endpoint_extract_pages_summary'][$lang] . '. ' . $manualContent['endpoint_extract_pages_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /add-watermark</b>: ' . $manualContent['endpoint_add_watermark_summary'][$lang] . '. ' . $manualContent['endpoint_add_watermark_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /compress-pdf</b>: ' . $manualContent['endpoint_compress_pdf_summary'][$lang] . '. ' . $manualContent['endpoint_compress_pdf_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /images-to-pdf</b>: ' . $manualContent['endpoint_images_to_pdf_summary'][$lang] . '. ' . $manualContent['endpoint_images_to_pdf_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /split-pdf</b>: ' . $manualContent['endpoint_split_pdf_summary'][$lang] . '. ' . $manualContent['endpoint_split_pdf_desc'][$lang] . '</li>';
$manualHtml .= '<li><b>POST /pdf-to-text</b>: ' . $manualContent['endpoint_pdf_to_text_summary'][$lang] . '. ' . $manualContent['endpoint_pdf_to_text_desc'][$lang] . '</li>';

$manualHtml .= '</ul>';


// PDF Export Implementation Section
$manualHtml .= '<h2>' . $manualContent['section_pdf_export_title'][$lang] . '</h2>';
$manualHtml .= '<p>' . $manualContent['desc_pdf_export_intro'][$lang] . '</p>';
$manualHtml .= '<ul>';
$manualHtml .= '<li><b>' . $manualContent['text_choose_library'][$lang] . '</b> ...</li>';
$manualHtml .= '<li><b>' . $manualContent['text_install_library'][$lang] . '</b> ...</li>';
$manualHtml .= '<li><b>' . $manualContent['text_create_endpoint'][$lang] . '</b> ...</li>';
$manualHtml .= '<li><b>' . $manualContent['text_generate_content'][$lang] . '</b> ...</li>';
$manualHtml .= '<li><b>' . $manualContent['text_use_library'][$lang] . '</b> ...</li>';
$manualHtml .= '<li><b>' . $manualContent['text_example_dompdf'][$lang] . '</b> ...</li>';
$manualHtml .= '</ul>';


$manualHtml .= '</body></html>';


$dompdf->loadHtml($manualHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("user_manual_" . $lang . ".pdf", array("Attachment" => true));
?>
