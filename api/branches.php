<?php
/**
 * Master Computer Institute - Admin Branch Management API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
header('Content-Type: application/json; charset=utf-8');
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT b.*, 
               (SELECT COUNT(*) FROM enquiries e WHERE e.branch_id = b.id OR e.branch_name = b.name) as enquiry_count,
               (SELECT COUNT(*) FROM admissions a WHERE a.branch_id = b.id OR a.branch_name = b.name) as admission_count
        FROM branches b 
        ORDER BY b.id ASC
    ");
    $branches = $stmt->fetchAll() ?: [];
    json_response(true, 'Branches loaded.', ['branches' => $branches]);
}

if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $branch = $stmt->fetch();
    if (!$branch) json_response(false, 'Branch not found.', [], 404);
    json_response(true, 'Branch found.', ['branch' => $branch]);
}

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);

    $id             = (int)($_POST['id'] ?? 0);
    $branchCode     = strtoupper(sanitize_input($_POST['branch_code'] ?? ''));
    $name           = sanitize_input($_POST['name'] ?? '');
    $tagline        = sanitize_input($_POST['tagline'] ?? 'Branch Center');
    $address        = sanitize_input($_POST['address'] ?? '');
    $city           = sanitize_input($_POST['city'] ?? 'Nawabganj');
    $state          = sanitize_input($_POST['state'] ?? 'Uttar Pradesh');
    $pincode        = sanitize_input($_POST['pincode'] ?? '271303');
    $directorName   = sanitize_input($_POST['director_name'] ?? 'Shivam Pandey');
    $phone          = sanitize_input($_POST['phone'] ?? '');
    $email          = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;
    $instagram      = sanitize_input($_POST['instagram'] ?? '@master.computer1');
    $blogUrl        = sanitize_input($_POST['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com');
    $status         = sanitize_input($_POST['status'] ?? 'active');

    if (empty($branchCode) || empty($name) || empty($address)) {
        json_response(false, 'Branch code, center name, and address are required.', [], 422);
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE branches 
                SET branch_code = ?, name = ?, tagline = ?, address = ?, city = ?, state = ?, pincode = ?, director_name = ?, phone = ?, email = ?, instagram = ?, blog_url = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$branchCode, $name, $tagline, $address, $city, $state, $pincode, $directorName, $phone, $email, $instagram, $blogUrl, $status, $id]);
            log_admin_activity($pdo, 'Edit Branch', 'branch', $id, "Updated branch {$branchCode} ({$name})");
            json_response(true, 'Branch updated successfully.', ['id' => $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO branches (branch_code, name, tagline, address, city, state, pincode, director_name, phone, email, instagram, blog_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$branchCode, $name, $tagline, $address, $city, $state, $pincode, $directorName, $phone, $email, $instagram, $blogUrl, $status]);
            $newId = (int)$pdo->lastInsertId();
            log_admin_activity($pdo, 'Add Branch', 'branch', $newId, "Added branch {$branchCode} ({$name})");
            json_response(true, 'Branch added successfully.', ['id' => $newId], 201);
        }
    } catch (Exception $e) {
        error_log("Branch save error: " . $e->getMessage());
        json_response(false, 'Database error while saving branch.', [], 500);
    }
}

if ($action === 'toggle_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');
    if (!in_array($status, ['active', 'inactive'], true)) json_response(false, 'Invalid status.', [], 422);

    $stmt = $pdo->prepare("UPDATE branches SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    log_admin_activity($pdo, 'Toggle Branch Status', 'branch', $id, "Set branch #{$id} status to {$status}");
    json_response(true, "Branch status set to {$status}.", ['id' => $id, 'status' => $status]);
}

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
    $stmt->execute([$id]);
    log_admin_activity($pdo, 'Delete Branch', 'branch', $id, "Deleted branch #{$id}");
    json_response(true, 'Branch deleted successfully.');
}

json_response(false, 'Unknown branch action.', [], 400);
