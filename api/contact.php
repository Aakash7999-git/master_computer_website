<?php
/**
 * Master Computer Institute - Public Contact Form API
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

$name    = sanitize_input($data['contactName'] ?? $data['name'] ?? '');
$email   = filter_var(trim($data['contactEmail'] ?? $data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$mobile  = sanitize_input($data['contactMobile'] ?? $data['mobile'] ?? '');
$subject = sanitize_input($data['contactSubject'] ?? $data['subject'] ?? 'Website Inquiry');
$message = sanitize_input($data['contactMessage'] ?? $data['message'] ?? '');

if (empty($name)) {
    json_response(false, 'Please provide your name.', ['field' => 'contactName'], 422);
}

if (!$email) {
    json_response(false, 'Please provide a valid email address.', ['field' => 'contactEmail'], 422);
}

if (empty($mobile)) {
    json_response(false, 'Please provide your mobile number.', ['field' => 'contactMobile'], 422);
}

if (empty($message)) {
    json_response(false, 'Please write your message.', ['field' => 'contactMessage'], 422);
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, mobile, subject, message, status)
        VALUES (?, ?, ?, ?, ?, 'Unread')
    ");
    $stmt->execute([$name, $email, $mobile, $subject, $message]);
    $newId = (int)$pdo->lastInsertId();

    json_response(true, 'Your message has been sent successfully. Thank you for contacting Master Computer Institute!', [
        'message_id' => $newId
    ], 201);

} catch (Exception $e) {
    error_log("Contact submission error: " . $e->getMessage());
    json_response(false, 'An unexpected error occurred while sending your message. Please try again.', [], 500);
}
