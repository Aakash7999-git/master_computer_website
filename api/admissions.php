<?php
/**
 * Master Computer Institute - Admin Admission Management API
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
        $where[] = "(adm.full_name LIKE ? OR adm.mobile LIKE ? OR adm.email LIKE ? OR adm.admission_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "adm.status = ?";
        $params[] = $status;
    }
    if (!empty($source) && in_array($source, ['Website', 'Offline'], true)) {
        $where[] = "adm.source = ?";
        $params[] = $source;
    }
    if (!empty($course)) {
        $where[] = "adm.course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "adm.branch_name = ?";
        $params[] = $branch;
    }

    $sql = "SELECT adm.admission_code, adm.full_name, adm.dob, adm.gender, adm.mobile, adm.alternate_mobile,
                   adm.email, adm.city, adm.address, adm.qualification, adm.course_name, adm.branch_name,
                   adm.batch, adm.admission_date, adm.source, adm.status, adm.admin_notes, adm.created_at,
                   s.student_code
            FROM admissions adm
            LEFT JOIN students s ON s.admission_id = adm.id
            WHERE " . implode(" AND ", $where) . " 
            ORDER BY adm.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mci_admissions_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Application Code', 'Student Reg Code', 'Applicant Name', 'DOB', 'Gender', 'Mobile', 'Alt Mobile', 'Email', 'City', 'Address', 'Qualification', 'Course', 'Branch', 'Batch', 'Admission Date', 'Source', 'Status', 'Admin Notes', 'Date Recorded']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['admission_code'],
            $row['student_code'] ?? '—',
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
            $row['batch'] ?? '—',
            $row['admission_date'] ?? date('Y-m-d', strtotime($row['created_at'])),
            $row['source'] ?? 'Website',
            $row['status'],
            $row['admin_notes'] ?? '',
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// -------------------------------------------------------------
// 2. List Admissions
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
        $where[] = "(adm.full_name LIKE ? OR adm.mobile LIKE ? OR adm.email LIKE ? OR adm.admission_code LIKE ? OR s.student_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "adm.status = ?";
        $params[] = $status;
    }
    if (!empty($source) && in_array($source, ['Website', 'Offline'], true)) {
        $where[] = "adm.source = ?";
        $params[] = $source;
    }
    if (!empty($course)) {
        $where[] = "adm.course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "adm.branch_name = ?";
        $params[] = $branch;
    }

    $whereSql = implode(" AND ", $where);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM admissions adm 
        LEFT JOIN students s ON s.admission_id = adm.id
        WHERE {$whereSql}
    ");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    $sql = "SELECT adm.id, adm.admission_code, adm.full_name, adm.dob, adm.gender, adm.mobile, adm.alternate_mobile,
                   adm.email, adm.city, adm.address, adm.qualification, adm.course_name, adm.branch_name,
                   adm.batch, adm.admission_date, adm.message, adm.status, adm.source, adm.admin_notes,
                   adm.created_by_admin_id, adm.created_at, adm.updated_at,
                   s.id as student_id, s.student_code
            FROM admissions adm 
            LEFT JOIN students s ON s.admission_id = adm.id
            WHERE {$whereSql} 
            ORDER BY adm.id DESC 
            LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $admissions = $stmt->fetchAll() ?: [];

    json_response(true, 'Admissions retrieved.', [
        'admissions' => $admissions,
        'pagination' => [
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'current_page'  => $page,
            'limit'         => $limit
        ]
    ]);
}

// -------------------------------------------------------------
// 3. Single Admission
// -------------------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT adm.*, 
               s.id as student_id, s.student_code, s.status as student_status,
               a.username as created_by_username, a.full_name as created_by_name
        FROM admissions adm
        LEFT JOIN students s ON s.admission_id = adm.id
        LEFT JOIN admins a ON adm.created_by_admin_id = a.id
        WHERE adm.id = ? 
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(false, 'Admission application not found.', [], 404);
    }
    json_response(true, 'Admission found.', ['admission' => $row]);
}

// -------------------------------------------------------------
// 4. Create Offline Student Admission
// -------------------------------------------------------------
if ($action === 'create_offline') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

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
    $batch = sanitize_input($_POST['batch'] ?? 'Morning');
    $admissionDate = !empty($_POST['admission_date']) ? date('Y-m-d', strtotime($_POST['admission_date'])) : date('Y-m-d');
    $status = sanitize_input($_POST['status'] ?? 'Enrolled');
    $adminNotes = sanitize_input($_POST['admin_notes'] ?? '');

    // Validation
    if (empty($fullName)) {
        json_response(false, 'Please enter the student\'s full name.', ['field' => 'full_name'], 422);
    }
    $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($cleanMobile) < 10) {
        json_response(false, 'Please enter a valid 10-digit primary mobile number.', ['field' => 'mobile'], 422);
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Please enter a valid email address.', ['field' => 'email'], 422);
    }
    if (empty($courseName)) {
        json_response(false, 'Please select the enrolled course.', ['field' => 'course_name'], 422);
    }
    if (empty($branchName)) {
        json_response(false, 'Please select the admission branch.', ['field' => 'branch_name'], 422);
    }

    $validStatuses = ['New', 'Under Review', 'Approved', 'Enrolled'];
    if (!in_array($status, $validStatuses, true)) {
        $status = 'Enrolled';
    }

    try {
        $pdo->beginTransaction();

        // Course ID lookup
        $courseId = null;
        $cStmt = $pdo->prepare("SELECT id FROM courses WHERE name = ? OR course_code = ? LIMIT 1");
        $cStmt->execute([$courseName, $courseName]);
        if ($cRow = $cStmt->fetch()) {
            $courseId = (int)$cRow['id'];
        }

        // Branch ID lookup
        $branchId = null;
        $bStmt = $pdo->prepare("SELECT id FROM branches WHERE name LIKE ? LIMIT 1");
        $bStmt->execute(["%{$branchName}%"]);
        if ($bRow = $bStmt->fetch()) {
            $branchId = (int)$bRow['id'];
        }

        $admissionCode = generate_admission_code($pdo);
        $adminInfo = current_admin();
        $adminId = $adminInfo['id'] ?? null;

        $insertAdm = $pdo->prepare("
            INSERT INTO admissions (
                admission_code, full_name, dob, gender, mobile, alternate_mobile,
                email, address, city, qualification, course_id, course_name, branch_id,
                branch_name, batch, admission_date, message, status, source,
                created_by_admin_id, admin_notes
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, 'Offline walk-in registration', ?, 'Offline',
                ?, ?
            )
        ");

        $insertAdm->execute([
            $admissionCode,
            $fullName,
            $dob,
            $gender ?: null,
            $mobile,
            $altMobile ?: null,
            $email ?: null,
            $address ?: null,
            $city ?: 'Nawabganj',
            $qualification ?: null,
            $courseId,
            $courseName,
            $branchId,
            $branchName,
            $batch ?: 'Morning',
            $admissionDate,
            $status,
            $adminId,
            $adminNotes ?: null
        ]);

        $newAdmissionId = (int)$pdo->lastInsertId();
        $newStudentId = null;
        $studentCode = null;

        // If registered with 'Enrolled' status, automatically create the student record
        if ($status === 'Enrolled') {
            $newStudentId = enroll_student_from_admission($pdo, $newAdmissionId, $adminId);
            if ($newStudentId) {
                $stuStmt = $pdo->prepare("SELECT student_code FROM students WHERE id = ?");
                $stuStmt->execute([$newStudentId]);
                $studentCode = $stuStmt->fetchColumn();
            }
        }

        $pdo->commit();

        log_admin_activity($pdo, 'Register Offline Admission', 'admission', $newAdmissionId, "Registered offline student admission {$admissionCode} for {$fullName} ({$courseName})");

        json_response(true, "Offline admission {$admissionCode} registered successfully!" . ($studentCode ? " Student ID: {$studentCode}" : ""), [
            'admission_id'   => $newAdmissionId,
            'admission_code' => $admissionCode,
            'student_id'     => $newStudentId,
            'student_code'   => $studentCode,
            'status'         => $status,
            'full_name'      => $fullName
        ], 201);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Offline admission creation error: " . $e->getMessage());
        json_response(false, 'Failed to register offline admission: ' . $e->getMessage(), [], 500);
    }
}

// -------------------------------------------------------------
// 5. Update Status and Notes
// -------------------------------------------------------------
if ($action === 'update_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');
    $notes = sanitize_input($_POST['admin_notes'] ?? '');

    $validStatuses = ['New', 'Under Review', 'Approved', 'Enrolled', 'Rejected'];
    if (!in_array($status, $validStatuses, true)) {
        json_response(false, 'Invalid application status provided.', [], 422);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE admissions SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $notes, $id]);

        $studentId = null;
        $studentCode = null;

        // When updated to 'Enrolled', automatically create or activate the student record
        if ($status === 'Enrolled') {
            $adminInfo = current_admin();
            $studentId = enroll_student_from_admission($pdo, $id, $adminInfo['id'] ?? null);
            if ($studentId) {
                $stuStmt = $pdo->prepare("SELECT student_code FROM students WHERE id = ?");
                $stuStmt->execute([$studentId]);
                $studentCode = $stuStmt->fetchColumn();
            }
        } elseif ($status === 'Rejected') {
            // If rejected, set any linked student to 'Dropped'
            $pdo->prepare("UPDATE students SET status = 'Dropped', updated_at = NOW() WHERE admission_id = ?")->execute([$id]);
        }

        $pdo->commit();

        log_admin_activity($pdo, 'Update Status', 'admission', $id, "Updated admission #{$id} status to '{$status}'" . ($studentCode ? " (Student: {$studentCode})" : ""));

        json_response(true, 'Admission application status updated.' . ($studentCode ? " Student record active ({$studentCode})." : ""), [
            'id'           => $id,
            'status'       => $status,
            'student_id'   => $studentId,
            'student_code' => $studentCode,
            'admin_notes'  => $notes
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Update admission status error: " . $e->getMessage());
        json_response(false, 'Failed to update admission status.', [], 500);
    }
}

// -------------------------------------------------------------
// 6. Delete Admission
// -------------------------------------------------------------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM admissions WHERE id = ?");
    $stmt->execute([$id]);

    log_admin_activity($pdo, 'Delete', 'admission', $id, "Deleted admission #{$id}");

    json_response(true, 'Admission record deleted successfully.');
}

json_response(false, 'Invalid admission action.', [], 400);
