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
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR admission_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
    }
    if (!empty($course)) {
        $where[] = "course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "branch_name = ?";
        $params[] = $branch;
    }

    $sql = "SELECT admission_code, full_name, dob, gender, mobile, email, city, address, qualification, course_name, branch_name, batch, status, admin_notes, created_at 
            FROM admissions WHERE " . implode(" AND ", $where) . " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mci_admissions_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Application Code', 'Applicant Name', 'DOB', 'Gender', 'Mobile', 'Email', 'City', 'Address', 'Qualification', 'Course', 'Branch', 'Batch', 'Status', 'Admin Notes', 'Date Submitted']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['admission_code'],
            $row['full_name'],
            $row['dob'] ?? '—',
            $row['gender'] ?? '—',
            $row['mobile'],
            $row['email'],
            $row['city'],
            $row['address'],
            $row['qualification'],
            $row['course_name'],
            $row['branch_name'],
            $row['batch'],
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
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR admission_code LIKE ?)";
        $sTerm = "%{$search}%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
    }
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
    }
    if (!empty($course)) {
        $where[] = "course_name = ?";
        $params[] = $course;
    }
    if (!empty($branch)) {
        $where[] = "branch_name = ?";
        $params[] = $branch;
    }

    $whereSql = implode(" AND ", $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM admissions WHERE {$whereSql}");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    $sql = "SELECT id, admission_code, full_name, dob, gender, mobile, email, city, address, qualification, course_name, branch_name, batch, message, status, admin_notes, created_at, updated_at
            FROM admissions 
            WHERE {$whereSql} 
            ORDER BY id DESC 
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
    $stmt = $pdo->prepare("SELECT * FROM admissions WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(false, 'Admission application not found.', [], 404);
    }
    json_response(true, 'Admission found.', ['admission' => $row]);
}

// -------------------------------------------------------------
// 4. Update Status and Notes
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

    $stmt = $pdo->prepare("UPDATE admissions SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $notes, $id]);

    log_admin_activity($pdo, 'Update Status', 'admission', $id, "Updated admission #{$id} status to '{$status}'");

    json_response(true, 'Admission application status updated.', [
        'id'          => $id,
        'status'      => $status,
        'admin_notes' => $notes
    ]);
}

// -------------------------------------------------------------
// 5. Delete Admission
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
