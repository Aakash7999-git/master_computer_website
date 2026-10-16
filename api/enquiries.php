<?php
/**
 * Master Computer Institute - Admin Enquiry Management API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// -------------------------------------------------------------
// 1. Export Filtered Records to CSV
// -------------------------------------------------------------
if ($action === 'export_csv') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR enquiry_code LIKE ?)";
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
    if (!empty($dateFrom)) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $dateFrom;
    }
    if (!empty($dateTo)) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $dateTo;
    }

    $sql = "SELECT enquiry_code, full_name, mobile, email, course_name, branch_name, city, preferred_batch, status, follow_up_date, admin_notes, created_at 
            FROM enquiries WHERE " . implode(" AND ", $where) . " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mci_enquiries_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Enquiry Code', 'Full Name', 'Mobile', 'Email', 'Course', 'Branch', 'City', 'Batch', 'Status', 'Follow-up Date', 'Admin Notes', 'Date Received']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['enquiry_code'],
            $row['full_name'],
            $row['mobile'],
            $row['email'] ?? '—',
            $row['course_name'],
            $row['branch_name'],
            $row['city'] ?? '—',
            $row['preferred_batch'] ?? '—',
            $row['status'],
            $row['follow_up_date'] ?? '—',
            $row['admin_notes'] ?? '',
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// -------------------------------------------------------------
// 2. List Enquiries with Filters and Pagination
// -------------------------------------------------------------
if ($action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $course = trim($_GET['course'] ?? '');
    $branch = trim($_GET['branch'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 15)));
    $offset = ($page - 1) * $limit;

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR enquiry_code LIKE ?)";
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
    if (!empty($dateFrom)) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $dateFrom;
    }
    if (!empty($dateTo)) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $dateTo;
    }

    $whereSql = implode(" AND ", $where);

    // Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE {$whereSql}");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Rows
    $sql = "SELECT id, enquiry_code, full_name, mobile, email, course_name, branch_name, city, preferred_batch, message, status, follow_up_date, admin_notes, created_at, updated_at
            FROM enquiries 
            WHERE {$whereSql} 
            ORDER BY id DESC 
            LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $enquiries = $stmt->fetchAll() ?: [];

    json_response(true, 'Enquiries retrieved.', [
        'enquiries'    => $enquiries,
        'pagination'   => [
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'current_page'  => $page,
            'limit'         => $limit
        ]
    ]);
}

// -------------------------------------------------------------
// 3. Single Enquiry Detail
// -------------------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM enquiries WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(false, 'Enquiry record not found.', [], 404);
    }
    json_response(true, 'Enquiry record found.', ['enquiry' => $row]);
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
    $followUp = !empty($_POST['follow_up_date']) ? date('Y-m-d', strtotime($_POST['follow_up_date'])) : null;

    $validStatuses = ['New', 'Contacted', 'Follow-up', 'Converted', 'Closed'];
    if (!in_array($status, $validStatuses, true)) {
        json_response(false, 'Invalid status value provided.', [], 422);
    }

    $stmt = $pdo->prepare("
        UPDATE enquiries 
        SET status = ?, admin_notes = ?, follow_up_date = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$status, $notes, $followUp, $id]);

    log_admin_activity($pdo, 'Update Status', 'enquiry', $id, "Updated enquiry #{$id} status to '{$status}'");

    json_response(true, 'Enquiry record updated successfully.', [
        'id'             => $id,
        'status'         => $status,
        'follow_up_date' => $followUp,
        'admin_notes'    => $notes
    ]);
}

// -------------------------------------------------------------
// 5. Delete Record
// -------------------------------------------------------------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
    $stmt->execute([$id]);

    log_admin_activity($pdo, 'Delete', 'enquiry', $id, "Deleted enquiry #{$id}");

    json_response(true, 'Enquiry deleted successfully.');
}

json_response(false, 'Unknown enquiry action requested.', [], 400);
