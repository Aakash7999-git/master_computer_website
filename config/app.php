<?php
/**
 * Master Computer Institute - Application Core Configuration
 * Handles secure session initialization, base URL resolution, and CSRF protection
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookies
    $cookieParams = [
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    session_set_cookie_params($cookieParams);
    session_start();
}

// Application Constants
define('APP_NAME', 'Master Computer Institute');
define('APP_VERSION', '2.0.0');
define('APP_ROOT', dirname(__DIR__));

/**
 * Dynamically resolves the base URL of the application
 * Supports running from root, subdirectory (e.g. /master-computer/), or custom ports
 */
function base_url(string $path = ''): string {
    static $baseUrl = null;

    if ($baseUrl === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Detect project sub-path from SCRIPT_NAME
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $appDir = str_replace('\\', '/', APP_ROOT);

        // Find how many subdirectories deep we are from doc root if possible
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        $docRoot = rtrim($docRoot, '/');

        if (!empty($docRoot) && strpos($appDir, $docRoot) === 0) {
            $subPath = substr($appDir, strlen($docRoot));
            $subPath = '/' . trim($subPath, '/');
            if ($subPath === '/') {
                $subPath = '';
            }
        } else {
            // Fallback: analyze current script path up to /admin or /api or root
            $parts = explode('/', trim($scriptName, '/'));
            $subPathSegments = [];
            foreach ($parts as $segment) {
                if ($segment === 'admin' || $segment === 'api' || preg_match('/\.php$/i', $segment)) {
                    break;
                }
                $subPathSegments[] = $segment;
            }
            $subPath = !empty($subPathSegments) ? '/' . implode('/', $subPathSegments) : '';
        }

        $baseUrl = rtrim($protocol . $host . $subPath, '/');
    }

    $path = ltrim($path, '/');
    return $path !== '' ? $baseUrl . '/' . $path : $baseUrl . '/';
}

/**
 * Generate CSRF token if not already in session
 */
function get_csrf_token(): string {
    if (empty($_SESSION['mci_csrf_token'])) {
        $_SESSION['mci_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['mci_csrf_token'];
}

/**
 * Validate CSRF token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['mci_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['mci_csrf_token'], $token);
}

/**
 * Output CSRF hidden input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') . '" />';
}
