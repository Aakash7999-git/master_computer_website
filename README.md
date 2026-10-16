# Master Computer Institute — Database-Driven Web Application & Admin Portal

Official web application and comprehensive institute management portal for **Master Computer Institute**, located near Tripathi Hospital, Nawabganj, Uttar Pradesh (PIN 271303), India, under the directorship of **Shivam Pandey**.

- **Official Instagram**: [@master.computer1](https://www.instagram.com/master.computer1/)
- **Official Blog**: [mastercomputerinstitute.blogspot.com](https://mastercomputerinstitute.blogspot.com)

---

## 1. Project Overview & Architecture

This repository upgrades the original frontend into a fully database-driven, production-ready web application built natively with **Core PHP 8+**, **MySQL / MariaDB**, **Vanilla JavaScript Fetch API**, and **Bootstrap Icons**, designed to run directly on **Apache via XAMPP**.

### Key Features
- **Public Website (PHP & AJAX)**:
  - Responsive multi-page institute website preserving original brand typography, colors, animations, and layouts.
  - Dynamically rendered courses catalog, center location details, and campus gallery loaded directly from MySQL.
  - Universal **"Enquire Now"** call-to-actions across the header, course cards, and hero sections with pre-populated course selectors and AJAX submission.
  - Online admission application form with asynchronous database persistence.
  - Real-time online certificate verification with prepared statement validation and formatted student credentials.
  - Direct contact inquiry submission and WhatsApp integration.
- **Admin Portal (`/admin/`)**:
  - Secure session-based authentication using `password_hash()` (Bcrypt) and `password_verify()`.
  - Session regeneration on login, strict HTTP-only cookies, and CSRF token verification.
  - Login attempt rate limiting to prevent brute-force attacks.
  - Live statistics dashboard (total enquiries, pending follow-ups, converted leads, admissions, active courses, unread messages).
  - Search, multi-criteria filtering, pagination, status transitions, internal notes, and one-click CSV export for enquiries and admissions.
  - Complete course management (add, edit curriculum, fees, duration, activate/deactivate).
  - Branch and center location management.
  - Contact message review and email/WhatsApp direct reply actions.
  - Certificate management (issue, modify, revoke, or verify student records).
  - Gallery photo management with secure MIME-type validation, file size limits, and randomized filenames.
  - Configurable institute settings (phones, emails, address, social media links).
  - Immutable administrator audit activity logs.

---

## 2. Directory Structure

```text
master-computer/
├── index.php                      # Dynamic homepage with courses, enquiry & verification
├── about.php                      # About institute, leadership & vision
├── courses.php                    # All active computer courses catalog
├── course-details.php             # Individual course syllabus, fee & enquiry trigger
├── branches.php                   # Center locations & physical addresses
├── admission.php                  # Online admission application form
├── gallery.php                    # Filterable student photo gallery
├── certificate-verification.php   # Public certificate validation portal
├── contact.php                    # Inbound contact & inquiries page
├── login.php                      # Student portal login screen
├── index.html                     # Legacy compatibility entry (auto-redirects to index.php)
├── config/
│   ├── app.php                    # Session security, CSRF protection, base URL resolution
│   └── database.php               # PDO MySQL database connection module
├── includes/
│   ├── header.php                 # Shared public topbar, navigation & branding
│   ├── footer.php                 # Shared public footer, "Admin Dashboard" link & enquiry modal
│   └── functions.php              # Global helpers, security sanitizers & auth guards
├── admin/
│   ├── login.php                  # Secure database-backed administrator login
│   ├── logout.php                 # Session invalidation & cookie cleanup
│   ├── setup.php                  # First-admin initialization CLI / Web setup
│   ├── dashboard.php              # Live statistics overview & recent activity
│   ├── enquiries.php              # Enquiry search, filter, notes & CSV export
│   ├── admissions.php             # Admission applications review & management
│   ├── courses.php                # Course catalog creation, pricing & status
│   ├── branches.php               # Center branch information & contacts
│   ├── contacts.php               # Inbound visitor messages & direct replies
│   ├── certificates.php           # Student certificate issuance & revocation
│   ├── gallery.php                # Gallery photo uploads & category management
│   ├── settings.php               # Institute contact info & social link settings
│   └── includes/
│       ├── header.php             # Admin sidebar layout & live badge counters
│       └── footer.php             # Admin modal containers & toast notification system
├── api/
│   ├── enquiry.php                # Public course enquiry AJAX endpoint
│   ├── admission.php              # Public online admission enquiry AJAX endpoint
│   ├── contact.php                # Public contact message AJAX endpoint
│   ├── certificate.php            # Public certificate verification AJAX endpoint
│   ├── dashboard.php              # Protected admin metrics API
│   ├── enquiries.php              # Protected admin enquiry CRUD & CSV export
│   ├── admissions.php             # Protected admin admission CRUD & CSV export
│   ├── courses.php                # Protected admin course CRUD API
│   ├── branches.php               # Protected admin branch CRUD API
│   ├── certificates.php           # Protected admin certificate CRUD API
│   ├── gallery.php                # Protected admin gallery upload & CRUD API
│   └── admin-actions.php          # Protected settings, message status & audit logs
├── database/
│   └── master_computer.sql        # Full MySQL schema, indexes, FKs & seed data
├── assets/                        # Images, logos, fonts, vendor CSS/JS
├── css/                           # Main, responsive, and admin styling
└── js/                            # Public and admin interactive scripts
```

---

## 3. Step-by-Step Installation on XAMPP (Windows)

### Step 1: Install & Start XAMPP
1. Download and install **XAMPP for Windows** (with PHP 8.0+) from [apachefriends.org](https://www.apachefriends.org/).
2. Open the **XAMPP Control Panel**.
3. Click **Start** next to both **Apache** and **MySQL**. Verify that both show green status indicators.

### Step 2: Copy the Project into XAMPP Web Root
1. Copy the project folder into your XAMPP `htdocs` directory:
   ```text
   C:\xampp\htdocs\master-computer
   ```
2. Ensure file permissions allow Apache to read project files and write to `assets/images/gallery/` for uploads.

### Step 3: Import the MySQL Database
1. Open your web browser and navigate to:
   ```text
   http://localhost/phpmyadmin
   ```
2. Click **New** in the left sidebar and enter the database name:
   ```text
   master_computer_institute
   ```
   Choose collation: `utf8mb4_unicode_ci` and click **Create**.
3. Select the `master_computer_institute` database, click the **Import** tab.
4. Click **Choose File**, select `database/master_computer.sql` located inside the project, and click **Import** (or **Go**).
5. Alternatively, import via Windows Command Prompt / PowerShell:
   ```powershell
   Get-Content "C:\xampp\htdocs\master-computer\database\master_computer.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
   ```

### Step 4: Verify Database Connection
1. Open `config/database.php` in a text editor.
2. The default XAMPP settings are pre-configured:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'master_computer_institute');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. If your MySQL server uses a custom password or port, adjust these settings accordingly.

### Step 5: Administrator Setup
1. Open your browser and navigate to the admin setup tool:
   ```text
   http://localhost/master-computer/admin/setup.php
   ```
   *Or run via CLI*:
   ```powershell
   & "C:\xampp\php\php.exe" "C:\xampp\htdocs\master-computer\admin\setup.php"
   ```
2. The default initial administrative credentials are:
   - **Username**: `admin`
   - **Email**: `admin@mastercomputer.edu`
   - **Password**: `Admin@MCI2026!`
3. After setup or updating passwords, click **Lock Setup Script** for security.

### Step 6: Access the Application
- **Public Website**: [http://localhost/master-computer/](http://localhost/master-computer/)
- **Admin Login Portal**: [http://localhost/master-computer/admin/login.php](http://localhost/master-computer/admin/login.php)
- **Admin Dashboard Link**: Accessible directly from the footer of any page under the **Student & Admin** column.

---

## 4. Testing & Verification Guide

| Test Scenario | Action | Expected Result |
| :--- | :--- | :--- |
| **Public Browsing** | Open `index.php`, `about.php`, `courses.php`, `branches.php` | Pages load with 200 OK, full layout and styling preserved. |
| **Enquire Now CTAs** | Click any "Enquire Now" button on header or course cards | Modal opens with the selected course automatically pre-filled. |
| **Enquiry Submission** | Fill name, valid 10-digit phone, and click Submit | Asynchronous AJAX insert into MySQL `enquiries` table, success banner displayed. |
| **Online Admission** | Submit application on `admission.php` | Validated on client and server, stored in `admissions` table with `MCI-ADM-XXXX` code. |
| **Certificate Lookup** | Search `MCI-CERT-10001` on verification page | Returns authentic certificate card with course and student name. |
| **Invalid Certificate** | Search `MCI-CERT-99999` | Returns clean 404 error: "No certificate found matching...". |
| **Protected Dashboard** | Visit `/admin/dashboard.php` without logging in | HTTP 302 redirect directly to `/admin/login.php`. |
| **Admin Authentication** | Log in with `admin` / `Admin@MCI2026!` | Session regenerated, redirects to dashboard with live statistics. |
| **Invalid Credentials** | Attempt login with incorrect password | Generic error message, failed attempt recorded for rate limiting. |
| **Enquiry Management** | Filter by status "New", change status to "Contacted", add notes | Changes persist in MySQL and update live counters without reload. |
| **CSV Export** | Click "Export CSV" on enquiries or admissions | Downloads UTF-8 encoded `.csv` file with current filter criteria. |
| **Course Management** | Add or edit course fee / duration | Live course list on public `courses.php` reflects changes immediately. |
| **Admin Logout** | Click "Logout" in admin header | Session destroyed, cookies cleared, protected pages reject access. |

---

## 5. Security Implementations

1. **Prepared Statements**: All database operations execute strictly through PDO prepared statements with bound parameters to eliminate SQL injection vulnerabilities.
2. **Session Hardening**: Sessions use `session_regenerate_id(true)` upon successful authentication, preventing session fixation. Cookies have `HttpOnly=true` and `SameSite=Lax`.
3. **Password Security**: Passwords are saved exclusively as salted hashes using PHP's native `password_hash($pass, PASSWORD_DEFAULT)`.
4. **CSRF Tokens**: Form submissions and state-changing administrative requests validate unique cryptographically secure session tokens.
5. **Rate Limiting**: Administrator login tracks consecutive failed attempts per account and enforces temporary lockouts after 5 failed trials.
6. **File Upload Security**: Gallery uploads enforce server-side MIME verification using `finfo`, restrict extensions to JPG, PNG, WEBP and SVG, limit sizes to 5MB, and generate randomized non-executable filenames.
7. **XSS Protection**: Dynamic user-supplied outputs are sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

---

## 6. Troubleshooting Common XAMPP Issues

- **Apache Port Conflict (Port 80 / 443 already in use)**:
  - If Skype, IIS, or VMware occupy port 80:
  - In XAMPP Control Panel, click **Config** next to Apache > `httpd.conf`.
  - Change `Listen 80` to `Listen 8080` and `ServerName localhost:8080`.
  - Access the site via `http://localhost:8080/master-computer/`.
- **MySQL Connection Error ("Access denied for user 'root'@'localhost'")**:
  - Open `config/database.php` and verify the password matches your local MySQL root password.
- **Missing PDO / MySQL Extension**:
  - In XAMPP Control Panel, click **Config** next to Apache > `php.ini`.
  - Verify `extension=pdo_mysql` and `extension=fileinfo` are uncommented (no leading semicolon).
- **Broken Asset Links or Subdirectory Issues**:
  - `config/app.php` includes dynamic base URL detection that automatically resolves paths regardless of whether the site runs on root, `/master-computer/`, or a custom port.

---

## 7. License & Credits

- **Institute**: Master Computer Institute, Nawabganj, UP (271303), India.
- **Director**: Shivam Pandey
- **Technologies**: PHP 8, MySQL/MariaDB, Vanilla JavaScript, CSS3, Bootstrap Icons, Swiper.js.
