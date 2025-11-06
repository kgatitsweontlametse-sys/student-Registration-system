<?php
require 'config.php';

$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
if($student_id === ''){
    die('Student ID required.');
}

// fetch current record
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
if(!$student) die('Student not found.');

// When POST arrives, process update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = trim($_POST['full_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $course = trim($_POST['course']);
    $enroll = !empty($_POST['enrollment_date']) ? $_POST['enrollment_date'] : null;
    $status = in_array($_POST['status'], [STATUS_ACTIVE, STATUS_PENDING, STATUS_INACTIVE]) ? $_POST['status'] : STATUS_PENDING;

    $errors = [];
    if($full_name === '' || $email === '') $errors[] = 'Name and email are required.';
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    if(count($errors) > 0){
        echo "<div><strong>Errors:</strong><ul>";
        foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>";
        echo "</ul></div>";
    } else {
        // Update with prepared statement
        $update = $pdo->prepare("UPDATE students SET full_name = ?, email = ?, dob = ?, course = ?, enrollment_date = ?, status = ? WHERE student_id = ?");
        $update->execute([$full_name, $email, $dob, $course, $enroll, $status, $student_id]);
        header("Location: profile.php?student_id=" . urlencode($student_id));
        exit;
    }
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Update Student</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="container">
    <h1>Update — <?php echo htmlspecialchars($student['full_name']); ?></h1>
    <form method="post" action="">
      <div class="form-field">
        <label>Full Name</label>
        <input name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
      </div>
      <div class="form-field">
        <label>Email</label>
        <input name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
      </div>
      <div class="form-field">
        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>">
      </div>
      <div class="form-field">
        <label>Course</label>
        <input name="course" value="<?php echo htmlspecialchars($student['course']); ?>">
      </div>
      <div class="form-field">
        <label>Enrollment Date</label>
        <input type="date" name="enrollment_date" value="<?php echo htmlspecialchars($student['enrollment_date']); ?>">
      </div>

      <div class="form-field">
        <label>Status</label>
        <select name="status">
          <option value="<?php echo STATUS_ACTIVE; ?>" <?php if($student['status']===STATUS_ACTIVE) echo 'selected'; ?>><?php echo STATUS_ACTIVE; ?></option>
          <option value="<?php echo STATUS_PENDING; ?>" <?php if($student['status']===STATUS_PENDING) echo 'selected'; ?>><?php echo STATUS_PENDING; ?></option>
          <option value="<?php echo STATUS_INACTIVE; ?>" <?php if($student['status']===STATUS_INACTIVE) echo 'selected'; ?>><?php echo STATUS_INACTIVE; ?></option>
        </select>
      </div>

      <button class="primary" type="submit">Save Changes</button>
      <a class="ghost" href="profile.php?student_id=<?php echo urlencode($student['student_id']); ?>">Cancel</a>
    </form>
  </div>
</body>
</html>