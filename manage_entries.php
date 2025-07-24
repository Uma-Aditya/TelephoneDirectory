<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

$adminName = $_SESSION['admin_name'];
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

// Function to format date to DD-MM-YYYY
function formatDate($date) {
    if (!$date) return '';
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    return $dateObj ? $dateObj->format('d-m-Y') : '';
}

// Fetch all records
try {
    $stmt = $pdo->query("SELECT * FROM original_records ORDER BY name");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Entries</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .btn-sm {
      padding: 5px 10px;
      font-size: 14px;
      margin: 0 2px;
    }
    .hidden { 
      display: none; 
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
    .edit-form input, 
    .edit-form select {
      width: 100%;
      padding: 8px;
      font-size: 14px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
    .edit-form td { 
      padding: 8px; 
    }
    .btn-primary {
      background-color: #1e1e2f;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-primary:hover {
      background-color: #2a2a3f;
    }
    .btn-danger {
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-danger:hover {
      background-color: #c82333;
    }
    .table-container {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-top: 20px;
      overflow-x: auto;
    }
    #searchInput {
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .edit-row td {
      background-color: #f8f9fa;
    }
    .action-buttons {
      display: flex;
      gap: 5px;
    }
    .alert {
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 4px;
    }
    .alert-error {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
    .alert-success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
    }

    .modal-content {
      position: relative;
      background-color: #fff;
      margin: 50px auto;
      padding: 25px;
      width: 80%;
      max-width: 1000px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: #1e1e2f;
      outline: none;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .modal-header h3 {
      margin: 0;
      color: #1e1e2f;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }

    .close-modal:hover {
      color: #333;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .required::after {
      content: " *";
      color: #dc3545;
    }
  </style>
  <script>
    function filterTable() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const rows = document.querySelectorAll("#entriesTable tbody tr.data-row");
      rows.forEach(row => {
        const match = row.innerText.toLowerCase().includes(input);
        row.style.display = match ? "" : "none";
        // Hide the edit row if the parent row is hidden
        const formRow = row.nextElementSibling;
        if (formRow && formRow.classList.contains('edit-row')) {
          formRow.style.display = match ? "" : "none";
        }
      });
    }

    function toggleEdit(id) {
      const row = document.getElementById('edit-' + id);
      if (row.classList.contains('hidden')) {
        // Hide all other edit rows first
        document.querySelectorAll('.edit-row').forEach(r => r.classList.add('hidden'));
      }
      row.classList.toggle('hidden');
    }

    function confirmDelete() {
      return confirm('Are you sure you want to delete this entry?');
    }

    function openEditModal(id) {
      const modal = document.getElementById('editModal');
      const form = document.getElementById('editForm');
      const row = document.querySelector(`tr:has(input[value="${id}"])`);
      
      // Set the ID in the form
      form.querySelector('[name="id"]').value = id;

      // Set values from the row
      form.querySelector('[name="cpf"]').value = row.querySelector('[data-field="cpf"]').textContent;
      form.querySelector('[name="name"]').value = row.querySelector('[data-field="name"]').textContent;
      form.querySelector('[name="designation"]').value = row.querySelector('[data-field="designation"]').textContent;
      form.querySelector('[name="mobile"]').value = row.querySelector('[data-field="mobile"]').textContent;
      form.querySelector('[name="section"]').value = row.querySelector('[data-field="section"]').textContent;
      form.querySelector('[name="subsection"]').value = row.querySelector('[data-field="subsection"]').textContent;
      form.querySelector('[name="ext"]').value = row.querySelector('[data-field="ext"]').textContent;
      form.querySelector('[name="direct"]').value = row.querySelector('[data-field="direct"]').textContent;
      form.querySelector('[name="did_number"]').value = row.querySelector('[data-field="did_number"]').textContent;
      form.querySelector('[name="dob"]').value = row.querySelector('[data-field="dob"]').getAttribute('data-date');
      form.querySelector('[name="dor"]').value = row.querySelector('[data-field="dor"]').getAttribute('data-date');
      form.querySelector('[name="seating_location"]').value = row.querySelector('[data-field="seating_location"]').textContent;
      form.querySelector('[name="level"]').value = row.querySelector('[data-field="level"]').textContent;
      form.querySelector('[name="class"]').value = row.querySelector('[data-field="class"]').textContent;
      form.querySelector('[name="date_join_ongc"]').value = row.querySelector('[data-field="date_join_ongc"]').getAttribute('data-date');
      form.querySelector('[name="date_join_post"]').value = row.querySelector('[data-field="date_join_post"]').getAttribute('data-date');
      form.querySelector('[name="date_prom"]').value = row.querySelector('[data-field="date_prom"]').getAttribute('data-date');
      form.querySelector('[name="date_join_area"]').value = row.querySelector('[data-field="date_join_area"]').getAttribute('data-date');
      
      modal.style.display = 'block';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target == modal) {
        closeEditModal();
      }
    }
  </script>
</head>
<body>
<?php include "includes/sidebar.php"; ?>

<!-- Add Modal HTML -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Edit Directory Entry</h3>
      <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
    </div>
    <form id="editForm" action="handle_entries.php" method="POST" class="modal-grid">
      <input type="hidden" name="action" value="modify">
      <input type="hidden" name="id" value="">

      <div class="form-group">
        <label class="required" for="cpf">CPF</label>
        <input type="text" id="cpf" name="cpf" required>
      </div>

      <div class="form-group">
        <label class="required" for="name">Name</label>
        <input type="text" id="name" name="name" required>
      </div>

      <div class="form-group">
        <label class="required" for="designation">Designation</label>
        <input type="text" id="designation" name="designation" required>
      </div>

      <div class="form-group">
        <label class="required" for="mobile">Mobile</label>
        <input type="text" id="mobile" name="mobile" required>
      </div>

      <div class="form-group">
        <label class="required" for="section">Section</label>
        <input type="text" id="section" name="section" required>
      </div>

      <div class="form-group">
        <label for="subsection">Subsection</label>
        <input type="text" id="subsection" name="subsection">
      </div>

      <div class="form-group">
        <label for="ext">Extension</label>
        <input type="text" id="ext" name="ext">
      </div>

      <div class="form-group">
        <label for="direct">Direct Line</label>
        <input type="text" id="direct" name="direct">
      </div>

      <div class="form-group">
        <label for="did_number">DID Number</label>
        <input type="text" id="did_number" name="did_number">
      </div>

      <div class="form-group">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">
      </div>

      <div class="form-group">
        <label for="dor">Date of Retirement</label>
        <input type="date" id="dor" name="dor">
      </div>

      <div class="form-group">
        <label for="seating_location">Seating Location</label>
        <input type="text" id="seating_location" name="seating_location" placeholder="Enter seating location">
      </div>

      <div class="form-group">
        <label class="required" for="level">Level</label>
        <input type="text" id="level" name="level" required>
      </div>

      <div class="form-group">
        <label class="required" for="class">Class</label>
        <input type="text" id="class" name="class" required>
      </div>

      <div class="form-group">
        <label class="required" for="date_join_ongc">Date Join ONGC</label>
        <input type="date" id="date_join_ongc" name="date_join_ongc" required>
      </div>

      <div class="form-group">
        <label class="required" for="date_join_post">Date Join Post</label>
        <input type="date" id="date_join_post" name="date_join_post" required>
      </div>

      <div class="form-group">
        <label class="required" for="date_prom">Date Prom</label>
        <input type="date" id="date_prom" name="date_prom" required>
      </div>

      <div class="form-group">
        <label class="required" for="date_join_area">Date Join Area</label>
        <input type="date" id="date_join_area" name="date_join_area" required>
      </div>

      <div class="modal-footer" style="grid-column: 1 / -1;">
        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<main class="main-content">
  <div class="content">
    <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
    <h4>Manage Directory Entries</h4>

    <?php if (isset($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search..." style="width:100%; padding: 8px; margin-bottom: 20px;">

    <div class="table-container">
      <table class="table" id="entriesTable">
        <thead>
          <tr>
            <th>CPF</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Mobile</th>
            <th>Section</th>
            <th>Subsection</th>
            <th>Extension</th>
            <th>Direct Line</th>
            <th>DID Number</th>
            <th>DOB</th>
            <th>DOR</th>
            <th>Seating Location</th>
            <th>Level</th>
            <th>Class</th>
            <th>Date Join ONGC</th>
            <th>Date Join Post</th>
            <th>Date Prom</th>
            <th>Date Join Area</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr><td colspan="13" class="text-center">No records found</td></tr>
          <?php else: ?>
            <?php foreach ($records as $record): ?>
              <tr class="data-row">
                <td data-field="cpf"><?= htmlspecialchars($record['cpf']) ?></td>
                <td data-field="name"><?= htmlspecialchars($record['name']) ?></td>
                <td data-field="designation"><?= htmlspecialchars($record['designation']) ?></td>
                <td data-field="mobile"><?= htmlspecialchars($record['mobile']) ?></td>
                <td data-field="section"><?= htmlspecialchars($record['section']) ?></td>
                <td data-field="subsection"><?= htmlspecialchars($record['subsection'] ?? '') ?></td>
                <td data-field="ext"><?= htmlspecialchars($record['ext'] ?? '') ?></td>
                <td data-field="direct"><?= htmlspecialchars($record['direct'] ?? '') ?></td>
                <td data-field="did_number"><?= htmlspecialchars($record['did_number'] ?? '') ?></td>
                <td data-field="dob" data-date="<?= $record['dob'] ? date('Y-m-d', strtotime($record['dob'])) : '' ?>">
                  <?= formatDate($record['dob']) ?>
                </td>
                <td data-field="dor" data-date="<?= $record['dor'] ? date('Y-m-d', strtotime($record['dor'])) : '' ?>">
                  <?= formatDate($record['dor']) ?>
                </td>
                <td data-field="seating_location"><?= htmlspecialchars($record['seating_location'] ?? '') ?></td>
                <td data-field="level"><?= htmlspecialchars($record['level'] ?? '') ?></td>
                <td data-field="class"><?= htmlspecialchars($record['class'] ?? '') ?></td>
                <td data-field="date_join_ongc" data-date="<?= isset($record['date_join_ongc']) && $record['date_join_ongc'] ? date('Y-m-d', strtotime($record['date_join_ongc'])) : '' ?>">
                  <?= isset($record['date_join_ongc']) ? formatDate($record['date_join_ongc']) : '' ?>
                </td>
                <td data-field="date_join_post" data-date="<?= isset($record['date_join_post']) && $record['date_join_post'] ? date('Y-m-d', strtotime($record['date_join_post'])) : '' ?>">
                  <?= isset($record['date_join_post']) ? formatDate($record['date_join_post']) : '' ?>
                </td>
                <td data-field="date_prom" data-date="<?= isset($record['date_prom']) && $record['date_prom'] ? date('Y-m-d', strtotime($record['date_prom'])) : '' ?>">
                  <?= isset($record['date_prom']) ? formatDate($record['date_prom']) : '' ?>
                </td>
                <td data-field="date_join_area" data-date="<?= isset($record['date_join_area']) && $record['date_join_area'] ? date('Y-m-d', strtotime($record['date_join_area'])) : '' ?>">
                  <?= isset($record['date_join_area']) ? formatDate($record['date_join_area']) : '' ?>
                </td>
                <td>
                  <div class="action-buttons">
                    <button onclick="openEditModal(<?= $record['id'] ?>)" class="btn btn-sm btn-primary">Edit</button>
                    <form method="post" action="handle_entries.php" style="margin: 0; display: inline;" onsubmit="return confirmDelete()">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $record['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </div>
                </td>
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
