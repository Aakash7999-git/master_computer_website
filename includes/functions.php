<?php
/**
 * Master Computer Institute - Global Helper Functions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

/**
 * HTML Escaper shortcut
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Return JSON response and exit
 */
function json_response(bool $success, string $message, array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Sanitize string input
 */
function sanitize_input(?string $input): string {
    if ($input === null) return '';
    return trim(strip_tags($input));
}

/**
 * Fetch all site settings as associative array
 */
function get_all_settings(PDO $pdo): array {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Exception $e) {
        error_log("Settings load error: " . $e->getMessage());
        $settings = [];
    }

    return $settings;
}

/**
 * Get setting value with fallback
 */
function get_setting(PDO $pdo, string $key, string $default = ''): string {
    $settings = get_all_settings($pdo);
    return $settings[$key] ?? $default;
}

/**
 * Fetch active courses ordered by sort_order
 */
function get_active_courses(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        error_log("Error fetching courses: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch active branches
 */
function get_active_branches(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("SELECT * FROM branches WHERE status = 'active' ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        error_log("Error fetching branches: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if current user is logged in as an administrator
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['mci_admin_id']) && !empty($_SESSION['mci_admin_username']);
}

/**
 * Get current administrator info from session
 */
function current_admin(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['mci_admin_id'],
        'username' => $_SESSION['mci_admin_username'],
        'name'     => $_SESSION['mci_admin_name'] ?? 'Administrator',
        'role'     => $_SESSION['mci_admin_role'] ?? 'admin',
        'email'    => $_SESSION['mci_admin_email'] ?? ''
    ];
}

/**
 * Require admin authentication on protected pages
 */
function require_admin_auth(): void {
    if (!is_admin_logged_in()) {
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            json_response(false, 'Unauthorized. Please login to continue.', [], 401);
        }
        $loginUrl = base_url('admin/login.php');
        header("Location: " . $loginUrl);
        exit;
    }
}

/**
 * Format currency in Indian Rupees
 */
function format_inr($amount): string {
    $val = floatval($amount);
    return '₹' . number_format($val, 0, '.', ',');
}

/**
 * Format date for display
 */
function format_date(?string $dateTimeStr, string $format = 'd M Y, h:i A'): string {
    if (empty($dateTimeStr)) return '—';
    try {
        $dt = new DateTime($dateTimeStr);
        return $dt->format($format);
    } catch (Exception $e) {
        return $dateTimeStr;
    }
}

/**
 * Audit Log Helper
 */
function log_admin_activity(PDO $pdo, string $action, string $entityType, ?int $entityId = null, ?string $details = null): void {
    try {
        $admin = current_admin();
        $adminId = $admin['id'] ?? null;
        $adminUser = $admin['username'] ?? 'System';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_logs (admin_id, admin_username, action, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$adminId, $adminUser, $action, $entityType, $entityId, $details, $ip, $ua]);
    } catch (Exception $e) {
        error_log("Audit log failure: " . $e->getMessage());
    }
}

/**
 * Admin Rate Limiting: check if username is locked out
 */
function check_admin_rate_limit(PDO $pdo, string $username): array {
    try {
        $stmt = $pdo->prepare("SELECT failed_login_attempts, lockout_until FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $row = $stmt->fetch();

        if ($row && !empty($row['lockout_until'])) {
            $lockoutTime = strtotime($row['lockout_until']);
            if ($lockoutTime > time()) {
                $waitMinutes = ceil(($lockoutTime - time()) / 60);
                return [
                    'allowed'     => false,
                    'waitMinutes' => $waitMinutes,
                    'message'     => "Account temporarily locked due to multiple failed login attempts. Please try again in {$waitMinutes} minute(s)."
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
    }

    return ['allowed' => true];
}

/**
 * Record a failed admin login attempt
 */
function record_failed_login(PDO $pdo, string $username): void {
    try {
        $stmt = $pdo->prepare("SELECT id, failed_login_attempts FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $row = $stmt->fetch();

        if ($row) {
            $attempts = $row['failed_login_attempts'] + 1;
            $lockoutUntil = null;
            if ($attempts >= 5) {
                // Lock for 15 minutes after 5 failed attempts
                $lockoutUntil = date('Y-m-d H:i:s', time() + (15 * 60));
            }

            $update = $pdo->prepare("UPDATE admins SET failed_login_attempts = ?, lockout_until = ? WHERE id = ?");
            $update->execute([$attempts, $lockoutUntil, $row['id']]);
        }
    } catch (Exception $e) {
        error_log("Record failed login error: " . $e->getMessage());
    }
}

/**
 * Reset failed admin login counter after successful login
 */
function reset_failed_logins(PDO $pdo, int $adminId): void {
    try {
        $stmt = $pdo->prepare("UPDATE admins SET failed_login_attempts = 0, lockout_until = NULL, last_login = NOW() WHERE id = ?");
        $stmt->execute([$adminId]);
    } catch (Exception $e) {
        error_log("Reset failed logins error: " . $e->getMessage());
    }
}
