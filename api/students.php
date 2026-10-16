<?php
/**
 * Master Computer Institute - Admin Student Management API
 * Provides CRUD, search, status updates, and CSV export for enrolled students
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// -------------------------------------------------------------
// 1. Export CSV
// -------------------------------------------------------------
if ($action === 'export_csv') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $source = trim($_GET['source'] ?? '');
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(s.full_name LIKE ? OR s.mobile LIKE ? OR s.email LIKE ? OR s.student_code LIKE ? OR adm.admission_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "s.status = ?";
        $params[] = $status;
    }
    if (!empty($source)) {
        $where[] = "s.source = ?";
        $params[] = $source;
    }
    if (!empty($course)) {
        $where[] = "s.course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "s.branch_name = ?";
        $params[] = $branch;
    }

    $sql = "SELECT s.student_code, adm.admission_code, s.full_name, s.dob, s.gender, s.mobile, s.alternate_mobile,
                   s.email, s.city, s.address, s.qualification, s.course_name, s.branch_name, s.batch_timing,
                   s.admission_date, s.enrollment_date, s.source, s.status, s.remarks, s.created_at
            FROM students s
            LEFT JOIN admissions adm ON s.admission_id = adm.id
            WHERE " . implode(" AND ", $where) . " 
            ORDER BY s.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mci_students_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Student Code', 'Admission Code', 'Student Name', 'DOB', 'Gender', 'Mobile', 'Alt Mobile', 'Email', 'City', 'Address', 'Qualification', 'Course', 'Branch', 'Batch Timing', 'Admission Date', 'Enrollment Date', 'Source', 'Status', 'Remarks', 'Created At']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['student_code'],
            $row['admission_code'] ?? '—',
            $row['full_name'],
            $row['dob'] ?? '—',
            $row['gender'] ?? '—',
            $row['mobile'],
            $row['alternate_mobile'] ?? '—',
            $row['email'] ?? '—',
            $row['city'] ?? '—',
            $row['address'] ?? '—',
            $row['qualification'] ?? '—',
            $row['course_name'],
            $row['branch_name'],
            $row['batch_timing'] ?? '—',
            $row['admission_date'],
            $row['enrollment_date'],
            $row['source'],
            $row['status'],
            $row['remarks'] ?? '',
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// -------------------------------------------------------------
// 2. List Students with Filters and Pagination
// -------------------------------------------------------------
if ($action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $source = trim($_GET['source'] ?? '');
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(s.full_name LIKE ? OR s.mobile LIKE ? OR s.email LIKE ? OR s.student_code LIKE ? OR adm.admission_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "s.status = ?";
        $params[] = $status;
    }
    if (!empty($source)) {
        $where[] = "s.source = ?";
        $params[] = $source;
    }
    if (!empty($course)) {
        $where[] = "s.course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "s.branch_name = ?";
        $params[] = $branch;
    }

    $whereSql = implode(" AND ", $where);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM students s
        LEFT JOIN admissions adm ON s.admission_id = adm.id
        WHERE {$whereSql}
    ");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    $sql = "SELECT s.*, adm.admission_code, adm.created_at as admission_created_at
            FROM students s 
            LEFT JOIN admissions adm ON s.admission_id = adm.id
            WHERE {$whereSql} 
            ORDER BY s.id DESC 
            LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll() ?: [];

    json_response(true, 'Students list retrieved.', [
        'students'   => $students,
        'pagination' => [
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'current_page'  => $page,
            'limit'         => $limit
        ]
    ]);
}

// -------------------------------------------------------------
// 3. Single Student Details
// -------------------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT s.*, 
               adm.admission_code, adm.status as admission_status, adm.created_at as admission_date_submitted,
               a.username as created_by_username, a.full_name as created_by_name
        FROM students s
        LEFT JOIN admissions adm ON s.admission_id = adm.id
        LEFT JOIN admins a ON s.created_by_admin_id = a.id
        WHERE s.id = ? 
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        json_response(false, 'Student record not found.', [], 404);
    }
    json_response(true, 'Student record found.', ['student' => $student]);
}

// -------------------------------------------------------------
// 4. Update Student Information
// -------------------------------------------------------------
if ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $fullName = sanitize_input($_POST['full_name'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $altMobile = sanitize_input($_POST['alternate_mobile'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $dob = !empty($_POST['dob']) ? date('Y-m-d', strtotime($_POST['dob'])) : null;
    $gender = sanitize_input($_POST['gender'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $city = sanitize_input($_POST['city'] ?? 'Nawabganj');
    $qualification = sanitize_input($_POST['qualification'] ?? '');
    $courseName = sanitize_input($_POST['course_name'] ?? '');
    $branchName = sanitize_input($_POST['branch_name'] ?? '');
    $batch = sanitize_input($_POST['batch_timing'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'Active');
    $remarks = sanitize_input($_POST['remarks'] ?? '');

    if (empty($fullName)) {
        json_response(false, 'Please enter the student\'s full name.', [], 422);
    }
    $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($cleanMobile) < 10) {
        json_response(false, 'Please enter a valid 10-digit mobile number.', [], 422);
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Please enter a valid email address.', [], 422);
    }
    if (!in_array($status, ['Active', 'Completed', 'Dropped'], true)) {
        $status = 'Active';
    }

    // Lookup course_id and branch_id
    $courseId = null;
    if (!empty($courseName)) {
        $cStmt = $pdo->prepare("SELECT id FROM courses WHERE name = ? OR course_code = ? LIMIT 1");
        $cStmt->execute([$courseName, $courseName]);
        if ($cRow = $cStmt->fetch()) $courseId = (int)$cRow['id'];
    }

    $branchId = null;
    if (!empty($branchName)) {
        $bStmt = $pdo->prepare("SELECT id FROM branches WHERE name LIKE ? LIMIT 1");
        $bStmt->execute(["%{$branchName}%"]);
        if ($bRow = $bStmt->fetch()) $branchId = (int)$bRow['id'];
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE students 
            SET full_name = ?, dob = ?, gender = ?, mobile = ?, alternate_mobile = ?,
                email = ?, address = ?, city = ?, qualification = ?,
                course_id = ?, course_name = ?, branch_id = ?, branch_name = ?,
                batch_timing = ?, status = ?, remarks = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $fullName, $dob, $gender ?: null, $mobile, $altMobile ?: null,
            $email ?: null, $address ?: null, $city ?: 'Nawabganj', $qualification ?: null,
            $courseId, $courseName, $branchId, $branchName,
            $batch ?: null, $status, $remarks ?: null,
            $id
        ]);

        log_admin_activity($pdo, 'Update Student', 'student', $id, "Updated student record #{$id} ({$fullName})");

        json_response(true, 'Student information updated successfully.', [
            'id'     => $id,
            'status' => $status
        ]);
    } catch (Exception $e) {
        error_log("Update student error: " . $e->getMessage());
        json_response(false, 'Failed to update student information.', [], 500);
    }
}

// -------------------------------------------------------------
// 5. Delete Student
// -------------------------------------------------------------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    log_admin_activity($pdo, 'Delete', 'student', $id, "Deleted student record #{$id}");

    json_response(true, 'Student record deleted successfully.');
}

json_response(false, 'Invalid student action requested.', [], 400);
