<?php
// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Function to log errors
function logError($error) {
    $logFile = __DIR__ . '/upload_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $error\n";
    file_put_contents($logFile, $message, FILE_APPEND);
}

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("PHP Error: [$errno] $errstr in $errfile on line $errline");
    return true;
});

// Custom exception handler
set_exception_handler(function($e) {
    logError("Uncaught Exception: " . $e->getMessage());
    sendJsonResponse(false, "Internal server error. Check logs for details.");
});

session_start();
require_once 'config/config.php';

// Function to send JSON response
function sendJsonResponse($success, $message, $warning = null, $debug = null) {
    if (headers_sent($filename, $linenum)) {
        logError("Headers already sent in $filename on line $linenum");
    }
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ensure clean output
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'warning' => $warning,
        'debug' => $debug
    ]);
    exit();
}

// Start output buffering
ob_start();

try {
    // Check if file was uploaded
    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse(false, 'Please upload an Excel file.');
    }

    // Ensure upload directory exists
    if (!file_exists(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0777, true)) {
            sendJsonResponse(false, 'Failed to create upload directory.');
        }
    }

    $file = $_FILES['excelFile'];
    $filePath = $file['tmp_name'];

    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['xls', 'xlsx', 'csv'])) {
        sendJsonResponse(false, 'Please upload an Excel file (.xls, .xlsx) or CSV file.');
    }

    // Debug information about the uploaded file
    $debugInfo = [
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name'],
        'error' => $file['error']
    ];

    // Additional file checks
    if (!is_uploaded_file($file['tmp_name'])) {
        sendJsonResponse(false, 'File upload failed - security check failed.', null, $debugInfo);
    }

    if ($file['size'] == 0) {
        sendJsonResponse(false, 'Uploaded file is empty.', null, $debugInfo);
    }

    // Handle different file types
    $headers = [];
    $data = [];
    
    if ($extension === 'csv') {
        // For CSV files, check encoding
        if (!mb_check_encoding(file_get_contents($filePath), 'UTF-8')) {
            sendJsonResponse(false, 'CSV file must be in UTF-8 encoding.', null, $debugInfo);
        }

        // Read CSV file
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            sendJsonResponse(false, 'Unable to read file.', null, $debugInfo);
        }

        // Get headers
        $headers = fgetcsv($handle, 0, ",");
        if ($headers === false || empty($headers)) {
            fclose($handle);
            sendJsonResponse(false, 'Unable to read headers or file is empty.', null, $debugInfo);
        }

        // Remove BOM if present
        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
        $headers = array_map('trim', $headers);

        // Read data
        while (($row = fgetcsv($handle)) !== false) {
            if (!empty(array_filter($row))) { // Skip empty rows
                $data[] = $row;
            }
        }
        fclose($handle);
    } else {
        // For Excel files, use PhpSpreadsheet
        require 'vendor/autoload.php';
        
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get headers from first row
            $headers = [];
            $firstRow = $worksheet->getRowIterator(1)->current();
            $cellIterator = $firstRow->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            foreach ($cellIterator as $cell) {
                $value = trim($cell->getValue());
                if (!empty($value)) {
                    $headers[] = $value;
                }
            }
            
            if (empty($headers)) {
                sendJsonResponse(false, 'No headers found in Excel file.', null, $debugInfo);
            }
            
            // Read data starting from second row
            $rows = $worksheet->toArray();
            array_shift($rows); // Remove header row
            
            foreach ($rows as $row) {
                if (!empty(array_filter($row))) { // Skip empty rows
                    $data[] = $row;
                }
            }
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error reading Excel file: ' . $e->getMessage(), null, $debugInfo);
        }
    }

    try {
        $columnMapping = getColumnMapping();
        $mappedColumns = mapColumns($headers, $columnMapping);
    } catch (Exception $e) {
        sendJsonResponse(false, $e->getMessage(), null, array_merge($debugInfo, ['headers' => $headers]));
    }

    // Begin database transaction
    $pdo->beginTransaction();
    
    try {
        $row = 2; // Start from row 2 (after headers)
        $insertedCount = 0;
        $updatedCount = 0;
        $errors = [];

        foreach ($data as $rowData) {
            try {
                if (count($rowData) !== count($headers)) {
                    throw new Exception("Column count mismatch in row {$row}");
                }

                $formattedData = validateAndFormatData($rowData, $columnMapping, $mappedColumns);
                
                // First check if record exists
                $checkStmt = $pdo->prepare("SELECT cpf FROM original_records WHERE cpf = ?");
                $checkStmt->execute([$formattedData['cpf']]);
                $exists = $checkStmt->fetch();
                
                // Prepare SQL statement based on whether record exists
                if ($exists) {
                    // Update existing record
                    $updateParts = [];
                    $updateValues = [];
                    foreach ($formattedData as $col => $val) {
                        if ($col !== 'cpf') {
                            $updateParts[] = "$col = ?";
                            $updateValues[] = $val;
                        }
                    }
                    // Add CPF at the end for WHERE clause
                    $updateValues[] = $formattedData['cpf'];
                    
                    $sql = "UPDATE original_records SET " . implode(', ', $updateParts) . " WHERE cpf = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($updateValues);
                    $updatedCount++;
                    logError("Updated record with CPF: " . $formattedData['cpf']); // Log update
                } else {
                    // Insert new record
                    $columns = array_keys($formattedData);
                    $values = array_fill(0, count($formattedData), '?');
                    $sql = "INSERT INTO original_records (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_values($formattedData));
                    $insertedCount++;
                    logError("Inserted new record with CPF: " . $formattedData['cpf']); // Log insert
                }
                
            } catch (Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
                logError("Error processing row {$row}: " . $e->getMessage()); // Log error
            }
            $row++;
        }

        if (!empty($errors) && ($insertedCount + $updatedCount) === 0) {
            // If no records were processed successfully, rollback
            $pdo->rollBack();
            $warning = !empty($errors) ? implode("\n", $errors) : null;
            sendJsonResponse(false, 'No records were processed due to errors.', $warning, $debugInfo);
        } else {
            // Commit transaction
            $pdo->commit();
            $warning = !empty($errors) ? implode("\n", $errors) : null;
            $message = sprintf(
                "Successfully processed %d records (%d new, %d updated).",
                $insertedCount + $updatedCount,
                $insertedCount,
                $updatedCount
            );
            sendJsonResponse(true, $message, $warning, $debugInfo);
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse(false, 'Database error: ' . $e->getMessage(), null, $debugInfo);
    }

} catch (Exception $e) {
    sendJsonResponse(false, 'Error: ' . $e->getMessage(), null, isset($debugInfo) ? $debugInfo : null);
}

// End output buffering
ob_end_clean();

// Column mapping configuration
function getColumnMapping() {
    return [
        'cpf' => [
            'aliases' => ['cpf', 'CPF'],
            'required' => true,
            'type' => 'string',
            'validation' => function($value) {
                return !empty($value) && strlen($value) <= 20;
            }
        ],
        'name' => [
            'aliases' => ['name', 'NAME'],
            'required' => true,
            'type' => 'string',
            'validation' => function($value) {
                return !empty($value);
            }
        ],
        'designation' => [
            'aliases' => ['designation', 'DESIGNATION'],
            'required' => true,
            'type' => 'string'
        ],
        'level' => [
            'aliases' => ['level', 'LEVEL'],
            'required' => true,
            'type' => 'string'
        ],
        'class' => [
            'aliases' => ['class', 'CLASS'],
            'required' => true,
            'type' => 'string'
        ],
        'section' => [
            'aliases' => ['section', 'SECTION'],
            'required' => true,
            'type' => 'string'
        ],
        'subsection' => [
            'aliases' => ['subsection', 'SUBSECTION'],
            'required' => true,
            'type' => 'string'
        ],
        'ext' => [
            'aliases' => ['ext', 'EXT'],
            'required' => false,
            'type' => 'string'
        ],
        'direct' => [
            'aliases' => ['direct', 'DIRECT'],
            'required' => false,
            'type' => 'string'
        ],
        'mobile' => [
            'aliases' => ['mobile', 'MOBILE'],
            'required' => true,
            'type' => 'numeric',
            'validation' => function($value) {
                if (strpos(strtolower($value), 'e') !== false) {
                    $value = number_format((float)$value, 0, '', '');
                }
                return strlen($value) >= 10 && strlen($value) <= 15;
            }
        ],
        'dob' => [
            'aliases' => ['dob', 'DOB'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ],
        'dor' => [
            'aliases' => ['dor', 'DOR'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ],
        'date_join_ongc' => [
            'aliases' => ['date_join_ongc', 'DATE_JOIN_ONGC'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ],
        'date_join_post' => [
            'aliases' => ['date_join_post', 'DATE_JOIN_POST'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ],
        'eff_date_prom' => [
            'aliases' => ['eff_date_prom', 'EFF_DATE_PROM'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ],
        'date_join_area' => [
            'aliases' => ['date_join_area', 'DATE_JOIN_AREA'],
            'required' => true,
            'type' => 'date',
            'validation' => function($value) {
                return strtotime($value) !== false;
            }
        ]
    ];
}

// Function to find the column index for each required field
function mapColumns($headers, $columnMapping) {
    $mapping = [];
    $headers = array_map('trim', $headers); // Only trim whitespace
    
    foreach ($columnMapping as $dbColumn => $config) {
        $found = false;
        foreach ($config['aliases'] as $alias) {
            // Try exact match first
            $index = array_search($alias, $headers);
            if ($index !== false) {
                $mapping[$dbColumn] = $index;
                $found = true;
                break;
            }
            
            // Try case-insensitive match if exact match fails
            $index = array_search(strtolower($alias), array_map('strtolower', $headers));
            if ($index !== false) {
                $mapping[$dbColumn] = $index;
                $found = true;
                break;
            }
        }
        if (!$found && $config['required']) {
            throw new Exception("Required column not found: {$dbColumn}. Please check your Excel file.");
        }
    }
    
    return $mapping;
}

// Function to validate and format data
function validateAndFormatData($row, $columnMapping, $mappedColumns) {
    $formattedData = [];
    
    foreach ($columnMapping as $dbColumn => $config) {
        if (!isset($mappedColumns[$dbColumn])) {
            if ($config['required']) {
                throw new Exception("Missing required column: {$dbColumn}");
            }
            $formattedData[$dbColumn] = null;
            continue;
        }
        
        $value = $row[$mappedColumns[$dbColumn]] ?? null;
        $value = trim($value);
        
        // Handle empty values
        if (empty($value)) {
            if ($config['required']) {
                throw new Exception("Empty value in required column: {$dbColumn}");
            }
            $formattedData[$dbColumn] = null;
            continue;
        }
        
        // Type validation and formatting
        switch ($config['type']) {
            case 'numeric':
                if (strpos(strtolower($value), 'e') !== false) {
                    $value = number_format((float)$value, 0, '', '');
                }
                if (!is_numeric($value)) {
                    throw new Exception("Invalid numeric value in column: {$dbColumn}");
                }
                break;
                
            case 'date':
                $timestamp = strtotime($value);
                if ($timestamp === false) {
                    throw new Exception("Invalid date format in column: {$dbColumn}");
                }
                $value = date('Y-m-d', $timestamp);
                break;
        }
        
        // Custom validation if defined
        if (isset($config['validation']) && !$config['validation']($value)) {
            throw new Exception("Validation failed for column: {$dbColumn}");
        }
        
        $formattedData[$dbColumn] = $value;
    }
    
    return $formattedData;
} 