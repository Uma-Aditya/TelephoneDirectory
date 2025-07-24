<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bulk delete logic
    if (!empty($_POST['bulk_delete'])) {
        $targetStatus = $_POST['bulk_delete'];
        if (in_array($targetStatus, ['approved', 'rejected'])) {
            $stmt = $pdo->prepare("DELETE FROM requests WHERE status = ?");
            $stmt->execute([$targetStatus]);

            header("Location: requests.php?status=$targetStatus");
            exit;
        }
    }

    // Single approve/reject logic (already present)
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($id && in_array($action, ['approve', 'reject'])) {
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
    }

    header("Location: requests.php?status=pending");
    exit;
}
if (isset($_GET['status'])) {
    $status = $_GET['status'];
} else {
    $status = 'pending';
}
