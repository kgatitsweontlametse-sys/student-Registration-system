<?php
require 'config.php';

// delete.php?id=S2025001
$student_id = isset($_GET['id']) ? trim($_GET['id']) : '';
if($student_id === '') die('Student ID missing.');

try{
    // Fetch for logging
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    if(!$student) die('Student not found.');

    // Delete
    $del = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $del->execute([$student_id]);

    // Log deletion to file for recovery tracking
    $logDir = __DIR__ . '/logs';
    if(!is_dir($logDir)) mkdir($logDir, 0755, true);
    $logFile = $logDir . '/deletions.log';
    $entry = date('c') . " | Deleted student_id={$student['student_id']} | name=" . addslashes($student['full_name']) . " | email=" . addslashes($student['email']) . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

    header("Location: dashboard.php?msg=deleted");
    exit;
} catch (Exception $e){
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>