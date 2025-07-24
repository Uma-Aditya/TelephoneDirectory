<?php
session_start();
require_once 'config/config.php';

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$status = $_GET['status'] ?? 'pending';

$stmt = $pdo->prepare("SELECT * FROM requests WHERE status = ? ORDER BY request_date DESC");
$stmt->execute([$status]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Request Approval</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .btn-sm {
      padding: 5px 10px;
      font-size: 14px;
    }
    .table td {
      padding: 8px;
      vertical-align: middle;
      font-size: 14px;
    }
    .table th {
      padding: 12px 8px;
      font-size: 14px;
      font-weight: 500;
    }
    .table-container {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-top: 20px;
    }
    .status-filter {
      margin-bottom: 20px;
    }
    .status-filter select {
      padding: 8px;
      font-size: 14px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-left: 8px;
    }
    .status-filter label {
      font-size: 14px;
      font-weight: 500;
    }
    .btn-success {
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-danger {
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-warning {
      background-color: #ffc107;
      color: #212529;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    .btn-danger:hover {
      background-color: #c82333;
    }
    .btn-warning:hover {
      background-color: #e0a800;
    }
    .action-buttons {
      margin-bottom: 20px;
    }
    .action-buttons form {
      display: inline-block;
      margin-right: 10px;
    }
    .text-center {
      text-align: center;
    }
    .text-muted {
      color: #6c757d;
    }
  </style>
</head>
<body>
<?php include "includes/sidebar.php"; ?>

<main class="main-content">
  <div class="content">
    <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
    <h4>Directory Requests Management</h4>

    <div class="status-filter">
      <form method="get">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
          <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </form>
    </div>

    <?php if ($status === 'approved' || $status === 'rejected'): ?>
      <div class="action-buttons">
        <form method="post" action="handle_request.php" onsubmit="return confirm('Are you sure you want to delete all approved requests?');">
          <input type="hidden" name="bulk_delete" value="approved">
          <button class="btn btn-sm btn-warning">Clear Approved</button>
        </form>

        <form method="post" action="handle_request.php" onsubmit="return confirm('Are you sure you want to delete all rejected requests?');">
          <input type="hidden" name="bulk_delete" value="rejected">
          <button class="btn btn-sm btn-danger">Clear Rejected</button>
        </form>
      </div>
    <?php endif; ?>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>CPF</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Mobile</th>
            <th>Section</th>
            <th>Subsection</th>
            <th>Extension</th>
            <th>Direct</th>
            <th>DID Number</th>
            <th>DOB</th>
            <th>DOR</th>
            <th>Level</th>
            <th>Class</th>
            <th>Date Join ONGC</th>
            <th>Date Join Post</th>
            <th>Date Prom</th>
            <th>Date Join Area</th>
            <th>Seating Location</th>
            <th>Status</th>
            <?php if ($status === 'pending'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (count($requests) === 0): ?>
            <tr><td colspan="13" class="text-center text-muted" style="padding: 20px;">No requests found.</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['cpf']) ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['designation']) ?></td>
                <td><?= htmlspecialchars($r['mobile']) ?></td>
                <td><?= htmlspecialchars($r['section']) ?></td>
                <td><?= htmlspecialchars($r['subsection'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['ext'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['direct'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['did_number'] ?? '') ?></td>
                <td><?= $r['dob'] ? date('Y-m-d', strtotime($r['dob'])) : '' ?></td>
                <td><?= $r['dor'] ? date('Y-m-d', strtotime($r['dor'])) : '' ?></td>
                <td><?= htmlspecialchars($r['level'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['class'] ?? '') ?></td>
                <td><?= isset($r['date_join_ongc']) && $r['date_join_ongc'] ? date('Y-m-d', strtotime($r['date_join_ongc'])) : '' ?></td>
                <td><?= isset($r['date_join_post']) && $r['date_join_post'] ? date('Y-m-d', strtotime($r['date_join_post'])) : '' ?></td>
                <td><?= isset($r['date_prom']) && $r['date_prom'] ? date('Y-m-d', strtotime($r['date_prom'])) : '' ?></td>
                <td><?= isset($r['date_join_area']) && $r['date_join_area'] ? date('Y-m-d', strtotime($r['date_join_area'])) : '' ?></td>
                <td><?= htmlspecialchars($r['seating_location'] ?? '') ?></td>
                <td class="text-capitalize"><?= $r['status'] ?></td>
                <?php if ($status === 'pending'): ?>
                  <td class="text-center">
                    <form method="post" action="handle_approval.php" style="display: inline-block; margin: 0 2px;">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="approve">
                      <button class="btn btn-sm btn-success">Approve</button>
                    </form>
                    <form method="post" action="handle_approval.php" style="display: inline-block; margin: 0 2px;">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="reject">
                      <button class="btn btn-sm btn-danger">Reject</button>
                    </form>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</body>
</html>
