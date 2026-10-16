<?php
/**
 * Master Computer Institute - Admin General Actions API
 * Handles contact messages, site settings, and activity logs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
header('Content-Type: application/json; charset=utf-8');
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// -------------------------------------------------------------
// Contact Messages
// -------------------------------------------------------------
if ($action === 'list_contacts') {
    $status = trim($_GET['status'] ?? '');
    $where = ["1=1"];
    $params = [];
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
    }
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE " . implode(" AND ", $where) . " ORDER BY id DESC");
    $stmt->execute($params);
    $messages = $stmt->fetchAll() ?: [];
    json_response(true, 'Contact messages loaded.', ['messages' => $messages]);
}

if ($action === 'update_contact_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'Read');
    $notes = sanitize_input($_POST['admin_notes'] ?? '');

    $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $notes, $id]);
    log_admin_activity($pdo, 'Update Contact Status', 'contact', $id, "Updated message #{$id} status to {$status}");
    json_response(true, 'Message status updated.', ['id' => $id, 'status' => $status]);
}

if ($action === 'delete_contact') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    log_admin_activity($pdo, 'Delete Contact', 'contact', $id, "Deleted contact message #{$id}");
    json_response(true, 'Message deleted successfully.');
}

// -------------------------------------------------------------
// Website Settings
// -------------------------------------------------------------
if ($action === 'save_settings') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);

    $settings = $_POST['settings'] ?? [];
    if (!is_array($settings)) {
        json_response(false, 'Invalid settings payload.', [], 422);
    }

    // Allowed public editable settings (never credentials or security configs)
    $allowedKeys = [
        'institute_name', 'tagline', 'director_name', 'address', 'city', 'state',
        'pincode', 'phone', 'whatsapp', 'email', 'instagram_url', 'instagram_handle',
        'blog_url', 'timing'
    ];

    $stmt = $pdo->prepare("
        INSERT INTO site_settings (setting_key, setting_value, setting_group) 
        VALUES (?, ?, 'general')
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
    ");

    $updatedCount = 0;
    foreach ($settings as $key => $val) {
        if (in_array($key, $allowedKeys, true)) {
            $stmt->execute([$key, sanitize_input((string)$val)]);
            $updatedCount++;
        }
    }

    log_admin_activity($pdo, 'Update Settings', 'settings', null, "Updated {$updatedCount} website settings");
    json_response(true, 'Website settings saved successfully.', ['updated' => $updatedCount]);
}

// -------------------------------------------------------------
// Activity Logs
// -------------------------------------------------------------
if ($action === 'list_logs') {
    $stmt = $pdo->query("SELECT * FROM admin_activity_logs ORDER BY id DESC LIMIT 50");
    $logs = $stmt->fetchAll() ?: [];
    json_response(true, 'Activity logs loaded.', ['logs' => $logs]);
}

json_response(false, 'Unknown admin action.', [], 400);
