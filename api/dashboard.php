<?php
/**
 * Master Computer Institute - Admin Dashboard Data API
 * Supplies live SQL query counts and statistics for the dashboard
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
require_admin_auth();

$pdo = getDBConnection();

try {
    // 1. Live statistics from SQL queries
    $totalEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
    $websiteEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE source = 'Website'")->fetchColumn();
    $offlineEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE source = 'Offline'")->fetchColumn();
    $newEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
    $followUpEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Follow-up'")->fetchColumn();
    $convertedEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Converted'")->fetchColumn();

    $totalAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions")->fetchColumn();
    $websiteAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE source = 'Website'")->fetchColumn();
    $offlineAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE source = 'Offline'")->fetchColumn();
    $newAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'New'")->fetchColumn();
    $enrolledAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'Enrolled'")->fetchColumn();

    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();

    $activeCourses = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
    $activeBranches = (int)$pdo->query("SELECT COUNT(*) FROM branches WHERE status = 'active'")->fetchColumn();
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")->fetchColumn();
    $totalCertificates = (int)$pdo->query("SELECT COUNT(*) FROM certificates WHERE status = 'Valid'")->fetchColumn();

    // 2. Recent Enquiries (latest 6)
    $stmtRecentEnq = $pdo->query("
        SELECT id, enquiry_code, full_name, mobile, email, course_name, branch_name, source, status, created_at
        FROM enquiries
        ORDER BY id DESC
        LIMIT 6
    ");
    $recentEnquiries = $stmtRecentEnq->fetchAll() ?: [];

    // 3. Recent Admissions (latest 6)
    $stmtRecentAdm = $pdo->query("
        SELECT id, admission_code, full_name, mobile, course_name, branch_name, batch, source, status, created_at
        FROM admissions
        ORDER BY id DESC
        LIMIT 6
    ");
    $recentAdmissions = $stmtRecentAdm->fetchAll() ?: [];

    // 4. Course Enquiry Distribution
    $stmtCourseDist = $pdo->query("
        SELECT course_name, COUNT(*) as count
        FROM enquiries
        GROUP BY course_name
        ORDER BY count DESC
        LIMIT 5
    ");
    $courseDist = $stmtCourseDist->fetchAll() ?: [];

    json_response(true, 'Dashboard metrics loaded successfully.', [
        'stats' => [
            'total_enquiries'      => $totalEnquiries,
            'website_enquiries'    => $websiteEnquiries,
            'offline_enquiries'    => $offlineEnquiries,
            'new_enquiries'        => $newEnquiries,
            'followup_enquiries'   => $followUpEnquiries,
            'converted_enquiries'  => $convertedEnquiries,
            'total_admissions'     => $totalAdmissions,
            'website_admissions'   => $websiteAdmissions,
            'offline_admissions'   => $offlineAdmissions,
            'new_admissions'       => $newAdmissions,
            'enrolled_admissions'  => $enrolledAdmissions,
            'total_students'       => $totalStudents,
            'active_students'      => $activeStudents,
            'active_courses'       => $activeCourses,
            'active_branches'      => $activeBranches,
            'unread_messages'      => $unreadMessages,
            'total_certificates'   => $totalCertificates
        ],
        'recent_enquiries'    => $recentEnquiries,
        'recent_admissions'   => $recentAdmissions,
        'course_distribution' => $courseDist
    ]);

} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    json_response(false, 'Failed to calculate dashboard statistics.', [], 500);
}
