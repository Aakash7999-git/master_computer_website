USE master_computer_institute;

INSERT INTO branches (branch_code, branch_name, address, city, state, pincode, phone, email, opening_time, closing_time, status)
VALUES
('MCI-BR01', 'Master Computer Institute - Branch 1', 'Dummy Address, Mumbai', 'Mumbai', 'Maharashtra', '400001', '+91 90000 00001', 'branch1@example.com', '09:00:00', '19:00:00', 'ACTIVE'),
('MCI-BR02', 'Master Computer Institute - Branch 2', 'Dummy Address, Mumbai', 'Mumbai', 'Maharashtra', '400002', '+91 90000 00002', 'branch2@example.com', '09:00:00', '19:00:00', 'ACTIVE');

INSERT INTO courses (course_code, course_name, description, duration, eligibility, fees, certificate_available, status)
VALUES
('MCI-C01', 'Computer Fundamentals', 'Foundation course for digital literacy and office productivity.', '3 Months', 'Beginners', 4999.00, TRUE, 'ACTIVE'),
('MCI-C02', 'Advanced Excel', 'Spreadsheet and data analysis training for professional productivity.', '2 Months', 'All levels', 3999.00, TRUE, 'ACTIVE'),
('MCI-C03', 'Web Development', 'Practical web development using frontend and backend fundamentals.', '6 Months', 'Beginners/Intermediate', 18999.00, TRUE, 'ACTIVE'),
('MCI-C04', 'Java Programming', 'Structured Java programming for software learning and application development.', '4 Months', 'Intermediate', 12499.00, TRUE, 'ACTIVE'),
('MCI-C05', 'Python Programming', 'Python logic, automation and programming basics for real-world tasks.', '4 Months', 'Beginners', 12499.00, TRUE, 'ACTIVE'),
('MCI-C06', 'Tally + GST', 'Accounting, tax and invoice-related training for business workflows.', '3 Months', 'All levels', 8999.00, TRUE, 'ACTIVE'),
('MCI-C07', 'Graphic Designing', 'Creative design training using modern visual tools and principles.', '4 Months', 'Beginners', 11499.00, TRUE, 'ACTIVE'),
('MCI-C08', 'Digital Marketing', 'Online marketing strategy, content and modern growth fundamentals.', '4 Months', 'All levels', 10999.00, TRUE, 'ACTIVE');

INSERT INTO course_branches (course_id, branch_id, status)
SELECT c.id, b.id, 'ACTIVE' FROM courses c, branches b;

INSERT INTO admins (username, email, password_hash, full_name, role, status)
VALUES
('demo.admin', 'demo.admin@example.com', '$2a$10$7Z3Q5m2u2Z4ZgL09S5U0X.G9WQm3s8DqH3KcQxF2sYzQ2YH0xG9G2', 'Demo Admin', 'ADMIN', 'ACTIVE');

INSERT INTO students (student_id, full_name, dob, gender, mobile, email, address, city, qualification, course_id, branch_id, admission_date, password_hash, status)
VALUES
('MCI-STU-1001', 'Demo Student 01', '2002-05-15', 'Female', '+91 98765 43210', 'demo.student1@example.com', 'Dummy Address 1, Mumbai', 'Mumbai', '12th', 3, 1, '2026-01-15', '$2a$10$7Z3Q5m2u2Z4ZgL09S5U0X.G9WQm3s8DqH3KcQxF2sYzQ2YH0xG9G2', 'ACTIVE'),
('MCI-STU-1002', 'Demo Student 02', '2001-08-20', 'Male', '+91 98765 43211', 'demo.student2@example.com', 'Dummy Address 2, Mumbai', 'Mumbai', 'Graduate', 5, 2, '2026-02-10', '$2a$10$7Z3Q5m2u2Z4ZgL09S5U0X.G9WQm3s8DqH3KcQxF2sYzQ2YH0xG9G2', 'ACTIVE');

INSERT INTO admissions (application_id, full_name, dob, gender, mobile, email, address, course_id, branch_id, qualification, preferred_batch, message, status)
VALUES
('MCI-APP-000001', 'Demo Applicant 01', '2003-04-18', 'Female', '+91 90909 11111', 'applicant1@example.com', 'Dummy Street 1, Mumbai', 1, 1, '10th', 'Morning', 'Interested in beginner course.', 'PENDING'),
('MCI-APP-000002', 'Demo Applicant 02', '2000-11-25', 'Male', '+91 90909 11112', 'applicant2@example.com', 'Dummy Street 2, Mumbai', 2, 2, '12th', 'Evening', 'Interested in Excel course.', 'APPROVED');

INSERT INTO certificates (certificate_number, student_id, course_id, branch_id, issue_date, status)
VALUES
('MCI-CERT-10001', 1, 3, 1, '2026-06-01', 'VALID'),
('MCI-CERT-10002', 2, 5, 2, '2026-06-15', 'VALID');

INSERT INTO enquiries (name, email, mobile, subject, message, status)
VALUES
('Demo Enquiry 01', 'enquiry1@example.com', '+91 91234 56789', 'Course Guidance', 'I want information about Web Development.', 'NEW'),
('Demo Enquiry 02', 'enquiry2@example.com', '+91 91234 56788', 'Branch Details', 'Please share the branch timings.', 'RESOLVED');

INSERT INTO attendance (student_id, present_days, total_days, percentage)
VALUES
(1, 18, 20, 90.00),
(2, 16, 20, 80.00);

INSERT INTO fees (student_id, total_fee, paid_amount, outstanding, status)
VALUES
(1, 18999.00, 12000.00, 6999.00, 'PARTIAL'),
(2, 12499.00, 12499.00, 0.00, 'PAID');

INSERT INTO gallery (title, category, image_path)
VALUES
('Classroom Session', 'Classrooms', 'assets/images/gallery/classroom-01.jpg'),
('Event Day', 'Events', 'assets/images/gallery/event-01.jpg'),
('Workshop Activity', 'Workshops', 'assets/images/gallery/workshop-01.jpg'),
('Student Group', 'Students', 'assets/images/gallery/student-01.jpg');

INSERT INTO testimonials (student_name, course_name, message, status)
VALUES
('Demo Student 01', 'Web Development', 'Great learning environment and supportive trainers.', 'ACTIVE'),
('Demo Student 02', 'Python Programming', 'The course made complex topics easier to understand.', 'ACTIVE');
