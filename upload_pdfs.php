<?php
session_start();
require_once 'config/config.php';

$pdfs = [
  'kakinada' => 'Kakinada Directory',
  'ogt' => 'OGT Directory',
  'plqp' => 'PLQP Directory'
];

$uploadDir = 'uploads/';
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($pdfs as $key => $label) {
    if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
      if ($ext === 'pdf') {
        $target = $uploadDir . $key . '.pdf';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        if (move_uploaded_file($_FILES[$key]['tmp_name'], $target)) {
          $messages[] = "$label uploaded successfully.";
        } else {
          $messages[] = "Failed to upload $label.";
        }
      } else {
        $messages[] = "$label must be a PDF file.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Directory PDFs</title>
  <link rel="stylesheet" href="assets/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body>
<?php include "includes/sidebar.php"; ?>
<main class="main-content">
  <div class="content">
    <h2>Upload Directory PDFs</h2>
    <?php foreach ($messages as $msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="pdf-upload-section" style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;">
      <?php foreach ($pdfs as $key => $label): ?>
        <div class="pdf-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 24px 32px; margin: 10px; min-width: 260px; display: flex; flex-direction: column; align-items: center;">
          <span class="material-symbols-outlined" style="font-size: 48px; color: #e53935; margin-bottom: 10px;">picture_as_pdf</span>
          <label class="pdf-upload-label" for="<?= $key ?>" style="font-weight: 600; margin-bottom: 10px;">Upload <?= $label ?> (PDF only):</label>
          <input class="pdf-upload-input" type="file" name="<?= $key ?>" id="<?= $key ?>" accept="application/pdf" style="margin-bottom: 12px;">
          <?php $pdfPath = $uploadDir . $key . '.pdf'; if (file_exists($pdfPath)): ?>
            <a href="<?= $pdfPath ?>" class="pdf-link" target="_blank" style="margin-top: 8px; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 4px;">
              <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle;">download</span> Download <?= $label ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div style="flex-basis: 100%; height: 0;"></div>
      <button type="submit" class="button" style="margin-top: 20px; width: 100%; max-width: 300px; align-self: center;">Upload PDFs</button>
    </form>
  </div>
</main>
</body>
</html> 