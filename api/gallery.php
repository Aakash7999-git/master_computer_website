<?php
/**
 * Master Computer Institute - Admin Gallery Management API
 * Includes secure image upload with MIME validation, randomized names, and extension restrictions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
header('Content-Type: application/json; charset=utf-8');
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $category = trim($_GET['category'] ?? '');
    $where = ["1=1"];
    $params = [];
    if (!empty($category) && $category !== 'all') {
        $where[] = "category = ?";
        $params[] = $category;
    }
    $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE " . implode(" AND ", $where) . " ORDER BY sort_order ASC, id DESC");
    $stmt->execute($params);
    $images = $stmt->fetchAll() ?: [];
    json_response(true, 'Gallery loaded.', ['images' => $images]);
}

if ($action === 'upload') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);

    $title    = sanitize_input($_POST['title'] ?? '');
    $category = sanitize_input($_POST['category'] ?? 'classroom');
    $altText  = sanitize_input($_POST['alt_text'] ?? $title);

    if (empty($title)) {
        json_response(false, 'Image title is required.', ['field' => 'title'], 422);
    }

    $imagePath = '';

    // Handle File Upload if provided
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $maxSizeBytes = 5 * 1024 * 1024; // 5 MB

        if ($file['size'] > $maxSizeBytes) {
            json_response(false, 'Uploaded image size exceeds maximum permitted limit of 5 MB.', [], 422);
        }

        // Validate MIME type securely using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            json_response(false, 'Invalid image format. Only JPG, PNG, WEBP and SVG images are permitted.', [], 422);
        }

        $extension = $allowedMimes[$mime];
        // Generate random safe filename
        $randomName = 'mci_' . bin2hex(random_bytes(10)) . '.' . $extension;
        $uploadDir = __DIR__ . '/../assets/images/gallery/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetFile = $uploadDir . $randomName;
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            json_response(false, 'Failed to save the uploaded image file on server.', [], 500);
        }

        $imagePath = 'assets/images/gallery/' . $randomName;
    } else {
        // Alternatively an existing asset path can be passed
        $imagePath = sanitize_input($_POST['image_path'] ?? '');
    }

    if (empty($imagePath)) {
        json_response(false, 'Please provide or upload an image file.', [], 422);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO gallery_images (title, category, image_path, alt_text, status, sort_order)
            VALUES (?, ?, ?, ?, 'active', 0)
        ");
        $stmt->execute([$title, $category, $imagePath, $altText]);
        $newId = (int)$pdo->lastInsertId();

        log_admin_activity($pdo, 'Upload Gallery Image', 'gallery', $newId, "Added gallery item '{$title}' ({$category})");
        json_response(true, 'Gallery image added successfully.', ['id' => $newId, 'path' => $imagePath], 201);
    } catch (Exception $e) {
        error_log("Gallery save error: " . $e->getMessage());
        json_response(false, 'Database error while saving gallery image.', [], 500);
    }
}

if ($action === 'toggle_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');
    if (!in_array($status, ['active', 'inactive'], true)) json_response(false, 'Invalid status.', [], 422);

    $stmt = $pdo->prepare("UPDATE gallery_images SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    log_admin_activity($pdo, 'Toggle Gallery Status', 'gallery', $id, "Set gallery image #{$id} status to {$status}");
    json_response(true, "Image status updated to {$status}.", ['id' => $id, 'status' => $status]);
}

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'Method not allowed.', [], 405);
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->execute([$id]);
    log_admin_activity($pdo, 'Delete Gallery Image', 'gallery', $id, "Deleted gallery image #{$id}");
    json_response(true, 'Image removed from gallery successfully.');
}

json_response(false, 'Unknown gallery action.', [], 400);
