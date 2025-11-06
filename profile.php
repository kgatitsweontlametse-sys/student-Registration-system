<?php
require 'config.php';

// profile.php?student_id=S2025001
$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
if($student_id === ''){
    die('Student ID missing.');
}

// Use a function to fetch and return profile array
function get_student_profile($pdo, $student_id){
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $row = $stmt->fetch();
    return $row ? $row : null;
}

$student = get_student_profile($pdo, $student_id);
if(!$student){
    die('Student not found.');
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profile: <?php echo htmlspecialchars($student['full_name']); ?></title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Profile — <?php echo htmlspecialchars($student['full_name']); ?></h1>
      <div>
        <a class="ghost" href="dashboard.php">Back to Dashboard</a>
      </div>
    </div>

    <div>
      <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
      <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['dob']); ?></p>
      <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
      <p><strong>Enrollment Date:</strong> <?php echo htmlspecialchars($student['enrollment_date']); ?></p>
      <p><strong>Academic Status:</strong>
        <?php
           $status = $student['status'];
           echo '<span class="status-pill '.($status===STATUS_ACTIVE?'status-active':($status===STATUS_PENDING?'status-pending':'status-inactive')).'">'.htmlspecialchars($status).'</span>';
        ?>
      </p>
    </div>

    <div style="margin-top:12px;">
      <a class="primary" href="reports/profile_report.php?student_id=<?php echo urlencode($student['student_id']); ?>" target="_blank">Print / Download Profile</a>
      <a class="ghost" href="reports/confirmation_slip.php?student_id=<?php echo urlencode($student['student_id']); ?>" target="_blank">Registration Slip</a>
    </div>
  </div>
</body>
</html>