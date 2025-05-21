<?php

require_once '../../../vendor/autoload.php';

use Symfony\Component\Process\Process;

function executePythonScript($scriptName, $arguments = []) {
    $pythonPath = '/usr/bin/python3';
    $scriptPath = __DIR__ . '/' . $scriptName;

    if (!file_exists($scriptPath)) {
        throw new Exception("Python script not found: $scriptPath");
    }
    $Arguments = [$pythonPath, $scriptPath, ...$arguments];
    $process = new Process($Arguments);
    $process->run();

    if (!$process->isSuccessful()) {
        // --- Capture BOTH standard error and standard output on failure ---
    $errorOutput = $process->getErrorOutput();
    $output = $process->getOutput();

    // Log the error details server-side
    error_log("Python script failed: " . $scriptName);
    error_log("Arguments: " . implode(" ", array_map('escapeshellarg', $arguments)));
    error_log("Exit Code: " . $process->getExitCode());
    error_log("Standard Error: " . $errorOutput);
    error_log("Standard Output: " . $output);

    // --- Throw an exception including both outputs ---
    throw new Exception(
        'Error running Python script: ' . $errorOutput .
        ' Output: ' . $output
    );
}

    return trim($process->getOutput());
}

function handlePdfMerge($files) {
    if (!is_array($files) || empty($files)) {
        throw new Exception("Invalid file input for merging.");
    }
    $tempFiles = [];
    foreach ($files as $file) {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_merge_');
        if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
            throw new Exception("Failed to move uploaded file");
        }
        $tempFiles[] = $tempFilePath;
    }
    $outputFile = tempnam(sys_get_temp_dir(), 'merged_pdf_');
    $arguments = $tempFiles;
    $arguments[] = $outputFile;
    $result = executePythonScript('merge_pdf.py', $arguments);

    foreach ($tempFiles as $tempFile) {
        unlink($tempFile);
    }

    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handlePdfSplit($file, $splitPageNumber) {
    if (!isset($file['tmp_name'])) {
        throw new Exception("Invalid file input for splitting.");
    }

    $tempInputFilePath = tempnam(sys_get_temp_dir(), 'pdf_split_input_');
    if (!move_uploaded_file($file['tmp_name'], $tempInputFilePath)) {
        throw new Exception("Failed to move uploaded file for splitting.");
    }

    $tempOutputZipPath = tempnam(sys_get_temp_dir(), 'split_pdf_output_');

    $arguments = [$tempInputFilePath, $splitPageNumber, $tempOutputZipPath];
    $result = executePythonScript('split_pdf.py', $arguments);

    unlink($tempInputFilePath);

    if ($result === "success") {
        return $tempOutputZipPath;
    } else {
        error_log("Python script error for split_pdf.py: " . $result);
        if (file_exists($tempOutputZipPath)) {
            unlink($tempOutputZipPath);
        }
        return false;
    }
}

function handlePdfRotate($file, $outputFile, $rotation) {
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_rotate_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }
    $result = executePythonScript('rotate_pdf.py', [$tempFilePath, $outputFile, $rotation]);
    unlink($tempFilePath);
    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handlePdfDeletePages($file, $outputFile, $pages) {
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_delete_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }
    $result = executePythonScript('delete_pages.py', [$tempFilePath, $outputFile, $pages]);
    unlink($tempFilePath);
    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handlePdfExtractPages($file, $outputFile, $pages) {
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_extract_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }
    $result = executePythonScript('extract_pages.py', [$tempFilePath, $outputFile, $pages]);
    unlink($tempFilePath);
    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handlePdfToText($file) {
    if (!isset($file['tmp_name'])) {
        throw new Exception("Invalid file input for text extraction.");
    }

    $tempInputFilePath = tempnam(sys_get_temp_dir(), 'pdf_to_text_input_');
    if (!move_uploaded_file($file['tmp_name'], $tempInputFilePath)) {
        throw new Exception("Failed to move uploaded file for text extraction.");
    }

    $tempOutputTextPath = tempnam(sys_get_temp_dir(), 'extracted_text_output_');
    if ($tempOutputTextPath === false) {

        if (file_exists($tempInputFilePath)) { @unlink($tempInputFilePath); }
        throw new Exception("Failed to create temporary output text file.");
    }

    $arguments = [$tempInputFilePath, $tempOutputTextPath];
    $result = executePythonScript('pdf_to_text.py', $arguments);

    unlink($tempInputFilePath);

    if ($result === "success") {
        if (file_exists($tempOutputTextPath) && is_readable($tempOutputTextPath)) {
            $extractedText = file_get_contents($tempOutputTextPath);
            unlink($tempOutputTextPath);
            return $extractedText;
        } else {
            error_log("PDF to Text: Script reported success but output file missing/unreadable: " . $tempOutputTextPath);
            if (file_exists($tempOutputTextPath)) { @unlink($tempOutputTextPath); }
            throw new Exception("Text extraction script succeeded but output file could not be read.");
        }

    } else {
        error_log("PDF to Text: Python script error: " . $result);
        if (file_exists($tempOutputTextPath)) {
            @unlink($tempOutputTextPath);
        }
        throw new Exception("Error during text extraction: " . $result);
    }
}

function handlePdfAddWatermark($file, $watermarkText, $outputFile) {
    if (!isset($file['tmp_name'])) {
        throw new Exception("Invalid file input for adding watermark.");
    }
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_watermark_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }

    $arguments = [$tempFilePath, $watermarkText, $outputFile];

    $result = executePythonScript('add_watermark.py', $arguments);

    unlink($tempFilePath);

    if ($result === "success") {
        return $outputFile;
    } else {
        error_log("Python script error for add_watermark.py: " . $result);
        return false;
    }
}

function handlePdfCompress($file, $outputFile) {
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_compress_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }
    $result = executePythonScript('compress_pdf.py', [$tempFilePath, $outputFile]);
    unlink($tempFilePath);
    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handleImagesToPdf($images) {
    if (!is_array($images) || empty($images)) {
        throw new Exception("Invalid image input for conversion.");
    }
    $tempFiles = [];
    foreach ($images as $image) {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'img_to_pdf_');
        if (!move_uploaded_file($image['tmp_name'], $tempFilePath)) {
            throw new Exception("Failed to move uploaded image file");
        }
        $tempFiles[] = $tempFilePath;
    }
    $outputFile = tempnam(sys_get_temp_dir(), 'images_pdf_');
    $arguments = $tempFiles;
    $arguments[] = $outputFile;
    $result = executePythonScript('images_to_pdf.py', $arguments);
    foreach ($tempFiles as $tempFile) {
        unlink($tempFile);
    }
    if ($result === "success") {
        return $outputFile;
    } else {
        return false;
    }
}

function handlePdfReorderPages($file, $order) {
    if (!isset($file['tmp_name'])) {
        throw new Exception("Invalid file input for reordering.");
    }
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_reorder_');
    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        throw new Exception("Failed to move uploaded file.");
    }

    $outputFile = tempnam(sys_get_temp_dir(), 'reordered_pdf_');

    $arguments = [$tempFilePath, $outputFile, $order];

    $result = executePythonScript('reorder_pdf.py', $arguments); 

    unlink($tempFilePath);

    if ($result === "success") {
        return $outputFile;
    } else {
        error_log("Python script error for reorder_pdf.py: " . $result);
        return false;
    }
}
?>
