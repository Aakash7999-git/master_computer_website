/**
 * Master Computer Institute - Static Course Details Loader
 * Enables dynamic switching on course-details.html without any backend server
 */
(function () {
  const coursesData = {
    'ccc': {
      id: 1,
      code: 'CCC',
      name: 'CCC — Course on Computer Concepts',
      category: 'Govt. Recognized',
      duration: '3 Months',
      eligibility: '10th / Any Student',
      fee: '₹3,499',
      certification: 'NIELIT Exam Prep & MCI Certification',
      image: 'assets/images/courses/ccc-course.jpg',
      description: 'Course on Computer Concepts (CCC) is designed to impart a basic level IT literacy programme for the common man. It provides students and job aspirants with practical computer fundamentals, operating system operations, MS Office tools, digital banking, and government portal services required for competitive and public examinations.'
    },
    'dca': {
      id: 2,
      code: 'DCA',
      name: 'DCA — Diploma in Computer Applications',
      category: 'Diploma',
      duration: '6 Months',
      eligibility: '10th / 12th Pass',
      fee: '₹6,499',
      certification: 'MCI Diploma Certificate',
      image: 'assets/images/courses/dca-course.jpg',
      description: 'Diploma in Computer Applications (DCA) is a 6-month specialized program for beginners and intermediate learners. It equips students with hands-on capabilities across Word, Excel, PowerPoint, Access, database fundamentals, Internet applications, and clerical automation.'
    },
    'adca': {
      id: 3,
      code: 'ADCA',
      name: 'ADCA — Advanced Diploma in Computer Applications',
      category: 'Advanced Diploma',
      duration: '12 Months',
      eligibility: '10th / 12th / Graduate',
      fee: '₹11,999',
      certification: 'Govt. Registered Master Diploma',
      image: 'assets/images/courses/adca-course.jpg',
      description: 'The Advanced Diploma in Computer Applications (ADCA) is our flagship 1-year career track. Students master advanced office automation, computerized accounting with Tally Prime and GST, visual designing in Photoshop and CorelDRAW, internet protocols, and workplace productivity.'
    },
    'tally': {
      id: 4,
      code: 'Tally Prime + GST',
      name: 'Tally Prime + GST',
      category: 'Accounting',
      duration: '3 Months',
      eligibility: '10th / 12th / Commerce Preferred',
      fee: '₹7,999',
      certification: 'Tally Accounting Professional Certificate',
      image: 'assets/images/courses/tally-course.jpg',
      description: 'Tally Prime with GST is an intensive accounting course designed for commerce students, business owners, and accounting job seekers. Topics include ledger creation, voucher entry, inventory tracking, GST computation, e-invoicing, bank reconciliation, and profit & loss statements.'
    },
    'excel': {
      id: 5,
      code: 'Advanced Excel & MIS',
      name: 'Advanced Excel & MIS',
      category: 'Office Skills',
      duration: '2 Months',
      eligibility: 'Basic Computer Knowledge',
      fee: '₹3,999',
      certification: 'Advanced Excel Specialist Certificate',
      image: 'assets/images/courses/advanced-excel-course.jpg',
      description: 'Learn advanced spreadsheet modeling, VLOOKUP, XLOOKUP, INDEX-MATCH, nested logical formulas, dynamic Pivot Tables, conditional formatting, dashboard visualization, and executive MIS reporting workflows.'
    },
    'graphic': {
      id: 6,
      code: 'Graphic Designing',
      name: 'Graphic Designing',
      category: 'Creative',
      duration: '4 Months',
      eligibility: 'Beginner to Intermediate',
      fee: '₹9,999',
      certification: 'Professional Graphic Design Certificate',
      image: 'assets/images/courses/graphic-design-course.jpg',
      description: 'Hands-on practical training in Adobe Photoshop, CorelDRAW, vector illustration, digital photo manipulation, logo design, marketing flyers, social media banners, typography, and commercial print preparation.'
    },
    'digital-mkt': {
      id: 7,
      code: 'Digital Marketing',
      name: 'Digital Marketing',
      category: 'Marketing',
      duration: '4 Months',
      eligibility: 'All levels / 12th Pass',
      fee: '₹10,499',
      certification: 'Digital Marketing Specialist Certificate',
      image: 'assets/images/courses/digital-marketing-course.jpg',
      description: 'Comprehensive digital marketing course covering search engine optimization (SEO), Google Search Console, social media marketing (Instagram, Facebook, LinkedIn), search engine marketing (Google Ads), email outreach, and brand analytics.'
    },
    'fundamentals': {
      id: 8,
      code: 'Fundamentals & Typing',
      name: 'Computer Fundamentals & Typing',
      category: 'Foundation',
      duration: '2 Months',
      eligibility: 'Open to All Beginners',
      fee: '₹2,999',
      certification: 'Certificate of Completion & Speed Record',
      image: 'assets/images/courses/computer-fundamentals-course.jpg',
      description: 'Ideal foundational training for school students and beginners learning hardware parts, Windows OS navigation, file management, Hindi (KrutiDev / Mangal) and English touch typing, and safe internet browsing.'
    }
  };

  function getQueryParam(param) {
    const params = new URLSearchParams(window.location.search);
    return params.get(param);
  }

  function resolveCourse() {
    const rawCourse = (getQueryParam('course') || getQueryParam('code') || getQueryParam('slug') || '').toLowerCase();
    const id = getQueryParam('id');

    if (rawCourse) {
      for (const [key, c] of Object.entries(coursesData)) {
        if (key === rawCourse || c.code.toLowerCase() === rawCourse || rawCourse.includes(key)) {
          return c;
        }
      }
    }

    if (id) {
      for (const c of Object.values(coursesData)) {
        if (String(c.id) === String(id)) return c;
      }
    }

    // Default to flagship ADCA course
    return coursesData['adca'];
  }

  document.addEventListener('DOMContentLoaded', () => {
    const course = resolveCourse();
    if (!course) return;

    // Update document title and elements
    document.title = `${course.name} | Course Details | Master Computer Institute`;
    
    const cdBreadcrumbCode = document.getElementById('cdBreadcrumbCode');
    if (cdBreadcrumbCode) cdBreadcrumbCode.textContent = course.code;

    const cdTitle = document.getElementById('cdTitle');
    if (cdTitle) cdTitle.textContent = course.name;

    const cdSubtitle = document.getElementById('cdSubtitle');
    if (cdSubtitle) cdSubtitle.textContent = `Duration: ${course.duration} | Eligibility: ${course.eligibility}`;

    const cdImage = document.getElementById('cdImage');
    if (cdImage) {
      cdImage.src = course.image;
      cdImage.alt = `${course.name} practical training at Master Computer Institute`;
    }

    const cdDescription = document.getElementById('cdDescription');
    if (cdDescription) {
      cdDescription.innerHTML = `<p>${course.description}</p>`;
    }

    const cdSumCode = document.getElementById('cdSumCode');
    if (cdSumCode) cdSumCode.textContent = course.code;

    const cdSumTitle = document.getElementById('cdSumTitle');
    if (cdSumTitle) cdSumTitle.textContent = course.name;

    const cdSumCategory = document.getElementById('cdSumCategory');
    if (cdSumCategory) cdSumCategory.textContent = course.category;

    const cdSumDuration = document.getElementById('cdSumDuration');
    if (cdSumDuration) cdSumDuration.textContent = course.duration;

    const cdSumEligibility = document.getElementById('cdSumEligibility');
    if (cdSumEligibility) cdSumEligibility.textContent = course.eligibility;

    const cdSumFee = document.getElementById('cdSumFee');
    if (cdSumFee) cdSumFee.textContent = course.fee;

    const cdSumCert = document.getElementById('cdSumCert');
    if (cdSumCert) cdSumCert.textContent = course.certification;

    const cdEnquireBtn = document.getElementById('cdEnquireBtn');
    if (cdEnquireBtn) {
      cdEnquireBtn.onclick = () => {
        if (typeof openEnquireModal === 'function') {
          openEnquireModal(course.name);
        }
      };
    }
  });
})();
