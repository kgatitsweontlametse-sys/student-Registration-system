<?php
// register.php - Admin registration form
require 'config.php';

// For demonstration assume admin-only access (no auth implemented)
$errors = [];
$old = [
  'full_name'=>'', 'student_id'=>'', 'email'=>'', 'dob'=>'', 'course'=>'', 'enrollment_date'=>''
];
if(isset($_GET['errors'])){
    // This example reads errors via GET for simplicity — production should use sessions/flash
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register Student</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/scripts.js"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Student Registration (Admin)</h1>
      <a class="ghost" href="dashboard.php">Manage Students</a>
    </div>

    <form method="post" action="process_register.php" onsubmit="return validateRegisterForm();">
      <div class="form-field">
        <label>Full Name</label>
        <input id="full_name" name="full_name" type="text" maxlength="200" required>
      </div>
      <div class="form-field">
        <label>Student ID</label>
        <input id="student_id" name="student_id" type="text" maxlength="50" required>
      </div>
      <div class="form-field">
        <label>Email</label>
        <input id="email" name="email" type="email" maxlength="150" required>
      </div>
      <div class="form-field">
        <label>Date of Birth</label>
        <input name="dob" type="date">
      </div>
      <div class="form-field">
        <label>Course of Study</label>
        <input name="course" type="text" maxlength="150">
      </div>
      <div class="form-field">
        <label>Enrollment Date</label>
        <input name="enrollment_date" type="date">
      </div>

      <button class="primary" type="submit">Register Student</button>
    </form>
  </div>
</body>
</html>