<?php
header('Content-Type: text/plain');

echo "PHP Version: " . phpversion() . "\n\n";

echo "Required Extensions:\n";
echo "==================\n";
echo "GD Extension: " . (extension_loaded('gd') ? "Enabled" : "Not enabled") . "\n";
echo "ZIP Extension: " . (extension_loaded('zip') ? "Enabled" : "Not enabled") . "\n";
echo "MBString Extension: " . (extension_loaded('mbstring') ? "Enabled" : "Not enabled") . "\n";

echo "\nUpload Configuration:\n";
echo "===================\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

echo "\nError Reporting:\n";
echo "===============\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";

echo "\nDirectory Permissions:\n";
echo "====================\n";
$uploadDir = __DIR__ . '/public/uploads';
echo "Upload directory: $uploadDir\n";
echo "Exists: " . (file_exists($uploadDir) ? "Yes" : "No") . "\n";
if (file_exists($uploadDir)) {
    echo "Writable: " . (is_writable($uploadDir) ? "Yes" : "No") . "\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
}

echo "\nComposer:\n";
echo "=========\n";
echo "vendor/autoload.php: " . (file_exists(__DIR__ . '/vendor/autoload.php') ? "Exists" : "Missing") . "\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "PhpSpreadsheet: " . (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory') ? "Available" : "Not available") . "\n";
} 