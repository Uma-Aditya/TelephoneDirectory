<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit();
}

try {
    // Validate required fields
    $required_fields = ['cpf', 'name', 'designation', 'mobile', 'section'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("$field is required");
        }
    }

    // Check if CPF already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM original_records WHERE cpf = ?");
    $stmt->execute([$_POST['cpf']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("An entry with this CPF already exists");
    }

    // Prepare the insert query
    $sql = "INSERT INTO original_records (
        cpf, name, designation, mobile, section, subsection, 
        ext, direct, dob, dor, level
    ) VALUES (
        :cpf, :name, :designation, :mobile, :section, :subsection,
        :ext, :direct, :dob, :dor, :level
    )";

    $stmt = $pdo->prepare($sql);
    
    // Execute the insert
    $result = $stmt->execute([
        'cpf' => $_POST['cpf'],
        'name' => $_POST['name'],
        'designation' => $_POST['designation'],
        'mobile' => $_POST['mobile'],
        'section' => $_POST['section'],
        'subsection' => $_POST['subsection'] ?? null,
        'ext' => $_POST['ext'] ?? null,
        'direct' => $_POST['direct'] ?? null,
        'dob' => $_POST['dob'] ?: null,
        'dor' => $_POST['dor'] ?: null,
        'level' => $_POST['level'] ?? null
    ]);

    if ($result) {
        header('Location: add.php?success=1');
    } else {
        throw new Exception('Failed to add entry');
    }

} catch (Exception $e) {
    error_log("Error in handle_add.php: " . $e->getMessage());
    header('Location: add.php?error=1&message=' . urlencode($e->getMessage()));
}
exit();
