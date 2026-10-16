<?php
/**
 * Master Computer Institute - Public Online Admission Enquiry API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method. Only POST is allowed.', [], 405);
}

$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$fullName      = sanitize_input($data['fullName'] ?? $data['full_name'] ?? '');
$dob           = sanitize_input($data['dob'] ?? '');
$gender        = sanitize_input($data['gender'] ?? '');
$mobile        = sanitize_input($data['mobile'] ?? '');
$email         = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$address       = sanitize_input($data['address'] ?? '');
$city          = sanitize_input($data['city'] ?? '');
$qualification = sanitize_input($data['qualification'] ?? '');
$course        = sanitize_input($data['course'] ?? $data['course_name'] ?? '');
$branch        = sanitize_input($data['branch'] ?? $data['branch_name'] ?? 'Nawabganj Center');
$batch         = sanitize_input($data['batch'] ?? '');
$message       = sanitize_input($data['message'] ?? '');

// Server-side validation
if (empty($fullName)) {
    json_response(false, 'Please provide your full applicant name.', ['field' => 'fullName'], 422);
}

if (empty($mobile)) {
    json_response(false, 'Please provide your mobile number.', ['field' => 'mobile'], 422);
}

$cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 12) {
    json_response(false, 'Please enter a valid 10-digit mobile number.', ['field' => 'mobile'], 422);
}

if (!$email) {
    json_response(false, 'Please provide a valid email address.', ['field' => 'email'], 422);
}

if (empty($city)) {
    json_response(false, 'Please provide your city/town.', ['field' => 'city'], 422);
}

if (empty($address)) {
    json_response(false, 'Please enter your complete address.', ['field' => 'address'], 422);
}

if (empty($qualification)) {
    json_response(false, 'Please enter your educational qualification.', ['field' => 'qualification'], 422);
}

if (empty($course)) {
    json_response(false, 'Please select a course for admission.', ['field' => 'course'], 422);
}

if (empty($batch)) {
    json_response(false, 'Please select your preferred batch timing.', ['field' => 'batch'], 422);
}

// Format DOB safely
$dobValue = null;
if (!empty($dob)) {
    $parsedDob = strtotime($dob);
    if ($parsedDob !== false) {
        $dobValue = date('Y-m-d', $parsedDob);
    }
}

try {
    $pdo = getDBConnection();

    // Check course and branch references
    $courseId = null;
    $cStmt = $pdo->prepare("SELECT id FROM courses WHERE name = ? OR course_code = ? LIMIT 1");
    $cStmt->execute([$course, $course]);
    if ($cRow = $cStmt->fetch()) {
        $courseId = (int)$cRow['id'];
    }

    $branchId = null;
    $bStmt = $pdo->prepare("SELECT id FROM branches WHERE name LIKE ? LIMIT 1");
    $bStmt->execute(["%{$branch}%"]);
    if ($bRow = $bStmt->fetch()) {
        $branchId = (int)$bRow['id'];
    }

    $pdo->beginTransaction();

    $stmtCode = $pdo->query("SELECT MAX(id) FROM admissions");
    $maxId = ((int)$stmtCode->fetchColumn()) + 1;
    $admissionCode = 'MCI-ADM-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);

    $insert = $pdo->prepare("
        INSERT INTO admissions (admission_code, full_name, dob, gender, mobile, email, address, city, qualification, course_id, course_name, branch_id, branch_name, batch, message, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')
    ");

    $insert->execute([
        $admissionCode,
        $fullName,
        $dobValue,
        $gender ?: null,
        $mobile,
        $email,
        $address,
        $city,
        $qualification,
        $courseId,
        $course,
        $branchId,
        $branch,
        $batch,
        $message
    ]);

    $newId = (int)$pdo->lastInsertId();
    $pdo->commit();

    json_response(true, "Application submitted successfully! Your application reference number is {$admissionCode}. Our institute will get in touch with you soon.", [
        'admission_id'   => $newId,
        'admission_code' => $admissionCode,
        'applicant_name' => $fullName
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Admission submission error: " . $e->getMessage());
    json_response(false, 'Unable to submit your application due to a technical issue. Please try again or contact the center.', [], 500);
}
