<?php
/**
 * Master Computer Institute - Admin Login
 * Secure, database-backed authentication using password_verify(), rate limiting, and session security
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already authenticated
if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$pdo = getDBConnection();
$error = '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        if ($isAjax) {
            json_response(false, 'Security token expired. Please refresh and try again.', [], 403);
        }
        $error = 'Security token invalid or expired. Please try again.';
    } else {
        $username = trim($_POST['adminUser'] ?? '');
        $password = $_POST['adminPassword'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both your username/email and password.';
        } else {
            // Check rate limiting
            $rateCheck = check_admin_rate_limit($pdo, $username);
            if (!$rateCheck['allowed']) {
                $error = $rateCheck['message'];
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
                    $stmt->execute([$username, $username]);
                    $admin = $stmt->fetch();

                    if ($admin && password_verify($password, $admin['password_hash'])) {
                        // Success: Regenerate session to prevent fixation
                        session_regenerate_id(true);

                        $_SESSION['mci_admin_id'] = $admin['id'];
                        $_SESSION['mci_admin_username'] = $admin['username'];
                        $_SESSION['mci_admin_name'] = $admin['full_name'];
                        $_SESSION['mci_admin_role'] = $admin['role'];
                        $_SESSION['mci_admin_email'] = $admin['email'];
                        $_SESSION['mci_admin_logged_in'] = time();

                        reset_failed_logins($pdo, (int)$admin['id']);
                        log_admin_activity($pdo, 'Login', 'auth', (int)$admin['id'], "Admin {$admin['username']} logged in successfully");

                        if ($isAjax) {
                            json_response(true, 'Login successful. Redirecting to dashboard...', ['redirect' => 'dashboard.php']);
                        }
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        // Failed login
                        record_failed_login($pdo, $username);
                        $error = 'Invalid username or password.';
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $error = 'An error occurred during authentication. Please try again.';
                }
            }
        }
    }

    if ($isAjax && !empty($error)) {
        json_response(false, $error, [], 400);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Secure Admin Login for Master Computer Institute Dashboard." />
  <title>Admin Login | Master Computer Institute</title>
  <link rel="icon" href="../assets/logos/mci-logo.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/responsive.css" />
  <link rel="stylesheet" href="../css/animations.css" />
  <style>
    .admin-auth-wrapper {
      min-height: calc(100vh - 180px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
      background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.08), transparent 70%);
    }
    .admin-login-card {
      width: 100%;
      max-width: 440px;
      background: var(--surface, #ffffff);
      border: 1px solid var(--border, #e2e8f0);
      border-radius: 16px;
      padding: 36px 32px;
      box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.04);
    }
    .admin-login-header {
      text-align: center;
      margin-bottom: 28px;
    }
    .admin-login-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 16px;
      background: linear-gradient(135deg, #1e3a8a, #2563eb);
      color: #fff;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
    }
    .admin-login-header h2 {
      font-size: 22px;
      font-weight: 700;
      color: var(--text, #0f172a);
      margin: 0 0 6px;
    }
    .admin-login-header p {
      font-size: 13px;
      color: var(--text-muted, #64748b);
      margin: 0;
    }
    .password-field-wrap {
      position: relative;
    }
    .password-field-wrap input {
      padding-right: 44px;
    }
    .toggle-pass-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted, #64748b);
      cursor: pointer;
      font-size: 16px;
      padding: 4px;
    }
    .btn-block {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 18px;
      font-size: 15px;
    }
    .login-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(37, 99, 235, 0.1);
      color: var(--primary, #1e3a8a);
      font-size: 11px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 9999px;
      margin-top: 8px;
    }
    .login-helper {
      margin-top: 24px;
      padding-top: 18px;
      border-top: 1px solid var(--border, #e2e8f0);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13px;
    }
    .login-helper a {
      color: var(--primary, #1e3a8a);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .login-helper a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="container topbar-inner">
      <div>Master Computer Institute • Nawabganj, Uttar Pradesh (271303)</div>
      <div class="topbar-links">
        <a href="https://www.instagram.com/master.computer1/" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i> @master.computer1</a>
        <a href="https://mastercomputerinstitute.blogspot.com" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe"></i> Official Blog</a>
      </div>
    </div>
  </div>

  <header class="navbar">
    <div class="container navbar-inner">
      <a class="nav-logo" href="../index.php">
        <span class="nav-logo-mark"><img src="../assets/logos/mci-logo.svg" alt="Master Computer Institute Logo" /></span>
        <span class="nav-logo-text">MASTER<br />COMPUTER<br />INSTITUTE</span>
      </a>
      <nav aria-label="Main navigation">
        <ul class="nav-menu">
          <li><a href="../index.php">Home</a></li>
          <li><a href="../about.php">About</a></li>
          <li><a href="../courses.php">Courses</a></li>
          <li><a href="../branches.php">Branches</a></li>
          <li><a href="../admission.php">Admissions</a></li>
          <li><a href="../gallery.php">Gallery</a></li>
          <li><a href="../certificate-verification.php">Certificate Verification</a></li>
          <li><a href="../contact.php">Contact</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <a href="../index.php#enquire" class="btn btn-primary">Enquire Now</a>
      </div>
      <button class="mobile-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
        <i class="bi bi-list"></i>
      </button>
    </div>
  </header>

  <main>
    <div class="admin-auth-wrapper">
      <div class="admin-login-card reveal">
        <div class="admin-login-header">
          <div class="admin-login-icon">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <h2>Institute Administrator</h2>
          <p>Sign in to manage courses, enquiries, admissions & certificates</p>
          <span class="login-badge"><i class="bi bi-shield-check"></i> Encrypted Session</span>
        </div>

        <?php if (!empty($error)): ?>
          <div class="form-message error" style="display:block; margin-bottom: 20px;">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
          </div>
        <?php endif; ?>

        <form id="adminLoginForm" method="POST" action="login.php" novalidate>
          <?= csrf_field() ?>

          <div class="form-field" style="margin-bottom: 18px;">
            <label for="adminUser"><i class="bi bi-person-fill"></i> Email or Username</label>
            <input id="adminUser" name="adminUser" type="text" required placeholder="Enter username or email" value="<?= e($_POST['adminUser'] ?? '') ?>" autocomplete="username" />
          </div>

          <div class="form-field" style="margin-bottom: 22px;">
            <label for="adminPassword"><i class="bi bi-key-fill"></i> Password</label>
            <div class="password-field-wrap">
              <input id="adminPassword" name="adminPassword" type="password" required placeholder="Enter password" autocomplete="current-password" />
              <button type="button" class="toggle-pass-btn" aria-label="Toggle password visibility" onclick="togglePassVisibility()">
                <i class="bi bi-eye" id="passEyeIcon"></i>
              </button>
            </div>
          </div>

          <button type="submit" id="loginSubmitBtn" class="btn btn-primary btn-block">
            <i class="bi bi-box-arrow-in-right"></i> Sign In to Dashboard
          </button>
        </form>

        <div class="login-helper">
          <a href="../index.php"><i class="bi bi-arrow-left"></i> Public Website</a>
          <a href="setup.php"><i class="bi bi-gear"></i> First Admin Setup</a>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer-bottom" style="margin-top:0;">
      <div>© 2026 Master Computer Institute. All Rights Reserved.</div>
      <div>Admin Portal • Nawabganj, UP</div>
    </div>
  </footer>

  <script src="../js/main.js"></script>
  <script>
    function togglePassVisibility() {
      const input = document.getElementById('adminPassword');
      const icon = document.getElementById('passEyeIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }
  </script>
</body>
</html>
