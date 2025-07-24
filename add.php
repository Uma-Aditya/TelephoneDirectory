<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Entry</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      padding: 20px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .form-grid .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-grid label {
      margin-bottom: 5px;
      font-size: 14px;
      color: #666;
    }

    .form-grid input {
      width: 100%;
      padding: 8px;
      font-size: 14px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .form-grid button {
      grid-column: span 2;
      padding: 10px;
      font-size: 14px;
      cursor: pointer;
      background-color: #1e1e2f;
      color: white;
      border: none;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }

    .form-grid button:hover {
      background-color: #2a2a3f;
    }

    .alert {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 4px;
      font-size: 14px;
    }

    .alert.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .required::after {
      content: " *";
      color: #dc3545;
    }
  </style>
</head>
<body>
  <?php include "includes/sidebar.php"; ?>

  <main class="main-content">
    <div class="content">
      <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
      <h4>Add New Directory Entry</h4>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert success">✅ Entry added successfully to the directory.</div>
      <?php elseif (isset($_GET['error'])): ?>
        <div class="alert error">
          ❌ Error: <?= $message ? htmlspecialchars($message) : 'Failed to add entry. Please try again.' ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="handle_add.php" class="form-grid">
        <div class="form-group">
          <label class="required" for="cpf">CPF</label>
          <input type="number" id="cpf" name="cpf" required>
        </div>
        
        <div class="form-group">
          <label class="required" for="name">Full Name</label>
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
          <input type="text" id="did_number" name="did_number" placeholder="Enter DID number">
        </div>
        
        <div class="form-group">
          <label for="dob">Date of Birth</label>
          <input type="text" id="dob" name="dob" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}">
        </div>
        
        <div class="form-group">
          <label for="dor">Date of Retirement</label>
          <input type="text" id="dor" name="dor" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}">
        </div>
        
        <div class="form-group">
          <label for="level">Level</label>
          <input type="text" id="level" name="level">
        </div>
        
        <div class="form-group">
          <label for="seating_location">Seating Location</label>
          <input type="text" id="seating_location" name="seating_location" placeholder="Enter seating location">
        </div>
        
        <div class="form-group">
          <label for="class">Class</label>
          <input type="text" id="class" name="class">
        </div>
        <div class="form-group">
          <label for="date_join_ongc">Date Join ONGC</label>
          <input type="date" id="date_join_ongc" name="date_join_ongc">
        </div>
        <div class="form-group">
          <label for="date_join_post">Date Join Post</label>
          <input type="date" id="date_join_post" name="date_join_post">
        </div>
        <div class="form-group">
          <label for="date_prom">Date Prom</label>
          <input type="date" id="date_prom" name="date_prom">
        </div>
        <div class="form-group">
          <label for="date_join_area">Date Join Area</label>
          <input type="date" id="date_join_area" name="date_join_area">
        </div>
        
        <button type="submit" class="btn btn-primary">Add Entry</button>
      </form>
    </div>
  </main>

  <script>
  // Date format validation
  function validateDateFormat(input) {
      const datePattern = /^(\d{2})-(\d{2})-(\d{4})$/;
      if (!input.value) return true; // Allow empty dates
      
      if (!datePattern.test(input.value)) {
          input.setCustomValidity('Please enter date in DD-MM-YYYY format');
          return false;
      }
      
      const [_, day, month, year] = input.value.match(datePattern);
      const date = new Date(year, month - 1, day);
      const valid = date.getDate() == day && date.getMonth() == month - 1 && date.getFullYear() == year;
      
      input.setCustomValidity(valid ? '' : 'Please enter a valid date');
      return valid;
  }

  // Add event listeners to date inputs
  document.addEventListener('DOMContentLoaded', function() {
      const dateInputs = document.querySelectorAll('input[type="date"]');
      dateInputs.forEach(input => {
          input.addEventListener('input', function() {
              validateDateFormat(this);
          });
      });
  });
  </script>
</body>
</html>
