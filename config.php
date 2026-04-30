<?php
// Configuration File - config.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'asfour_survey');
define('DB_USER', 'root'); // Ganti jika berbeda
define('DB_PASS', '');     // Ganti jika berbeda

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Function to log admin activities
function logActivity($pdo, $admin_id, $action, $details = "") {
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$admin_id, $action, $details]);
}

// Check survey status
function isSurveyOpen($pdo) {
    $stmt = $pdo->query("SELECT survey_status FROM settings WHERE id = 1");
    $result = $stmt->fetch();
    return $result['survey_status'] === 'open';
}
?>
