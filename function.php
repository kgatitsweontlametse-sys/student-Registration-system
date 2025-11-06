<?php
/**
 * function.php
 *
 * Reusable helper functions for the Student Registration & Academic Management System.
 * Intended to be included after config.php so $pdo and STATUS_* constants are available.
 *
 * Usage:
 *   require 'config.php';
 *   require 'function.php';
 *
 * NOTE: All DB functions expect a valid PDO instance ($pdo) passed explicitly.
 */

/**
 * Trim and sanitize a string input.
 */
function sanitize(string $value): string {
    return trim(filter_var($value, FILTER_UNSAFE_RAW));
}

/**
 * Sanitize email specifically.
 */
function sanitize_email(string $email): string {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Validate required fields exist and are not empty.
 * $fields = ['full_name','student_id', ...]
 * $data = $_POST or associative array
 * Returns array of error messages (empty if none).
 */
function validate_required(array $fields, array $data): array {
    $errors = [];
    foreach ($fields as $f) {
        if (!isset($data[$f]) || trim((string)$data[$f]) === '') {
            $errors[] = "The field '{$f}' is required.";
        }
    }
    return $errors;
}

/**
 * Validate email format. Returns true if valid.
 */
function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate student id format.
 * This example allows alphanumeric, dashes and underscores, length limit 1..50.
 */
function validate_student_id(string $student_id): bool {
    if ($student_id === '') return false;
    if (strlen($student_id) > 50) return false;
    return preg_match('/^[A-Za-z0-9\-\_]+$/', $student_id) === 1;
}

/**
 * Return CSS class for status pills (for use in templates).
 */
function status_class(string $status): string {
    if (defined('STATUS_ACTIVE') && $status === STATUS_ACTIVE) return 'status-active';
    if (defined('STATUS_PENDING') && $status === STATUS_PENDING) return 'status-pending';
    if (defined('STATUS_INACTIVE') && $status === STATUS_INACTIVE) return 'status-inactive';
    return 'status-inactive';
}

/**
 * Fetch a student profile by student_id.
 * Returns associative array or null if not found.
 */
function get_student_by_id(PDO $pdo, string $student_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row === false ? null : $row;
}

/**
 * Fetch multiple students with optional search, order and pagination.
 * - $search: string to match against full_name, student_id, email or course (optional)
 * - $order: column name (whitelist enforced)
 * - $dir: 'ASC'|'DESC'
 * - $limit, $offset: pagination
 *
 * Returns an array of associative student rows.
 */
function get_students(PDO $pdo, ?string $search = null, string $order = 'created_at', string $dir = 'DESC', ?int $limit = null, ?int $offset = null): array {
    // whitelist columns to prevent SQL injection in ORDER BY
    $allowedOrder = ['created_at','full_name','enrollment_date','student_id'];
    if (!in_array($order, $allowedOrder, true)) $order = 'created_at';
    $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

    $sql = "SELECT * FROM students";
    $params = [];
    if ($search !== null && $search !== '') {
        $sql .= " WHERE full_name LIKE :q OR student_id LIKE :q OR email LIKE :q OR course LIKE :q";
        $params[':q'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY {$order} {$dir}";
    if (is_int($limit) && $limit > 0) {
        $sql .= " LIMIT :limit";
        if (is_int($offset) && $offset >= 0) {
            $sql .= " OFFSET :offset";
        }
    }

    $stmt = $pdo->prepare($sql);

    // Bind params carefully (PDO doesn't accept binding LIMIT with named params on all drivers)
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    if (is_int($limit) && $limit > 0) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if (is_int($offset) && $offset >= 0) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insert a new student record using prepared statements.
 * $data must include: student_id, full_name, email, dob (or null), course (or null), enrollment_date (or null), status (optional)
 *
 * Returns true on success. Throws PDOException on failure.
 */
function insert_student(PDO $pdo, array $data): bool {
    $sql = "INSERT INTO students (student_id, full_name, email, dob, course, enrollment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $status = $data['status'] ?? (defined('STATUS_PENDING') ? STATUS_PENDING : 'Pending');
    return $stmt->execute([
        $data['student_id'],
        $data['full_name'],
        $data['email'],
        $data['dob'] ?? null,
        $data['course'] ?? null,
        $data['enrollment_date'] ?? null,
        $status
    ]);
}

/**
 * Update an existing student record.
 * $student_id identifies the record, $data contains columns to change.
 * Allowed keys: full_name, email, dob, course, enrollment_date, status
 *
 * Returns true on success.
 */
function update_student(PDO $pdo, string $student_id, array $data): bool {
    $fields = [];
    $params = [];

    $allowed = ['full_name','email','dob','course','enrollment_date','status'];
    foreach ($allowed as $col) {
        if (array_key_exists($col, $data)) {
            $fields[] = "{$col} = ?";
            $params[] = $data[$col];
        }
    }
    if (count($fields) === 0) {
        // nothing to update
        return true;
    }
    $params[] = $student_id;
    $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE student_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete a student by student_id.
 * Logs deletion for audit and recovery.
 * Returns true on success.
 */
function delete_student(PDO $pdo, string $student_id): bool {
    // fetch before delete for logging
    $student = get_student_by_id($pdo, $student_id);
    if (!$student) {
        throw new RuntimeException("Student not found: {$student_id}");
    }

    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $result = $stmt->execute([$student_id]);
    if ($result) {
        log_deletion($student);
    }
    return $result;
}

/**
 * Append deletion information to logs/deletions.log
 */
function log_deletion(array $student): void {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/deletions.log';
    $entry = sprintf(
        "%s | DELETED student_id=%s | name=%s | email=%s | course=%s | enrollment_date=%s%s",
        date('c'),
        $student['student_id'] ?? '',
        addslashes($student['full_name'] ?? ''),
        addslashes($student['email'] ?? ''),
        addslashes($student['course'] ?? ''),
        addslashes($student['enrollment_date'] ?? ''),
        PHP_EOL
    );
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Simple helper to redirect with optional message in GET string.
 */
function redirect_with_message(string $url, ?string $msg = null): void {
    if ($msg !== null) {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . 'msg=' . urlencode($msg);
    }
    header("Location: {$url}");
    exit;
}

/**
 * Generate a profile PDF using FPDF if available.
 * Returns binary PDF string on success or false if FPDF not installed.
 *
 * Usage:
 *   $pdf = generate_profile_pdf($student);
 *   if ($pdf !== false) {
 *       header('Content-Type: application/pdf');
 *       echo $pdf;
 *   }
 */
function generate_profile_pdf(array $student) {
    if (!class_exists('FPDF')) {
        return false;
    }

    // Basic PDF generation using FPDF (expects FPDF installed/included)
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Profile Summary Report', 0, 1, 'C');

    $pdf->Ln(4);
    $pdf->SetFont('Arial', '', 12);
    $rows = [
        ['Full Name', $student['full_name'] ?? ''],
        ['Student ID', $student['student_id'] ?? ''],
        ['Email', $student['email'] ?? ''],
        ['Date of Birth', $student['dob'] ?? ''],
        ['Course', $student['course'] ?? ''],
        ['Enrollment Date', $student['enrollment_date'] ?? ''],
        ['Status', $student['status'] ?? ''],
    ];
    foreach ($rows as $r) {
        $pdf->Cell(50, 8, $r[0] . ':', 0, 0);
        $pdf->MultiCell(0, 8, $r[1] ?? '');
    }

    return $pdf->Output('', 'S'); // return as string
}

/**
 * Simple helper to convert a student record into a printable HTML card (string).
 * Useful for returning printable content from an API.
 */
function student_to_html_card(array $s): string {
    $html = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:700px;margin:0 auto;">';
    $html .= '<h2>Profile Summary Report</h2>';
    $html .= '<p><strong>Full Name:</strong> ' . htmlspecialchars($s['full_name'] ?? '') . '</p>';
    $html .= '<p><strong>Student ID:</strong> ' . htmlspecialchars($s['student_id'] ?? '') . '</p>';
    $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($s['email'] ?? '') . '</p>';
    $html .= '<p><strong>Date of Birth:</strong> ' . htmlspecialchars($s['dob'] ?? '') . '</p>';
    $html .= '<p><strong>Course:</strong> ' . htmlspecialchars($s['course'] ?? '') . '</p>';
    $html .= '<p><strong>Enrollment Date:</strong> ' . htmlspecialchars($s['enrollment_date'] ?? '') . '</p>';
    $html .= '<p><strong>Status:</strong> ' . htmlspecialchars($s['status'] ?? '') . '</p>';
    $html .= '</div>';
    return $html;
}