<?php
session_start();

// Set the admin name — change it to whatever you need
$_SESSION['admin_name'] = 'Botta Bharath';

// Redirect to the requests page
header("Location: requests.php");
exit;
