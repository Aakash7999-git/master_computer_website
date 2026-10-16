<?php
/**
 * Master Computer Institute - Admin Certificate Management API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
header('Content-Type: application/json; charset=utf-8');
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(certificate_no LIKE ? OR student_name LIKE ? OR course_name LIKE ?)";
        $term = "%{$search}%";
        $params[] = $term; $params[] = $term; $params[] = $term;
    }
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    $whereSql = implode(" AND ", $where);
    $cStmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE {$whereSql}");
    $cStmt->execute($params);
    $totalRecords = (int)$cStmt->fetchColumn();

    $sql = "SELECT * FROM certificates WHERE {$whereSql} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $certs = $stmt->fetchAll() ?: [];

    json_response(true, 'Certificates loaded.', [
        'certificates' => $certs,
        'pagination' => [
            'total_records' => $totalRecords,
            'total_pages'   => ceil($totalRecords / $limit),
            'current_page'  => $page,
            'limit'         => $limit
        ]
    ]);
}

if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $cert = $stmt->fetch();
    if (!$cert) json_response(false, 'Certificate not found.', [], 404);
    json_response(true, 'Certificate found.', ['certificate' => $cert]);
}

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);

    $id             = (int)($_POST['id'] ?? 0);
    $certificateNo  = strtoupper(sanitize_input($_POST['certificate_no'] ?? ''));
    $studentName    = sanitize_input($_POST['student_name'] ?? '');
    $fatherName     = sanitize_input($_POST['father_name'] ?? '');
    $courseName     = sanitize_input($_POST['course_name'] ?? '');
    $duration       = sanitize_input($_POST['duration'] ?? '');
    $issueDate      = sanitize_input($_POST['issue_date'] ?? date('Y-m-d'));
    $grade          = sanitize_input($_POST['grade'] ?? 'A');
    $percentage     = sanitize_input($_POST['percentage'] ?? '');
    $centerName     = sanitize_input($_POST['center_name'] ?? 'Master Computer Institute, Nawabganj');
    $status         = sanitize_input($_POST['status'] ?? 'Valid');
    $remarks        = sanitize_input($_POST['remarks'] ?? '');

    if (empty($certificateNo) || empty($studentName) || empty($courseName)) {
        json_response(false, 'Certificate number, student name, and course are required.', [], 422);
    }

    try {
        if ($id > 0) {
            $chk = $pdo->prepare("SELECT id FROM certificates WHERE certificate_no = ? AND id != ? LIMIT 1");
            $chk->execute([$certificateNo, $id]);
            if ($chk->fetch()) {
                json_response(false, "Certificate number '{$certificateNo}' already exists.", ['field' => 'certificate_no'], 422);
            }

            $stmt = $pdo->prepare("
                UPDATE certificates 
                SET certificate_no = ?, student_name = ?, father_name = ?, course_name = ?, duration = ?, issue_date = ?, grade = ?, percentage = ?, center_name = ?, status = ?, remarks = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$certificateNo, $studentName, $fatherName, $courseName, $duration, $issueDate, $grade, $percentage, $centerName, $status, $remarks, $id]);
            log_admin_activity($pdo, 'Edit Certificate', 'certificate', $id, "Updated certificate {$certificateNo} for {$studentName}");
            json_response(true, 'Certificate updated successfully.', ['id' => $id]);
        } else {
            $chk = $pdo->prepare("SELECT id FROM certificates WHERE certificate_no = ? LIMIT 1");
            $chk->execute([$certificateNo]);
            if ($chk->fetch()) {
                json_response(false, "Certificate number '{$certificateNo}' is already registered.", ['field' => 'certificate_no'], 422);
            }

            $stmt = $pdo->prepare("
                INSERT INTO certificates (certificate_no, student_name, father_name, course_name, duration, issue_date, grade, percentage, center_name, status, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$certificateNo, $studentName, $fatherName, $courseName, $duration, $issueDate, $grade, $percentage, $centerName, $status, $remarks]);
            $newId = (int)$pdo->lastInsertId();
            log_admin_activity($pdo, 'Add Certificate', 'certificate', $newId, "Added certificate {$certificateNo} for {$studentName}");
            json_response(true, 'Certificate added successfully.', ['id' => $newId], 201);
        }
    } catch (Exception $e) {
        error_log("Certificate save error: " . $e->getMessage());
        json_response(false, 'Database error while saving certificate.', [], 500);
    }
}

if ($action === 'toggle_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');
    if (!in_array($status, ['Valid', 'Revoked', 'Pending'], true)) json_response(false, 'Invalid status.', [], 422);

    $stmt = $pdo->prepare("UPDATE certificates SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    log_admin_activity($pdo, 'Toggle Certificate Status', 'certificate', $id, "Set certificate #{$id} status to {$status}");
    json_response(true, "Certificate status updated to {$status}.", ['id' => $id, 'status' => $status]);
}

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$id]);
    log_admin_activity($pdo, 'Delete Certificate', 'certificate', $id, "Deleted certificate #{$id}");
    json_response(true, 'Certificate deleted successfully.');
}

json_response(false, 'Unknown certificate action.', [], 400);
