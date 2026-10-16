<?php
/**
 * Master Computer Institute - Admin Course Management API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_auth();
header('Content-Type: application/json; charset=utf-8');
$pdo = getDBConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// -------------------------------------------------------------
// 1. List All Courses (with enquiry counts)
// -------------------------------------------------------------
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM enquiries e WHERE e.course_id = c.id OR e.course_name = c.name) as enquiry_count,
               (SELECT COUNT(*) FROM admissions a WHERE a.course_id = c.id OR a.course_name = c.name) as admission_count
        FROM courses c 
        ORDER BY c.sort_order ASC, c.id ASC
    ");
    $courses = $stmt->fetchAll() ?: [];
    json_response(true, 'Courses retrieved.', ['courses' => $courses]);
}

// -------------------------------------------------------------
// 2. Get Single Course
// -------------------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $course = $stmt->fetch();

    if (!$course) {
        json_response(false, 'Course not found.', [], 404);
    }
    json_response(true, 'Course found.', ['course' => $course]);
}

// -------------------------------------------------------------
// 3. Save (Add / Edit) Course
// -------------------------------------------------------------
if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id            = (int)($_POST['id'] ?? 0);
    $courseCode    = strtoupper(sanitize_input($_POST['course_code'] ?? ''));
    $name          = sanitize_input($_POST['name'] ?? '');
    $category      = sanitize_input($_POST['category'] ?? '');
    $duration      = sanitize_input($_POST['duration'] ?? '');
    $fee           = floatval($_POST['fee'] ?? 0);
    $shortDesc     = sanitize_input($_POST['short_desc'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $eligibility   = sanitize_input($_POST['eligibility'] ?? '');
    $certification = sanitize_input($_POST['certification'] ?? '');
    $badge         = sanitize_input($_POST['badge'] ?? '');
    $imageUrl      = sanitize_input($_POST['image_url'] ?? '');
    $status        = sanitize_input($_POST['status'] ?? 'active');

    // Validations
    if (empty($courseCode)) {
        json_response(false, 'Course code is required (e.g. CCC, ADCA).', ['field' => 'course_code'], 422);
    }
    if (empty($name)) {
        json_response(false, 'Course title is required.', ['field' => 'name'], 422);
    }
    if (empty($duration)) {
        json_response(false, 'Course duration is required (e.g. 3 Months, 1 Year).', ['field' => 'duration'], 422);
    }
    if ($fee < 0) {
        json_response(false, 'Course fee must be a valid positive amount.', ['field' => 'fee'], 422);
    }

    // Slug generation
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    try {
        if ($id > 0) {
            // Check code uniqueness
            $chk = $pdo->prepare("SELECT id FROM courses WHERE course_code = ? AND id != ? LIMIT 1");
            $chk->execute([$courseCode, $id]);
            if ($chk->fetch()) {
                json_response(false, "Course code '{$courseCode}' is already in use by another course.", ['field' => 'course_code'], 422);
            }

            $stmt = $pdo->prepare("
                UPDATE courses 
                SET course_code = ?, name = ?, slug = ?, category = ?, duration = ?, fee = ?, short_desc = ?, description = ?, eligibility = ?, certification = ?, badge = ?, image_url = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $courseCode, $name, $slug, $category, $duration, $fee, $shortDesc, $description, $eligibility, $certification, $badge, $imageUrl, $status, $id
            ]);

            log_admin_activity($pdo, 'Edit Course', 'course', $id, "Updated course {$courseCode} ({$name})");
            json_response(true, 'Course updated successfully.', ['id' => $id]);
        } else {
            // New course
            $chk = $pdo->prepare("SELECT id FROM courses WHERE course_code = ? LIMIT 1");
            $chk->execute([$courseCode]);
            if ($chk->fetch()) {
                json_response(false, "Course code '{$courseCode}' is already registered.", ['field' => 'course_code'], 422);
            }

            $stmt = $pdo->prepare("
                INSERT INTO courses (course_code, name, slug, category, duration, fee, short_desc, description, eligibility, certification, badge, image_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $courseCode, $name, $slug, $category, $duration, $fee, $shortDesc, $description, $eligibility, $certification, $badge, $imageUrl, $status
            ]);
            $newId = (int)$pdo->lastInsertId();

            log_admin_activity($pdo, 'Add Course', 'course', $newId, "Added new course {$courseCode} ({$name})");
            json_response(true, 'Course added successfully.', ['id' => $newId], 201);
        }
    } catch (Exception $e) {
        error_log("Save course error: " . $e->getMessage());
        json_response(false, 'Database error while saving course.', [], 500);
    }
}

// -------------------------------------------------------------
// 4. Toggle Status (active / inactive)
// -------------------------------------------------------------
if ($action === 'toggle_status') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');
    if (!in_array($status, ['active', 'inactive'], true)) {
        json_response(false, 'Invalid status.', [], 422);
    }

    $stmt = $pdo->prepare("UPDATE courses SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    log_admin_activity($pdo, 'Toggle Course Status', 'course', $id, "Set course #{$id} status to {$status}");
    json_response(true, "Course status set to {$status}.", ['id' => $id, 'status' => $status]);
}

// -------------------------------------------------------------
// 5. Delete Course
// -------------------------------------------------------------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Method not allowed.', [], 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);

    log_admin_activity($pdo, 'Delete Course', 'course', $id, "Deleted course #{$id}");
    json_response(true, 'Course deleted successfully.');
}

json_response(false, 'Unknown course action.', [], 400);
