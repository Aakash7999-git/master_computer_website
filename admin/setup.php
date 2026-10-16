<?php
/**
 * Master Computer Institute - Initial Administrator Setup Script
 * Allows secure creation or resetting of the initial administrator account.
 * Automatically locks or disables itself after initial admin creation.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$lockFile = __DIR__ . '/.setup_locked';

// Check if locked
$isLocked = file_exists($lockFile);

// Check if admins exist in DB
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
$adminCount = (int)$stmt->fetchColumn();

$message = '';
$messageType = '';

// CLI Mode
if (php_sapi_name() === 'cli') {
    echo "===========================================\n";
    echo " Master Computer Institute - Admin Setup\n";
    echo "===========================================\n";

    $username = 'admin';
    $email = 'admin@mastercomputer.edu';
    $password = 'Admin@MCI2026!';
    $name = 'Shivam Pandey';

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($adminCount > 0) {
        $update = $pdo->prepare("UPDATE admins SET password_hash = ?, failed_login_attempts = 0, lockout_until = NULL WHERE username = ?");
        $update->execute([$hash, $username]);
        echo "Updated existing administrator '{$username}'.\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, 'superadmin', 'active')");
        $insert->execute([$username, $email, $hash, $name]);
        echo "Created new administrator '{$username}'.\n";
    }

    echo "Default Password set to: {$password}\n";
    echo "Setup finished.\n";
    exit(0);
}

// Browser Mode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'lock') {
        file_put_contents($lockFile, "Locked on " . date('Y-m-d H:i:s'));
        $isLocked = true;
        $message = "Setup tool has been locked successfully. For security, remove this file from production.";
        $messageType = 'success';
    } elseif ($action === 'create_admin') {
        if ($isLocked) {
            $message = "Setup is locked. Remove admin/.setup_locked to unlock.";
            $messageType = 'error';
        } else {
            $username = sanitize_input($_POST['username'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $fullName = sanitize_input($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($username) || !$email || empty($fullName) || empty($password)) {
                $message = "All fields are required and email must be valid.";
                $messageType = 'error';
            } elseif (strlen($password) < 8) {
                $message = "Password must be at least 8 characters.";
                $messageType = 'error';
            } elseif ($password !== $confirm) {
                $message = "Passwords do not match.";
                $messageType = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $check = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
                    $check->execute([$username, $email]);
                    $existing = $check->fetch();

                    if ($existing) {
                        $update = $pdo->prepare("UPDATE admins SET password_hash = ?, full_name = ?, failed_login_attempts = 0, lockout_until = NULL, status = 'active' WHERE id = ?");
                        $update->execute([$hash, $fullName, $existing['id']]);
                        $message = "Administrator account '{$username}' updated successfully! You can now log in.";
                    } else {
                        $ins = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, 'superadmin', 'active')");
                        $ins->execute([$username, $email, $hash, $fullName]);
                        $message = "Administrator account created successfully! You can now log in.";
                    }
                    $messageType = 'success';
                    // Re-check count
                    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
                } catch (Exception $e) {
                    $message = "Error saving admin: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Setup | Master Computer Institute</title>
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; margin: 0; }
    .setup-card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; width: 100%; max-width: 520px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
    .setup-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .setup-brand img { width: 44px; height: 44px; }
    .setup-brand h2 { font-size: 20px; margin: 0; color: #fff; }
    .badge-status { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }
    .badge-ok { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .badge-warn { background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: #cbd5e1; }
    .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: #fff; font-size: 14px; box-sizing: border-box; }
    .form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
    .btn-submit { width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-submit:hover { background: #1d4ed8; }
    .btn-lock { background: #475569; color: #f8fafc; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; margin-top: 10px; }
    .btn-lock:hover { background: #334155; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #4ade80; }
    .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; }
    .setup-footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .setup-footer a { color: #60a5fa; text-decoration: none; }
  </style>
</head>
<body>
  <div class="setup-card">
    <div class="setup-brand">
      <img src="../assets/logos/mci-logo.svg" alt="MCI Logo" />
      <div>
        <h2>Master Computer Institute</h2>
        <small style="color: #94a3b8;">Administrator Setup Procedure</small>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($isLocked): ?>
      <div class="badge-status badge-warn"><i class="bi bi-lock-fill"></i> Setup Locked for Security</div>
      <p style="color: #94a3b8; font-size: 14px; line-height: 1.5;">This setup script has been locked. To unlock it, delete the file <code>admin/.setup_locked</code> on your server.</p>
      <div style="margin-top: 20px;">
        <a href="login.php" class="btn-submit" style="display:inline-block; text-align:center; text-decoration:none;">Go to Admin Login</a>
      </div>
    <?php else: ?>
      <div class="badge-status <?= $adminCount > 0 ? 'badge-warn' : 'badge-ok' ?>">
        <?= $adminCount > 0 ? "Existing Admins in Database: {$adminCount}" : "No Administrators Found — First Admin Setup" ?>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="action" value="create_admin" />
        <div class="form-group">
          <label for="username">Admin Username</label>
          <input type="text" id="username" name="username" value="admin" required />
        </div>
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" value="Shivam Pandey" required />
        </div>
        <div class="form-group">
          <label for="email">Admin Email</label>
          <input type="email" id="email" name="email" value="admin@mastercomputer.edu" required />
        </div>
        <div class="form-group">
          <label for="password">Password (Minimum 8 chars)</label>
          <input type="password" id="password" name="password" placeholder="e.g. Admin@MCI2026!" required />
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required />
        </div>

        <button type="submit" class="btn-submit">
          <?= $adminCount > 0 ? "Update / Reset Administrator Account" : "Create Administrator Account" ?>
        </button>
      </form>

      <?php if ($adminCount > 0): ?>
        <form method="POST" action="" style="margin-top: 16px;">
          <input type="hidden" name="action" value="lock" />
          <button type="submit" class="btn-lock"><i class="bi bi-shield-lock-fill"></i> Lock Setup Script</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>

    <div class="setup-footer">
      <a href="../index.php"><i class="bi bi-arrow-left"></i> Back to Website</a>
      <a href="login.php">Admin Login <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</body>
</html>
