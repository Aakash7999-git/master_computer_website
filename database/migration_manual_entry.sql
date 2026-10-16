-- ==========================================================
-- Master Computer Institute - Database Migration for Manual Entry
-- Adds Offline Enquiry, Offline Admission, and Student Management
-- ==========================================================

USE `master_computer_institute`;

-- 1. Update `enquiries` table
ALTER TABLE `enquiries`
  ADD COLUMN IF NOT EXISTS `source` ENUM('Website', 'Offline') NOT NULL DEFAULT 'Website' AFTER `source_page`,
  ADD COLUMN IF NOT EXISTS `created_by_admin_id` INT UNSIGNED NULL DEFAULT NULL AFTER `assigned_staff`,
  ADD INDEX IF NOT EXISTS `idx_enquiry_source` (`source`);

-- Add foreign key constraint if not exists
SET @fk_enquiries_admin_exists = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'enquiries' AND CONSTRAINT_NAME = 'fk_enquiries_admin'
);
SET @sql_enquiries_fk = IF(@fk_enquiries_admin_exists = 0,
  'ALTER TABLE `enquiries` ADD CONSTRAINT `fk_enquiries_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL',
  'SELECT "fk_enquiries_admin already exists"'
);
PREPARE stmt FROM @sql_enquiries_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- 2. Update `admissions` table
ALTER TABLE `admissions`
  ADD COLUMN IF NOT EXISTS `alternate_mobile` VARCHAR(25) NULL DEFAULT NULL AFTER `mobile`,
  ADD COLUMN IF NOT EXISTS `admission_date` DATE NULL DEFAULT NULL AFTER `batch`,
  ADD COLUMN IF NOT EXISTS `source` ENUM('Website', 'Offline') NOT NULL DEFAULT 'Website' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `created_by_admin_id` INT UNSIGNED NULL DEFAULT NULL AFTER `source`,
  MODIFY COLUMN `email` VARCHAR(100) NULL DEFAULT NULL,
  MODIFY COLUMN `address` TEXT NULL DEFAULT NULL,
  MODIFY COLUMN `city` VARCHAR(100) NULL DEFAULT NULL,
  MODIFY COLUMN `qualification` VARCHAR(100) NULL DEFAULT NULL,
  MODIFY COLUMN `batch` VARCHAR(50) NULL DEFAULT NULL,
  ADD INDEX IF NOT EXISTS `idx_admission_source` (`source`);

-- Set default admission_date for existing records where null
UPDATE `admissions` SET `admission_date` = DATE(`created_at`) WHERE `admission_date` IS NULL;

-- Add foreign key constraint if not exists
SET @fk_admissions_admin_exists = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'admissions' AND CONSTRAINT_NAME = 'fk_admissions_admin'
);
SET @sql_admissions_fk = IF(@fk_admissions_admin_exists = 0,
  'ALTER TABLE `admissions` ADD CONSTRAINT `fk_admissions_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL',
  'SELECT "fk_admissions_admin already exists"'
);
PREPARE stmt FROM @sql_admissions_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- 3. Create `students` table if not exists
CREATE TABLE IF NOT EXISTS `students` (
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
