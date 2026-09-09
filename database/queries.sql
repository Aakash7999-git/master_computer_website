USE master_computer_institute;

-- All active branches
SELECT * FROM branches WHERE status = 'ACTIVE';

-- All students in a specific branch
SELECT s.* FROM students s WHERE s.branch_id = 1;

-- All admissions for a specific course
SELECT * FROM admissions WHERE course_id = 3;

-- Students with course and branch details
SELECT s.student_id, s.full_name, c.course_name, b.branch_name
FROM students s
JOIN courses c ON s.course_id = c.id
JOIN branches b ON s.branch_id = b.id;

-- Pending admissions
SELECT * FROM admissions WHERE status = 'PENDING';

-- Valid certificate verification
SELECT c.certificate_number, s.full_name, co.course_name, b.branch_name, c.issue_date, c.status
FROM certificates c
JOIN students s ON c.student_id = s.id
JOIN courses co ON c.course_id = co.id
JOIN branches b ON c.branch_id = b.id
WHERE c.certificate_number = 'MCI-CERT-10001';

-- Admin login by email
SELECT * FROM admins WHERE email = 'demo.admin@example.com';

-- Student login by email
SELECT * FROM students WHERE email = 'demo.student1@example.com';
