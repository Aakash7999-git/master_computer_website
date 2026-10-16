-- ==========================================================
-- Master Computer Institute Database Schema & Seed Data
-- Database: master_computer_institute
-- Compatibility: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `master_computer_institute` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `master_computer_institute`;

-- ----------------------------------------------------------
-- 1. Table structure for table `admins`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'admin',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `failed_login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` DATETIME NULL DEFAULT NULL,
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_admin_username` (`username`),
  INDEX `idx_admin_email` (`email`),
  INDEX `idx_admin_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table structure for table `courses`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `category` VARCHAR(50) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `short_desc` TEXT NULL,
  `description` LONGTEXT NULL,
  `eligibility` VARCHAR(150) NULL,
  `certification` VARCHAR(150) NULL,
  `image_url` VARCHAR(255) NULL,
  `badge` VARCHAR(50) NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_course_status` (`status`),
  INDEX `idx_course_slug` (`slug`),
  INDEX `idx_course_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table structure for table `branches`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `tagline` VARCHAR(100) NOT NULL DEFAULT 'Main Center',
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) NOT NULL DEFAULT 'Nawabganj',
  `state` VARCHAR(50) NOT NULL DEFAULT 'Uttar Pradesh',
  `pincode` VARCHAR(10) NOT NULL DEFAULT '271303',
  `director_name` VARCHAR(100) NOT NULL DEFAULT 'Shivam Pandey',
  `phone` VARCHAR(25) NULL,
  `email` VARCHAR(100) NULL,
  `instagram` VARCHAR(100) NOT NULL DEFAULT '@master.computer1',
  `blog_url` VARCHAR(255) NOT NULL DEFAULT 'https://mastercomputerinstitute.blogspot.com',
  `map_placeholder` VARCHAR(255) NOT NULL DEFAULT 'Master Computer Institute • Nawabganj (271303)',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_branch_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Table structure for table `enquiries`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `enquiries`;
CREATE TABLE `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_code` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `mobile` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) NULL,
  `course_id` INT UNSIGNED NULL,
  `course_name` VARCHAR(150) NOT NULL,
  `branch_id` INT UNSIGNED NULL,
  `branch_name` VARCHAR(150) NOT NULL,
  `city` VARCHAR(100) NULL,
  `preferred_batch` VARCHAR(50) NULL,
  `message` TEXT NULL,
  `source_page` VARCHAR(100) NOT NULL DEFAULT 'website',
  `source` ENUM('Website', 'Offline') NOT NULL DEFAULT 'Website',
  `status` ENUM('New', 'Contacted', 'Follow-up', 'Converted', 'Closed') NOT NULL DEFAULT 'New',
  `assigned_staff` VARCHAR(100) NULL,
  `created_by_admin_id` INT UNSIGNED NULL,
  `admin_notes` TEXT NULL,
  `follow_up_date` DATE NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_enquiry_status` (`status`),
  INDEX `idx_enquiry_source` (`source`),
  INDEX `idx_enquiry_mobile` (`mobile`),
  INDEX `idx_enquiry_course` (`course_name`),
  INDEX `idx_enquiry_branch` (`branch_name`),
  INDEX `idx_enquiry_created` (`created_at`),
  CONSTRAINT `fk_enquiries_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_enquiries_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_enquiries_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table structure for table `admissions`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `admissions`;
CREATE TABLE `admissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admission_code` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `dob` DATE NULL,
  `gender` VARCHAR(20) NULL,
  `mobile` VARCHAR(25) NOT NULL,
  `alternate_mobile` VARCHAR(25) NULL,
  `email` VARCHAR(100) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `qualification` VARCHAR(100) NULL,
  `course_id` INT UNSIGNED NULL,
  `course_name` VARCHAR(150) NOT NULL,
  `branch_id` INT UNSIGNED NULL,
  `branch_name` VARCHAR(150) NOT NULL,
  `batch` VARCHAR(50) NULL,
  `admission_date` DATE NULL,
  `message` TEXT NULL,
  `status` ENUM('New', 'Under Review', 'Approved', 'Enrolled', 'Rejected') NOT NULL DEFAULT 'New',
  `source` ENUM('Website', 'Offline') NOT NULL DEFAULT 'Website',
  `created_by_admin_id` INT UNSIGNED NULL,
  `admin_notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_admission_status` (`status`),
  INDEX `idx_admission_source` (`source`),
  INDEX `idx_admission_mobile` (`mobile`),
  INDEX `idx_admission_email` (`email`),
  INDEX `idx_admission_created` (`created_at`),
  CONSTRAINT `fk_admissions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_admissions_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_admissions_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5B. Table structure for table `students`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_code` VARCHAR(30) NOT NULL UNIQUE,
  `admission_id` INT UNSIGNED NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `dob` DATE NULL DEFAULT NULL,
  `gender` VARCHAR(20) NULL DEFAULT NULL,
  `mobile` VARCHAR(25) NOT NULL,
  `alternate_mobile` VARCHAR(25) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `qualification` VARCHAR(100) NULL DEFAULT NULL,
  `course_id` INT UNSIGNED NULL DEFAULT NULL,
  `course_name` VARCHAR(150) NOT NULL,
  `branch_id` INT UNSIGNED NULL DEFAULT NULL,
  `branch_name` VARCHAR(150) NOT NULL,
  `batch_timing` VARCHAR(50) NULL DEFAULT NULL,
  `admission_date` DATE NOT NULL,
  `enrollment_date` DATE NOT NULL,
  `status` ENUM('Active', 'Completed', 'Dropped') NOT NULL DEFAULT 'Active',
  `source` ENUM('Website', 'Offline') NOT NULL DEFAULT 'Offline',
  `created_by_admin_id` INT UNSIGNED NULL DEFAULT NULL,
  `remarks` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_student_code` (`student_code`),
  INDEX `idx_student_mobile` (`mobile`),
  INDEX `idx_student_status` (`status`),
  INDEX `idx_student_course` (`course_name`),
  INDEX `idx_student_branch` (`branch_name`),
  INDEX `idx_student_source` (`source`),
  CONSTRAINT `fk_students_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_students_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_students_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_students_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table structure for table `contact_messages`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `mobile` VARCHAR(25) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Unread', 'Read', 'Replied', 'Archived') NOT NULL DEFAULT 'Unread',
  `admin_notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_contact_status` (`status`),
  INDEX `idx_contact_email` (`email`),
  INDEX `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table structure for table `certificates`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `certificate_no` VARCHAR(50) NOT NULL UNIQUE,
  `student_name` VARCHAR(100) NOT NULL,
  `father_name` VARCHAR(100) NULL,
  `course_name` VARCHAR(150) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `issue_date` DATE NOT NULL,
  `grade` VARCHAR(20) NOT NULL DEFAULT 'A',
  `percentage` VARCHAR(20) NULL,
  `center_name` VARCHAR(150) NOT NULL DEFAULT 'Master Computer Institute, Nawabganj',
  `status` ENUM('Valid', 'Revoked', 'Pending') NOT NULL DEFAULT 'Valid',
  `remarks` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cert_no` (`certificate_no`),
  INDEX `idx_cert_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Table structure for table `gallery_images`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `gallery_images`;
CREATE TABLE `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'classroom',
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_gallery_category` (`category`),
  INDEX `idx_gallery_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Table structure for table `site_settings`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 10. Table structure for table `admin_activity_logs`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `admin_activity_logs`;
CREATE TABLE `admin_activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NULL,
  `admin_username` VARCHAR(50) NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_log_admin` (`admin_id`),
  INDEX `idx_log_action` (`action`),
  INDEX `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================
-- SEED DATA
-- =================-----------------------------------------

-- 1. Initial Administrator
-- Default credentials:
-- Username: admin
-- Password: Admin@MCI2026! (Bcrypt hashed)
INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES
(1, 'admin', 'admin@mastercomputer.edu', '$2y$10$7zB3qQ893wG2vJpD9M6kbeYqN7hT5rC9vL4pM2xZ1oW8qA7sB6tK.', 'Shivam Pandey', 'superadmin', 'active')
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- 2. Branches
INSERT INTO `branches` (`id`, `branch_code`, `name`, `tagline`, `address`, `city`, `state`, `pincode`, `director_name`, `phone`, `email`, `instagram`, `blog_url`, `status`) VALUES
(1, 'MCI-BR01', 'Master Computer Institute — Nawabganj', 'Main Center', 'Center near Tripathi Hospital, Nawabganj, Uttar Pradesh - 271303, India', 'Nawabganj', 'Uttar Pradesh', '271303', 'Shivam Pandey', '+91 9696525287', 'mastercomputer@example.com', '@master.computer1', 'https://mastercomputerinstitute.blogspot.com', 'active'),
(2, 'MCI-BR02', 'Master Computer Institute — Extension Campus', 'Branch Center', 'Near Railway Station Road, Nawabganj, Uttar Pradesh - 271303, India', 'Nawabganj', 'Uttar Pradesh', '271303', 'Shivam Pandey', '+91 9696525287', 'info@mastercomputer.edu', '@master.computer1', 'https://mastercomputerinstitute.blogspot.com', 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Courses
INSERT INTO `courses` (`id`, `course_code`, `name`, `slug`, `category`, `duration`, `fee`, `short_desc`, `description`, `eligibility`, `certification`, `image_url`, `badge`, `status`, `sort_order`) VALUES
(1, 'CCC', 'CCC — Course on Computer Concepts', 'ccc-course-on-computer-concepts', 'Govt. Recognized', '3 Months', 3499.00, 'Master computer fundamentals, operating systems, MS Office, digital services, and internet essentials for government examinations.', 'Course on Computer Concepts (CCC) is designed to impart a basic level IT literacy programme for the common man. It provides students and job aspirants with practical computer fundamentals, operating system operations, MS Office tools, digital banking, and government portal services required for competitive and public examinations.', '10th / Any Student', 'NIELIT Exam Prep & MCI Certification', 'assets/images/courses/ccc-course.jpg', 'Govt. Recognized', 'active', 1),
(2, 'DCA', 'DCA — Diploma in Computer Applications', 'dca-diploma-in-computer-applications', 'Diploma', '6 Months', 6499.00, 'Comprehensive diploma covering office productivity tools, database management, internet applications, and workplace computing.', 'Diploma in Computer Applications (DCA) is a 6-month specialized program for beginners and intermediate learners. It equips students with hands-on capabilities across Word, Excel, PowerPoint, Access, database fundamentals, Internet applications, and clerical automation.', '10th / 12th Pass', 'MCI Diploma Certificate', 'assets/images/courses/dca-course.jpg', 'Diploma', 'active', 2),
(3, 'ADCA', 'ADCA — Advanced Diploma in Computer Applications', 'adca-advanced-diploma-in-computer-applications', 'Advanced Diploma', '12 Months', 11999.00, 'One-year master diploma covering office automation, Tally accounting, graphic design basics, and essential IT workflows.', 'The Advanced Diploma in Computer Applications (ADCA) is our flagship 1-year career track. Students master advanced office automation, computerized accounting with Tally Prime and GST, visual designing in Photoshop and CorelDRAW, internet protocols, and workplace productivity.', '10th / 12th / Graduate', 'Govt. Registered Master Diploma', 'assets/images/courses/adca-course.jpg', 'Advanced Diploma', 'active', 3),
(4, 'TALLY', 'Tally Prime + GST', 'tally-prime-gst', 'Accounting', '3 Months', 7999.00, 'Learn computerized accounting workflows, GST taxation, invoice management, banking, and financial reporting.', 'Tally Prime with GST is an intensive accounting course designed for commerce students, business owners, and accounting job seekers. Topics include ledger creation, voucher entry, inventory tracking, GST computation, e-invoicing, bank reconciliation, and profit & loss statements.', '10th / 12th / Commerce Preferred', 'Tally Accounting Professional Certificate', 'assets/images/courses/tally-course.jpg', 'Accounting', 'active', 4),
(5, 'EXCEL', 'Advanced Excel & MIS', 'advanced-excel-mis', 'Office Skills', '2 Months', 3999.00, 'Master spreadsheet formulas, pivot tables, lookup functions, dashboards, data analysis, and automated MIS reporting.', 'Learn advanced spreadsheet modeling, VLOOKUP, XLOOKUP, INDEX-MATCH, nested logical formulas, dynamic Pivot Tables, conditional formatting, dashboard visualization, and executive MIS reporting workflows.', 'Basic Computer Knowledge', 'Advanced Excel Specialist Certificate', 'assets/images/courses/advanced-excel-course.jpg', 'Office Skills', 'active', 5),
(6, 'GRAPHIC', 'Graphic Designing', 'graphic-designing', 'Creative', '4 Months', 9999.00, 'Create visual media assets, advertising banners, logos, and digital branding with Photoshop and CorelDRAW.', 'Hands-on practical training in Adobe Photoshop, CorelDRAW, vector illustration, digital photo manipulation, logo design, marketing flyers, social media banners, typography, and commercial print preparation.', 'Beginner to Intermediate', 'Professional Graphic Design Certificate', 'assets/images/courses/graphic-design-course.jpg', 'Creative', 'active', 6),
(7, 'DIGITAL-MKT', 'Digital Marketing', 'digital-marketing', 'Marketing', '4 Months', 10499.00, 'Learn SEO optimization, social media promotion, Google Ads campaigns, content strategy, and online brand growth.', 'Comprehensive digital marketing course covering search engine optimization (SEO), Google Search Console, social media marketing (Instagram, Facebook, LinkedIn), search engine marketing (Google Ads), email outreach, and brand analytics.', 'All levels / 12th Pass', 'Digital Marketing Specialist Certificate', 'assets/images/courses/digital-marketing-course.jpg', 'Marketing', 'active', 7),
(8, 'FUNDAMENTALS', 'Computer Fundamentals & Typing', 'computer-fundamentals-typing', 'Foundation', '2 Months', 2999.00, 'Build confidence with core computer operations, Hindi & English typing speed, digital tools, and email essentials.', 'Ideal foundational training for school students and beginners learning hardware parts, Windows OS navigation, file management, Hindi (KrutiDev / Mangal) and English touch typing, and safe internet browsing.', 'Open to All Beginners', 'Certificate of Completion & Speed Record', 'assets/images/courses/computer-fundamentals-course.jpg', 'Foundation', 'active', 8)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Sample Seed Certificates
INSERT INTO `certificates` (`id`, `certificate_no`, `student_name`, `father_name`, `course_name`, `duration`, `issue_date`, `grade`, `percentage`, `center_name`, `status`, `remarks`) VALUES
(1, 'MCI-CERT-10001', 'Amit Kumar', 'Ramesh Kumar', 'ADCA — Advanced Diploma in Computer Applications', '12 Months', '2025-06-15', 'A+', '88.5%', 'Master Computer Institute, Nawabganj', 'Valid', 'Passed with distinction'),
(2, 'MCI-CERT-10002', 'Priya Sharma', 'Suresh Sharma', 'CCC — Course on Computer Concepts', '3 Months', '2025-08-20', 'A', '82.0%', 'Master Computer Institute, Nawabganj', 'Valid', 'Completed all practical and theoretical modules'),
(3, 'MCI-CERT-10003', 'Rahul Verma', 'Dinesh Verma', 'Tally Prime + GST', '3 Months', '2025-11-10', 'A+', '91.0%', 'Master Computer Institute, Nawabganj', 'Valid', 'Outstanding accounting and GST filing proficiency')
ON DUPLICATE KEY UPDATE `certificate_no`=VALUES(`certificate_no`);

-- 5. Seed Gallery Images
INSERT INTO `gallery_images` (`id`, `title`, `category`, `image_path`, `alt_text`, `status`, `sort_order`) VALUES
(1, 'Classroom Training Session', 'classroom', 'assets/images/classroom/classroom-training-01.png', 'Master Computer Institute classroom training', 'active', 1),
(2, 'Institute Event Group', 'events', 'assets/images/gallery/gallery-event-group.png', 'Master Computer Institute group event', 'active', 2),
(3, 'Classroom Celebration', 'activities', 'assets/images/gallery/gallery-classroom-celebration.png', 'Master Computer Institute classroom celebration', 'active', 3),
(4, 'Practical Computer Lab Training', 'practical-training', 'assets/images/classroom/practical-training-01.png', 'Master Computer Institute practical training', 'active', 4),
(5, 'Interactive Learning Environment', 'classroom', 'assets/images/classroom/classroom-training-02.png', 'Master Computer Institute classroom learning environment', 'active', 5),
(6, 'Student Gathering Celebration', 'activities', 'assets/images/gallery/gallery-student-celebration.png', 'Master Computer Institute students during a celebration', 'active', 6),
(7, 'Discussion and Seminar Session', 'classroom', 'assets/images/gallery/gallery-discussion-panel.png', 'Master Computer Institute discussion and training session', 'active', 7),
(8, 'Student Certification Award', 'certifications', 'assets/images/certificate to student/student-certification-01.png', 'Master Computer Institute student receiving a certificate', 'active', 8)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- 6. Seed Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('institute_name', 'Master Computer Institute', 'general'),
('tagline', 'Premier Computer Education • Nawabganj', 'general'),
('director_name', 'Shivam Pandey', 'general'),
('address', 'Center near Tripathi Hospital, Nawabganj, Uttar Pradesh - 271303, India', 'contact'),
('city', 'Nawabganj', 'contact'),
('state', 'Uttar Pradesh', 'contact'),
('pincode', '271303', 'contact'),
('phone', '+91 9696525287', 'contact'),
('whatsapp', '919696525287', 'contact'),
('email', 'info@mastercomputer.edu', 'contact'),
('instagram_url', 'https://www.instagram.com/master.computer1/', 'social'),
('instagram_handle', '@master.computer1', 'social'),
('blog_url', 'https://mastercomputerinstitute.blogspot.com', 'social'),
('timing', 'Monday to Saturday: 8:00 AM - 6:00 PM', 'general')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);

-- 7. Sample Initial Enquiries (For testing and immediate dashboard visibility)
INSERT INTO `enquiries` (`id`, `enquiry_code`, `full_name`, `mobile`, `email`, `course_id`, `course_name`, `branch_id`, `branch_name`, `city`, `preferred_batch`, `message`, `source_page`, `status`, `created_at`) VALUES
(1, 'MCI-ENQ-1001', 'Vivek Mishra', '+91 9876543210', 'vivek.mishra@example.com', 3, 'ADCA — Advanced Diploma in Computer Applications', 1, 'Master Computer Institute — Nawabganj', 'Nawabganj', 'Morning', 'I want to know about fee installment plans for ADCA.', 'courses.php', 'New', NOW() - INTERVAL 2 DAY),
(2, 'MCI-ENQ-1002', 'Anjali Gupta', '+91 9812345678', 'anjali.gupta@example.com', 4, 'Tally Prime + GST', 1, 'Master Computer Institute — Nawabganj', 'Gonda', 'Evening', 'Looking for practical GST accounting training starting next week.', 'index.php', 'Follow-up', NOW() - INTERVAL 1 DAY),
(3, 'MCI-ENQ-1003', 'Sandeep Yadav', '+91 9988776655', 'sandeep.y@example.com', 1, 'CCC — Course on Computer Concepts', 1, 'Master Computer Institute — Nawabganj', 'Nawabganj', 'Morning', 'Need guidance for the upcoming NIELIT CCC exam cycle.', 'course-details.php', 'Converted', NOW() - INTERVAL 4 HOUR)
ON DUPLICATE KEY UPDATE `enquiry_code`=VALUES(`enquiry_code`);

-- 8. Sample Initial Admission Enquiries
INSERT INTO `admissions` (`id`, `admission_code`, `full_name`, `dob`, `gender`, `mobile`, `email`, `address`, `city`, `qualification`, `course_id`, `course_name`, `branch_id`, `branch_name`, `batch`, `message`, `status`, `created_at`) VALUES
(1, 'MCI-ADM-1001', 'Rohan Srivastava', '2004-05-12', 'Male', '+91 9765432109', 'rohan.s@example.com', 'Civil Lines, Near Clock Tower', 'Nawabganj', '12th Pass', 3, 'ADCA — Advanced Diploma in Computer Applications', 1, 'Master Computer Institute — Nawabganj', 'Morning', 'Seeking career advancement through 1-year ADCA diploma.', 'Under Review', NOW() - INTERVAL 3 DAY),
(2, 'MCI-ADM-1002', 'Neetu Tiwari', '2005-09-18', 'Female', '+91 9123456780', 'neetu.tiwari@example.com', 'Station Road, Nawabganj', 'Nawabganj', 'Graduate (B.Com)', 4, 'Tally Prime + GST', 1, 'Master Computer Institute — Nawabganj', 'Evening', 'Ready to join immediately for accounting career.', 'Approved', NOW() - INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE `admission_code`=VALUES(`admission_code`);

-- 9. Sample Initial Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `mobile`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Sunil Patel', 'sunil.patel@example.com', '+91 9845123456', 'Weekend Batch Query', 'Do you provide special weekend batches for working professionals in Advanced Excel?', 'Unread', NOW() - INTERVAL 5 HOUR),
(2, 'Kavita Singh', 'kavita.singh@example.com', '+91 9753124680', 'NIELIT Examination Center', 'Where will the practical and online exam for CCC be conducted?', 'Read', NOW() - INTERVAL 2 DAY)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
