<?php
/**
 * Master Computer Institute - Public Course Enquiry API
 * Accepts AJAX submissions from "Enquire Now" CTA buttons and modals
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method. Only POST is allowed.', [], 405);
}

// Support both form-data and JSON bodies
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$fullName = sanitize_input($data['fullName'] ?? $data['full_name'] ?? '');
$mobile   = sanitize_input($data['mobile'] ?? '');
$email    = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;
$course   = sanitize_input($data['course'] ?? $data['course_name'] ?? '');
$branch   = sanitize_input($data['branch'] ?? $data['branch_name'] ?? 'Master Computer Institute — Nawabganj');
$city     = sanitize_input($data['city'] ?? 'Nawabganj');
$batch    = sanitize_input($data['batch'] ?? $data['preferred_batch'] ?? 'Morning');
$message  = sanitize_input($data['message'] ?? '');
$source   = sanitize_input($data['source_page'] ?? 'website');

// Validations
if (empty($fullName)) {
    json_response(false, 'Please provide your full name.', ['field' => 'fullName'], 422);
}

if (strlen($fullName) < 2 || strlen($fullName) > 100) {
    json_response(false, 'Full name must be between 2 and 100 characters.', ['field' => 'fullName'], 422);
}

if (empty($mobile)) {
    json_response(false, 'Please provide a valid contact mobile number.', ['field' => 'mobile'], 422);
}

// Clean mobile number
$cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 12) {
    json_response(false, 'Please enter a valid 10-digit mobile number.', ['field' => 'mobile'], 422);
}

if (empty($course)) {
    json_response(false, 'Please select a course to enquire about.', ['field' => 'course'], 422);
}

try {
    $pdo = getDBConnection();

    // Find course ID if matchable
    $courseId = null;
    $cStmt = $pdo->prepare("SELECT id FROM courses WHERE name = ? OR course_code = ? LIMIT 1");
    $cStmt->execute([$course, $course]);
    if ($cRow = $cStmt->fetch()) {
        $courseId = (int)$cRow['id'];
    }

    // Find branch ID
    $branchId = null;
    $bStmt = $pdo->prepare("SELECT id FROM branches WHERE name LIKE ? LIMIT 1");
    $bStmt->execute(["%{$branch}%"]);
    if ($bRow = $bStmt->fetch()) {
        $branchId = (int)$bRow['id'];
    }

    // Generate unique enquiry code
    $pdo->beginTransaction();

    $stmtCode = $pdo->query("SELECT MAX(id) FROM enquiries");
    $maxId = ((int)$stmtCode->fetchColumn()) + 1;
    $enquiryCode = 'MCI-ENQ-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);

    $insert = $pdo->prepare("
        INSERT INTO enquiries (enquiry_code, full_name, mobile, email, course_id, course_name, branch_id, branch_name, city, preferred_batch, message, source_page, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')
    ");

    $insert->execute([
        $enquiryCode,
        $fullName,
        $mobile,
        $email,
        $courseId,
        $course,
        $branchId,
        $branch,
        $city,
        $batch,
        $message,
        $source
    ]);

    $newId = (int)$pdo->lastInsertId();
    $pdo->commit();

    json_response(true, 'Your course enquiry has been received successfully! Our admission coordinator will reach out to you shortly.', [
        'enquiry_id'   => $newId,
        'enquiry_code' => $enquiryCode,
        'course'       => $course
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Enquiry submission error: " . $e->getMessage());
    json_response(false, 'An unexpected server error occurred while processing your enquiry. Please try again or call us directly.', [], 500);
}
