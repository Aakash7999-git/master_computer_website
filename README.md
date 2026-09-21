# Master Computer Institute

## Project Overview
This project is the official website and institute management interface for **Master Computer Institute**, located in Nawabganj, Uttar Pradesh (PIN 271303), India, under the direction of **Shivam Pandey**.

- Instagram: [@master.computer1](https://www.instagram.com/master.computer1/)
- Official Blog: [mastercomputerinstitute.blogspot.com](https://mastercomputerinstitute.blogspot.com)

## Features
- Responsive landing page and multi-page frontend
- About, courses, center location, admissions, gallery, contact, and verification pages
- Student and admin login UI screens
- Course and center detail layouts
- Form validation and UI animations
- Professional educational branding aligned with Master Computer Institute
- Prepared layout for future backend integration and database development

## Technology Stack
- HTML5
- CSS3
- JavaScript
- Bootstrap Icons
- Swiper.js
- Java (planned backend)
- JSP / Servlets (planned backend)
- JDBC (planned backend)
- MySQL (planned database)
- Apache Tomcat (planned deployment)

## Project Structure
```text
master-computer/
├── frontend/
│   ├── index.html
│   ├── about.html
│   ├── courses.html
│   ├── course-details.html
│   ├── branches.html
│   ├── admission.html
│   ├── certificate-verification.html
│   ├── contact.html
│   ├── gallery.html
│   ├── login.html
│   ├── admin-login.html
│   ├── css/
│   ├── js/
│   └── assets/
├── backend/
│   └── src/
├── database/
│   ├── schema.sql
│   ├── dummy-data.sql
│   └── queries.sql
├── docs/
│   ├── project-architecture.md
│   ├── database-documentation.md
│   └── api-documentation.md
├── README.md
└── .gitignore
```

## Demo Credentials
The current frontend uses placeholder/demo records only. Example demo values:
- Demo Admin: demo.admin@example.com
- Demo Student: demo.student@example.com
- Demo Certificate Number: MCI-CERT-10001
- Demo Branch Codes: MCI-BR01, MCI-BR02

## How to Run Frontend
1. Open the frontend folder in a browser directly or serve it with a local static server.
2. For example, using Python:
   ```bash
   cd frontend
   python -m http.server 8000
   ```
3. Open `http://localhost:8000` in a browser.

## Database Setup (Planned)
1. Create a MySQL database named `master_computer_institute`.
2. Import the SQL files from the database folder.
3. Use branch-specific and course-specific records as needed.

## Phase Status
- Phase 1 UI: Completed
- Database and backend integration: Pending for next implementation phase

## Notes
All institute details such as addresses, phone numbers and email values are currently marked as dummy data placeholders. These should be replaced with real institute information before production deployment.
