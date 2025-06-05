<?php
session_start();
require_once 'config/config.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'approval_errors.log');

// Redirect to login if not logged in
if (!isset($_SESSION['admin_name'])) {
    error_log("Unauthorized access attempt");
    header('Location: login.php');
    exit();
}

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header('Location: requests.php');
    exit();
}

$request_id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

error_log("Processing request: ID=$request_id, Action=$action");

if (!$request_id || !in_array($action, ['approve', 'reject'])) {
    error_log("Invalid request parameters: ID=$request_id, Action=$action");
    $_SESSION['error'] = "Invalid request parameters";
    header('Location: requests.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Get request details first
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception("Request not found or already processed");
    }

    if ($action === 'approve') {
        // Insert into original_records if approved
        $stmt = $pdo->prepare("
            INSERT INTO original_records 
            (cpf, name, designation, mobile, section, subsection, ext, direct, dob, dor, level)
            VALUES 
            (:cpf, :name, :designation, :mobile, :section, :subsection, :ext, :direct, :dob, :dor, :level)
        ");
        
        $stmt->execute([
            'cpf' => $request['cpf'],
            'name' => $request['name'],
            'designation' => $request['designation'],
            'mobile' => $request['mobile'],
            'section' => $request['section'],
            'subsection' => $request['subsection'],
            'ext' => $request['ext'],
            'direct' => $request['direct'],
            'dob' => $request['dob'],
            'dor' => $request['dor'],
            'level' => $request['level']
        ]);
    }

    // Update request status
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt->execute([$new_status, $request_id]);

    $pdo->commit();
    $_SESSION['success'] = "Request successfully " . $action . "ed";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error processing request: " . $e->getMessage();
}

header('Location: requests.php');
exit();
