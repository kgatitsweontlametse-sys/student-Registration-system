<?php
require '../config.php';
$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
if($student_id === '') die('Student ID missing.');
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$s = $stmt->fetch();
if(!$s) die('Student not found.');

$timestamp = date('Y-m-d H:i:s');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Registration Slip — <?php echo htmlspecialchars($s['student_id']); ?></title>
  <style>
    body{ font-family: Arial, sans-serif; padding:20px; }
    .slip{ border:1px dashed #333; padding:16px; width:700px; margin:0 auto;}
    .label{ font-weight:700; width:160px; display:inline-block; }
  </style>
</head>
<body>
  <div class="slip">
    <h2>Registration Confirmation Slip</h2>
    <div><span class="label">Generated:</span> <?php echo htmlspecialchars($timestamp); ?></div>
    <div><span class="label">Name:</span> <?php echo htmlspecialchars($s['full_name']); ?></div>
    <div><span class="label">Student ID:</span> <?php echo htmlspecialchars($s['student_id']); ?></div>
    <div><span class="label">Course:</span> <?php echo htmlspecialchars($s['course']); ?></div>
    <div><span class="label">Status:</span> <?php echo htmlspecialchars($s['status']); ?></div>
    <div style="margin-top:12px;"><button onclick="window.print()">Print Slip</button></div>
  </div>
</body>
</html>