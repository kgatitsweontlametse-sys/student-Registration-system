<?php
// process_register.php
require 'config.php';

// Basic server-side validation and sanitization
function clean($v){
    return trim($v);
}

$full_name = isset($_POST['full_name']) ? clean($_POST['full_name']) : '';
$student_id = isset($_POST['student_id']) ? clean($_POST['student_id']) : '';
$email = isset($_POST['email']) ? filter_var(clean($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
$course = isset($_POST['course']) ? clean($_POST['course']) : null;
$enroll = !empty($_POST['enrollment_date']) ? $_POST['enrollment_date'] : null;

$errors = [];

// Validate required fields
if($full_name === '' || $student_id === '' || $email === ''){
    $errors[] = 'Full Name, Student ID and Email are required.';
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors[] = 'Email address is invalid.';
}
if(strlen($student_id) > 50){ $errors[] = 'Student ID too long.'; }
if(strlen($full_name) > 200){ $errors[] = 'Full name too long.'; }

if(count($errors) > 0){
    // In production use session-based flash; for demo we'll echo errors
    echo "<div style='padding:20px;'><strong>Errors:</strong><ul>";
    foreach($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>";
    echo "</ul><a href='register.php'>Back</a></div>";
    exit;
}

// Use prepared statement to prevent SQL injection
try{
    $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, email, dob, course, enrollment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = STATUS_PENDING; // default when admin registers manually
    $stmt->execute([$student_id, $full_name, $email, $dob, $course, $enroll, $status]);
    header("Location: dashboard.php?msg=created");
    exit;
} catch (PDOException $e){
    // Unique student_id constraint may fail; show friendly message
    if($e->getCode() == 23000){
        echo "A student with the given Student ID or Email already exists. <a href='register.php'>Back</a>";
    } else {
        echo "Database error: " . htmlspecialchars($e->getMessage());
    }
}
?>