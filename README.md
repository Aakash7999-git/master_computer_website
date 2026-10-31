# Master Computer Institute — Official Website

Official website for **Master Computer Institute**, premier computer training center located near Tripathi Hospital, Nawabganj, Uttar Pradesh (PIN 271303), India, under the directorship of **Shivam Pandey**.

- **Official Instagram**: [@master.computer1](https://www.instagram.com/master.computer1/)
- **Official Blog**: [mastercomputerinstitute.blogspot.com](https://mastercomputerinstitute.blogspot.com)

---

## 1. Project Overview & Architecture

The website is a **100% static, frontend-only, production-ready web application** built with clean HTML5, CSS3, Vanilla JavaScript, and Bootstrap Icons. It is completely decoupled from any server-side runtime, PHP processing, or MySQL database, making it exceptionally fast, secure, lightweight, and deployable to any static web host, CDN, or custom domain.

### Key Highlights
- **100% Preserved UI & Design**: Pixel-for-pixel preservation of all brand colors, typography, responsive grid layouts, Swiper carousels, and smooth animations.
- **Zero Backend Dependencies**: Operates entirely client-side without PHP, MySQL, Apache XAMPP, or Node.js runtime requirements.
- **Universal "Enquire Now" Modal**: Available on every page and course card, prefilling the selected course and connecting inquiries via WhatsApp and configurable form services (Formspree).
- **Interactive Certificate Verification**: Real-time client-side verification of official student certificate credentials against registered institute records.
- **Dynamic Course Details**: Single lightweight template (`course-details.html`) that seamlessly renders syllabus, pricing, and duration for any of the 8 certified courses via query parameters (`?course=adca`, `?course=ccc`, etc.).
- **Direct WhatsApp Helpline**: Floating WhatsApp button and fallback routing ensuring no student inquiry is ever missed.
- **Static Hosting Ready**: Includes pre-configured redirects for Netlify (`_redirects`), Vercel (`vercel.json`), XML sitemap (`sitemap.xml`), and custom 404 handler (`404.html`).

---

## 2. Directory Structure

```text
master-computer/
├── index.html                     # Homepage with hero, courses, branches, gallery, verification & contact
├── about.html                     # About institute, leadership (Director Shivam Pandey) & mission
├── courses.html                   # All 8 computer courses catalog with durations & pricing
├── course-details.html            # Course syllabus, details, summary box & enquiry action
├── branches.html                  # Main Center & Extension Campus locations and details
├── admission.html                 # Online student admission enquiry application form
├── gallery.html                   # Campus gallery with filterable categories & lightbox modal
├── certificate-verification.html  # Official student certificate verification portal
├── contact.html                   # Inbound visitor inquiries, address, phone & direct WhatsApp
├── login.html                     # Student portal login screen
├── 404.html                       # Branded custom 404 page not found
├── _redirects                     # Netlify / Cloudflare Pages 301 redirects and fallback routing
├── vercel.json                    # Vercel deployment configuration
├── robots.txt                     # SEO search engine crawl directives
├── sitemap.xml                    # Complete XML sitemap for search indexation
├── assets/
│   ├── fonts/                     # Inter & Poppins web fonts
│   ├── images/                    # Classrooms, campus gallery, hero graphics, course thumbnails
│   ├── logos/                     # Official vector logo (mci-logo.svg)
│   └── vendor/
│       ├── bootstrap-icons/       # Bootstrap Icons fonts & styles
│       └── swiper/                # Swiper carousel bundle
├── css/
│   ├── style.css                  # Core design tokens, typography, grid & component styles
│   ├── responsive.css             # Mobile, tablet & desktop viewport breakpoints
│   └── animations.css             # Scroll reveals, floating badges & hover transitions
└── js/
    ├── main.js                    # Navbar, mobile drawer, counter animations & gallery lightbox
    ├── animations.js              # Tilt scenes & interactive micro-animations
    ├── course-details.js          # Dynamic course parameter resolver for course-details.html
    └── forms.js                   # Client-side form validation, WhatsApp routing & certificate lookup
```

---

## 3. Courses Offered

| Course Code | Full Course Title | Duration | Category |
| :--- | :--- | :--- | :--- |
| **CCC** | CCC — Course on Computer Concepts | 3 Months | Govt. Recognized |
| **DCA** | DCA — Diploma in Computer Applications | 6 Months | Diploma |
| **ADCA** | ADCA — Advanced Diploma in Computer Applications | 12 Months | Advanced Diploma |
| **TALLY** | Tally Prime + GST | 3 Months | Accounting |
| **EXCEL** | Advanced Excel & MIS | 2 Months | Office Skills |
| **GRAPHIC** | Graphic Designing (Photoshop & CorelDRAW) | 4 Months | Creative |
| **DIGITAL-MKT** | Digital Marketing & SEO | 4 Months | Marketing |
| **FUNDAMENTALS** | Computer Fundamentals & Typing (Hindi & English) | 2 Months | Foundation |

---

## 4. Local Testing & Verification

Because this is a pure static website, you do not need Apache or MySQL to test it:

### Method A: Direct Browser Preview
Double-click `index.html` to open it in any web browser (Chrome, Edge, Firefox, Safari).

### Method B: Any Local Static Server (Python, Node, or VS Code Live Server)
```bash
# Python 3
python -m http.server 8080

# Or Node npx serve
npx serve .
```
Then visit `http://localhost:8080` in your browser.

---

## 5. Deployment Options

### Deploy to Netlify (Recommended)
1. Drag and drop the project folder directly into the [Netlify Drop](https://app.netlify.com/drop) dashboard, OR connect your GitHub repository.
2. Build command: *(leave empty)*
3. Publish directory: `.` (root)
4. Custom domain: Point your domain's DNS `A` / `CNAME` records to Netlify.

### Deploy to Vercel
1. Install Vercel CLI (`npm i -g vercel`) and run `vercel`, or import the GitHub repository in the Vercel dashboard.
2. Framework Preset: **Other**.
3. Output Directory: `.` (root).

### Deploy to GitHub Pages
1. In repository **Settings > Pages**, choose `Deploy from a branch`.
2. Select branch `main` and folder `/ (root)`, then click **Save**.

### Deploy to cPanel / Shared Hosting
1. Compress the project files into a `.zip` file.
2. In cPanel **File Manager**, navigate to `public_html/`.
3. Upload and extract the files directly into `public_html/`.

---

## 6. Form Handling & WhatsApp Integration

All forms (Course Enquiry modal, Admission Application, and Contact Us) are handled in `js/forms.js`:
- **Client-Side Validation**: Ensures valid 10-digit phone numbers, valid email formats, and required field completion before submitting.
- **WhatsApp Direct Connection**: Connects the visitor directly to the Master Computer Institute official counselor WhatsApp (`+91 9696525287`) with the application details neatly pre-filled.
- **Formspree / External Form Service (Optional)**: To receive submissions via email through Formspree, set your Formspree endpoint at the top of `js/forms.js`:
  ```javascript
  const FORMSPREE_ENDPOINT = 'https://formspree.io/f/YOUR_FORM_ID';
  ```

---

## 7. Certificate Verification System

The website includes an authentic certificate verification system in `js/forms.js`:
- Supports instant lookup by certificate number (e.g., `MCI-CERT-10001`, `MCI-CERT-10002`, `MCI-CERT-10003`).
- Displays student name, father's name, course completed, duration, issue date, grade, and center verification stamp upon a match.
- Displays an accurate "No record found" alert when an invalid certificate number is entered.
- To add newly issued student certificates, simply append new records to the `REGISTERED_CERTIFICATES` object in `js/forms.js`.

---

## 8. License & Credits

- **Institute**: Master Computer Institute, Nawabganj, Uttar Pradesh (PIN 271303), India.
- **Director**: Shivam Pandey
- **Technologies**: HTML5, CSS3, JavaScript (ES6), Bootstrap Icons, Swiper.js.
