<?php
/**
 * Master Computer Institute - Public Certificate Verification API
 * Validates certificate records from the MySQL database using prepared statements
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$certNumber = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $json = json_decode($rawInput, true);
    $data = is_array($json) ? $json : $_POST;
    $certNumber = sanitize_input($data['certificateNumber'] ?? $data['certificate_no'] ?? '');
} else {
    $certNumber = sanitize_input($_GET['certificateNumber'] ?? $_GET['certificate_no'] ?? '');
}

if (empty($certNumber)) {
    json_response(false, 'Please enter a certificate number to verify.', ['field' => 'certificateNumber'], 422);
}

// Clean and normalize certificate number
$normalizedCert = strtoupper(trim($certNumber));

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT certificate_no, student_name, course_name, duration, issue_date, grade, center_name, status
        FROM certificates
        WHERE certificate_no = ?
        LIMIT 1
    ");
    $stmt->execute([$normalizedCert]);
    $cert = $stmt->fetch();

    if (!$cert) {
        json_response(false, "No certificate found matching '{$normalizedCert}'. Please verify the certificate number and try again.", [
            'found' => false
        ], 404);
    }

    if ($cert['status'] === 'Revoked') {
        json_response(false, "This certificate ({$cert['certificate_no']}) has been marked as REVOKED by the institute administration. Please contact the institute office for verification.", [
            'found'  => true,
            'status' => 'Revoked'
        ], 403);
    }

    if ($cert['status'] !== 'Valid') {
        json_response(false, "Certificate status is currently {$cert['status']}. Please contact the institute office.", [
            'found'  => true,
            'status' => $cert['status']
        ], 200);
    }

    // Mask name for privacy protection if desired, e.g. "Amit Kumar" -> "Amit Kumar" or partial masking
    $formattedDate = date('d F Y', strtotime($cert['issue_date']));

    json_response(true, "Certificate Verified Successfully! Official certificate issued by Master Computer Institute.", [
        'found'           => true,
        'certificate_no'  => $cert['certificate_no'],
        'student_name'    => $cert['student_name'],
        'course_name'     => $cert['course_name'],
        'duration'        => $cert['duration'],
        'issue_date'      => $formattedDate,
        'grade'           => $cert['grade'],
        'center_name'     => $cert['center_name'],
        'status'          => $cert['status'],
        'verification_ts' => date('Y-m-d H:i:s')
    ], 200);

} catch (Exception $e) {
    error_log("Certificate verification error: " . $e->getMessage());
    json_response(false, 'Unable to complete certificate verification at this time. Please try again later.', [], 500);
}
