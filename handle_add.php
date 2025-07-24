<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

// Function to convert date from DD-MM-YYYY to MySQL format
function convertToMySQLDate($date) {
    if (empty($date)) return null;
    $dateObj = DateTime::createFromFormat('d-m-Y', $date);
    return $dateObj ? $dateObj->format('Y-m-d') : null;
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

    // Convert dates to MySQL format (optional fields)
    $dob = !empty($_POST['dob']) ? convertToMySQLDate($_POST['dob']) : null;
    $dor = !empty($_POST['dor']) ? convertToMySQLDate($_POST['dor']) : null;
    $date_join_ongc = !empty($_POST['date_join_ongc']) ? $_POST['date_join_ongc'] : null;
    $date_join_post = !empty($_POST['date_join_post']) ? $_POST['date_join_post'] : null;
    $date_prom = !empty($_POST['date_prom']) ? $_POST['date_prom'] : null;
    $date_join_area = !empty($_POST['date_join_area']) ? $_POST['date_join_area'] : null;

    // Check if CPF already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM original_records WHERE cpf = ?");
    $stmt->execute([$_POST['cpf']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("An entry with this CPF already exists");
    }

    // Prepare the insert query
    $sql = "INSERT INTO original_records (
        cpf, name, designation, mobile, section, subsection, 
        ext, direct, dob, dor, level, class,
        date_join_ongc, date_join_post, date_prom, date_join_area
    ) VALUES (
        :cpf, :name, :designation, :mobile, :section, :subsection,
        :ext, :direct, :dob, :dor, :level, :class,
        :date_join_ongc, :date_join_post, :date_prom, :date_join_area
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
        'dob' => $dob,
        'dor' => $dor,
        'level' => $_POST['level'] ?? null,
        'class' => $_POST['class'] ?? null,
        'date_join_ongc' => $date_join_ongc,
        'date_join_post' => $date_join_post,
        'date_prom' => $date_prom,
        'date_join_area' => $date_join_area
    ]);

    if ($result) {
        $_SESSION['success'] = "Record added successfully";
        header('Location: add.php?success=1');
    } else {
        throw new Exception("Failed to add record");
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: add.php?error=1&message=' . urlencode($e->getMessage()));
}
exit();
