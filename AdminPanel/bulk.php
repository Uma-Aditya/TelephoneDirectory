<?php
session_start();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Entry</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <?php include "includes/sidebar.php"; ?>

  <main class="main-content">
    <div class="content">
      <h2>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h2>
      <p>This is the Add User page.</p>
    </div>
  </main>
</body>
</html>
