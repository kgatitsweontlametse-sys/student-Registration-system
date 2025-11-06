<?php
require 'config.php';

// Optional sorting/filtering from GET
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$order = isset($_GET['order']) && in_array($_GET['order'], ['full_name','enrollment_date']) ? $_GET['order'] : 'created_at';
$dir = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'ASC' : 'DESC';

// Build query: use simple LIKE for search (safe because we use prepared statements)
$sql = "SELECT * FROM students";
$params = [];
if($search !== ''){
    $sql .= " WHERE full_name LIKE :q OR student_id LIKE :q OR email LIKE :q OR course LIKE :q";
    $params[':q'] = "%{$search}%";
}
$sql .= " ORDER BY {$order} {$dir}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

function statusClass($s){
    if($s === STATUS_ACTIVE) return 'status-active';
    if($s === STATUS_PENDING) return 'status-pending';
    return 'status-inactive';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Students Dashboard</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/scripts.js"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Student List (Admin)</h1>
      <a class="ghost" href="register.php">Register New Student</a>
    </div>

    <div class="search-row">
      <input id="searchInput" placeholder="Search by name, ID, email or course" oninput="filterTable()" />
      <a class="ghost" href="dashboard.php">Reset</a>
    </div>

    <table id="studentsTable">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Course</th>
          <th>Enrollment</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($students as $s): ?>
        <tr>
          <td><?php echo htmlspecialchars($s['student_id']); ?></td>
          <td><?php echo htmlspecialchars($s['full_name']); ?></td>
          <td><?php echo htmlspecialchars($s['email']); ?></td>
          <td><?php echo htmlspecialchars($s['course']); ?></td>
          <td><?php echo htmlspecialchars($s['enrollment_date']); ?></td>
          <td><span class="status-pill <?php echo statusClass($s['status']); ?>"><?php echo htmlspecialchars($s['status']); ?></span></td>
          <td class="actions">
            <a class="ghost" href="profile.php?student_id=<?php echo urlencode($s['student_id']); ?>">View</a>
            <a class="ghost" href="update.php?student_id=<?php echo urlencode($s['student_id']); ?>">Update</a>
            <button class="ghost" onclick="confirmDelete('<?php echo addslashes($s['student_id']); ?>','<?php echo addslashes($s['full_name']); ?>')">Delete</button>
            <a class="ghost" href="reports/profile_report.php?student_id=<?php echo urlencode($s['student_id']); ?>" target="_blank">Report</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</body>
</html>