<?php
/**
 * Master Computer Institute - Admin Logout
 * Invalidates session and clears session cookies
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    try {
        $pdo = getDBConnection();
        log_admin_activity($pdo, 'Logout', 'auth', (int)$_SESSION['mci_admin_id'], 'Admin logged out');
    } catch (Exception $e) {
        // Silently continue logout even if logging fails
    }
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

header("Location: login.php");
exit;
