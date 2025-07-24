<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Upload - Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .upload-form {
            border: 2px dashed #4CAF50;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .upload-form:hover {
            border-color: #45a049;
            background: #f0f7f0;
        }
        .success { 
            color: #28a745;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error { 
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning { 
            color: #ffc107;
            background: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .column-list {
            text-align: left;
            margin: 20px auto;
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .column-list ul {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            list-style: none;
            padding: 0;
        }
        .column-list li {
            margin: 5px 0;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 14px;
            color: #495057;
        }
        #uploadStatus {
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        .progress {
            width: 100%;
            height: 25px;
            background: #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            margin: 15px 0;
            display: none;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            transition: width 0.3s ease;
        }
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
            margin: 10px 0;
        }
        button[type="submit"] {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        button[type="submit"]:hover {
            background: #45a049;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #0056b3;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }
        h2 {
            color: #34495e;
            margin-bottom: 20px;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include "includes/sidebar.php"; ?>
    <main class="main-content">
      <div class="content">
        <h2>Bulk Upload</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['warning'])) {
            echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['warning']) . '</div>';
            unset($_SESSION['warning']);
        }
        ?>
        <div id="uploadStatus"></div>
        <div class="upload-form">
            <h3>Upload Excel File</h3>
            <p>Please upload your Excel file with the following columns:</p>
            <div class="column-list">
                <ul>
                    <li>cpf</li>
                    <li>name</li>
                    <li>designation</li>
                    <li>level</li>
                    <li>class</li>
                    <li>section</li>
                    <li>subsection</li>
                    <li>ext</li>
                    <li>direct</li>
                    <li>mobile</li>
                    <li>dob</li>
                    <li>dor</li>
                    <li>date_join_ongc</li>
                    <li>date_join_post</li>
                    <li>date_prom</li>
                    <li>date_join_area</li>
                </ul>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="excelFile" id="excelFile" accept=".xls,.xlsx,.csv">
                <br><br>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <button type="submit">Upload</button>
            </form>
        </div>
        <div style="text-align: center;">
            <a href="generate_excel_template.php" class="button">Download Template</a>
        </div>
      </div>
    </main>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var fileInput = document.getElementById('excelFile');
        var uploadStatus = document.getElementById('uploadStatus');
        var progress = document.querySelector('.progress');
        var progressBar = document.querySelector('.progress-bar');
        
        if (!fileInput.files.length) {
            uploadStatus.innerHTML = '<p class="error">Please select a file to upload</p>';
            uploadStatus.style.display = 'block';
            return;
        }

        // Validate file type
        var fileName = fileInput.files[0].name;
        var fileExt = fileName.split('.').pop().toLowerCase();
        if (!['csv', 'xls', 'xlsx'].includes(fileExt)) {
            uploadStatus.innerHTML = '<p class="error">Please select a valid Excel (.xls, .xlsx) or CSV file</p>';
            uploadStatus.style.display = 'block';
            return;
        }

        // Show progress bar
        progress.style.display = 'block';
        uploadStatus.style.display = 'block';
        uploadStatus.innerHTML = '<p>Uploading...</p>';
        progressBar.style.width = '50%';

        fetch('handle_bulk_upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            progressBar.style.width = '100%';
            return response.json();
        })
        .then(data => {
            let statusHtml = '';
            
            if (data.success) {
                statusHtml = `<p class="success">${data.message}</p>`;
                if (data.warning) {
                    statusHtml += `<div class="warning"><p>Warnings:</p><pre>${data.warning}</pre></div>`;
                }
            } else {
                statusHtml = `<p class="error">${data.message}</p>`;
                if (data.warning) {
                    statusHtml += `<div class="warning"><p>Additional Info:</p><pre>${data.warning}</pre></div>`;
                }
                if (data.debug) {
                    statusHtml += `<div class="error"><p>Debug Information:</p><pre>${JSON.stringify(data.debug, null, 2)}</pre></div>`;
                }
            }
            
            uploadStatus.innerHTML = statusHtml;
            
            // Reset form and progress bar after 3 seconds if successful
            if (data.success) {
                setTimeout(() => {
                    document.getElementById('uploadForm').reset();
                    progressBar.style.width = '0%';
                    progress.style.display = 'none';
                }, 3000);
            } else {
                progressBar.style.width = '0%';
                progress.style.display = 'none';
            }
        })
        .catch(error => {
            uploadStatus.innerHTML = `
                <p class="error">Upload failed. Please try again.</p>
                <pre class="error">${error.message}</pre>
            `;
            progressBar.style.width = '0%';
            progress.style.display = 'none';
        });
    });
    </script>
</body>
</html>
