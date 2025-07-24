<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_entries.php');
    exit();
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';

if (!$action || !$id) {
    header('Location: manage_entries.php?message=' . urlencode('Invalid request'));
    exit();
}

try {
    switch ($action) {
        case 'modify':
            // Validate required fields
            $required_fields = ['cpf', 'name', 'designation', 'mobile', 'section'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    throw new Exception("$field is required");
                }
            }

            // Prepare date fields
            $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
            $dor = !empty($_POST['dor']) ? $_POST['dor'] : null;

            // Validate date formats
            if ($dob && !strtotime($dob)) {
                throw new Exception('Invalid date format for Date of Birth');
            }
            if ($dor && !strtotime($dor)) {
                throw new Exception('Invalid date format for Date of Retirement');
            }

            // Prepare the update query
            $sql = "UPDATE original_records SET 
                cpf = :cpf,
                name = :name,
                designation = :designation,
                mobile = :mobile,
                section = :section,
                subsection = :subsection,
                ext = :ext,
                direct = :direct,
                dob = :dob,
                dor = :dor,
                level = :level,
                class = :class,
                seating_location = :seating_location,
                did_number = :did_number,
                date_join_ongc = :date_join_ongc,
                date_join_post = :date_join_post,
                date_prom = :date_prom,
                date_join_area = :date_join_area
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            
            // Execute the update
            $params = [
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
                'seating_location' => $_POST['seating_location'] ?? null,
                'did_number' => $_POST['did_number'] ?? null,
                'date_join_ongc' => !empty($_POST['date_join_ongc']) ? $_POST['date_join_ongc'] : null,
                'date_join_post' => !empty($_POST['date_join_post']) ? $_POST['date_join_post'] : null,
                'date_prom' => !empty($_POST['date_prom']) ? $_POST['date_prom'] : null,
                'date_join_area' => !empty($_POST['date_join_area']) ? $_POST['date_join_area'] : null,
                'id' => $id
            ];

            $result = $stmt->execute($params);

            if ($result) {
                $message = 'Entry updated successfully';
            } else {
                throw new Exception('Failed to update entry');
            }
            break;

        case 'delete':
            // Check if the record exists before deleting
            $stmt = $pdo->prepare("SELECT id FROM original_records WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception('Record not found');
            }

            // Prepare and execute delete query
            $stmt = $pdo->prepare("DELETE FROM original_records WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                $message = 'Entry deleted successfully';
            } else {
                throw new Exception('Failed to delete entry');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

    header('Location: manage_entries.php?message=' . urlencode($message));
    exit();

} catch (PDOException $e) {
    // Log database errors
    error_log("Database error in handle_entries.php: " . $e->getMessage());
    header('Location: manage_entries.php?message=' . urlencode('Database error occurred. Please try again.'));
    exit();
} catch (Exception $e) {
    header('Location: manage_entries.php?message=' . urlencode('Error: ' . $e->getMessage()));
    exit();
} 