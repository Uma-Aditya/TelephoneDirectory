<?php
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf         = $_POST['cpf'];
    $name        = $_POST['name'];
    $designation = $_POST['designation'];
    $mobile      = $_POST['mobile'];
    $section     = $_POST['section'];
    $subsection  = $_POST['subsection'] ?? null;
    $ext         = $_POST['ext'] ?? null;
    $direct      = $_POST['direct'] ?? null;
    $dob         = $_POST['dob'] ?? null;
    $dor         = $_POST['dor'] ?? null;
    $level       = $_POST['level'] ?? null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO requests 
            (cpf, name, designation, mobile, section, subsection, ext, direct, dob, dor, level) 
            VALUES 
            (:cpf, :name, :designation, :mobile, :section, :subsection, :ext, :direct, :dob, :dor, :level)
        ");

        $stmt->execute([
            ':cpf' => $cpf,
            ':name' => $name,
            ':designation' => $designation,
            ':mobile' => $mobile,
            ':section' => $section,
            ':subsection' => $subsection,
            ':ext' => $ext,
            ':direct' => $direct,
            ':dob' => $dob,
            ':dor' => $dor,
            ':level' => $level
        ]);

        header("Location: index.php?success=1");
        exit;
    } catch (PDOException $e) {
        header("Location: index.php?error=1");
        exit;
    }
}
