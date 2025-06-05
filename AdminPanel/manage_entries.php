<?php
session_start();
require_once 'config/config.php';

$adminName = $_SESSION['admin_name'] ?? 'Admin';

try {
    $stmt = $pdo->query("SELECT * FROM original_records ORDER BY entry_date DESC");
    $records = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $records = [];
}

$message = $_GET['message'] ?? null;
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
      const rows = document.querySelectorAll("#dataTable tbody tr.data-row");
      rows.forEach(row => {
        const match = row.innerText.toLowerCase().includes(input);
        row.style.display = match ? "" : "none";
        const formRow = row.nextElementSibling;
        if (formRow && formRow.classList.contains('edit-row')) {
          formRow.style.display = match ? "none" : "none";
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
      const row = document.querySelector(`tr[data-id="${id}"]`);
      
      // Fill the form with data from the row
      form.querySelector('[name="id"]').value = id;
      form.querySelector('[name="cpf"]').value = row.querySelector('[data-field="cpf"]').textContent;
      form.querySelector('[name="name"]').value = row.querySelector('[data-field="name"]').textContent;
      form.querySelector('[name="designation"]').value = row.querySelector('[data-field="designation"]').textContent;
      form.querySelector('[name="mobile"]').value = row.querySelector('[data-field="mobile"]').textContent;
      form.querySelector('[name="section"]').value = row.querySelector('[data-field="section"]').textContent;
      form.querySelector('[name="subsection"]').value = row.querySelector('[data-field="subsection"]').textContent;
      form.querySelector('[name="ext"]').value = row.querySelector('[data-field="ext"]').textContent;
      form.querySelector('[name="direct"]').value = row.querySelector('[data-field="direct"]').textContent;
      form.querySelector('[name="dob"]').value = row.querySelector('[data-field="dob"]').getAttribute('data-date');
      form.querySelector('[name="dor"]').value = row.querySelector('[data-field="dor"]').getAttribute('data-date');
      form.querySelector('[name="level"]').value = row.querySelector('[data-field="level"]').textContent;
      
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
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">
      </div>

      <div class="form-group">
        <label for="dor">Date of Retirement</label>
        <input type="date" id="dor" name="dor">
      </div>

      <div class="form-group">
        <label for="level">Level</label>
        <input type="text" id="level" name="level">
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
      <table class="table" id="dataTable">
        <thead>
          <tr>
            <th>CPF</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Mobile</th>
            <th>Section</th>
            <th>Subsection</th>
            <th>Ext</th>
            <th>Direct</th>
            <th>DOB</th>
            <th>DOR</th>
            <th>Level</th>
            <th>Entry Date</th>
            <th>Last Modified</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr>
              <td colspan="14" class="text-center">No records found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($records as $r): ?>
              <tr class="data-row" data-id="<?= $r['id'] ?>">
                <td data-field="cpf"><?= htmlspecialchars($r['cpf']) ?></td>
                <td data-field="name"><?= htmlspecialchars($r['name']) ?></td>
                <td data-field="designation"><?= htmlspecialchars($r['designation']) ?></td>
                <td data-field="mobile"><?= htmlspecialchars($r['mobile']) ?></td>
                <td data-field="section"><?= htmlspecialchars($r['section']) ?></td>
                <td data-field="subsection"><?= htmlspecialchars($r['subsection']) ?></td>
                <td data-field="ext"><?= htmlspecialchars($r['ext']) ?></td>
                <td data-field="direct"><?= htmlspecialchars($r['direct']) ?></td>
                <td data-field="dob" data-date="<?= $r['dob'] ? date('Y-m-d', strtotime($r['dob'])) : '' ?>">
                  <?= $r['dob'] ? date('Y-m-d', strtotime($r['dob'])) : '' ?>
                </td>
                <td data-field="dor" data-date="<?= $r['dor'] ? date('Y-m-d', strtotime($r['dor'])) : '' ?>">
                  <?= $r['dor'] ? date('Y-m-d', strtotime($r['dor'])) : '' ?>
                </td>
                <td data-field="level"><?= htmlspecialchars($r['level']) ?></td>
                <td><?= $r['entry_date'] ? date('Y-m-d H:i:s', strtotime($r['entry_date'])) : '' ?></td>
                <td><?= $r['last_modified'] ? date('Y-m-d H:i:s', strtotime($r['last_modified'])) : '' ?></td>
                <td>
                  <div class="action-buttons">
                    <button onclick="openEditModal(<?= $r['id'] ?>)" class="btn btn-sm btn-primary">Edit</button>
                    <form method="post" action="handle_entries.php" style="margin: 0; display: inline;" onsubmit="return confirmDelete()">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
