/**
 * Master Computer Institute - Static Frontend Forms & Verification
 * Handles client-side validation, WhatsApp / Formspree integration, and real certificate lookup
 */

// Formspree endpoint (optional - user can insert their endpoint ID e.g. 'https://formspree.io/f/my-form-id')
const FORMSPREE_ENDPOINT = ''; // Leave empty for direct WhatsApp submission

// Registered official institute certificates database
const REGISTERED_CERTIFICATES = {
  'MCI-CERT-10001': {
    certificate_no: 'MCI-CERT-10001',
    student_name: 'Amit Kumar',
    father_name: 'Ramesh Kumar',
    course_name: 'ADCA — Advanced Diploma in Computer Applications',
    duration: '12 Months',
    issue_date: '15 Jun 2025',
    grade: 'A+ (88.5%)',
    center_name: 'Master Computer Institute, Nawabganj',
    status: 'Valid'
  },
  'MCI-CERT-10002': {
    certificate_no: 'MCI-CERT-10002',
    student_name: 'Priya Sharma',
    father_name: 'Suresh Sharma',
    course_name: 'CCC — Course on Computer Concepts',
    duration: '3 Months',
    issue_date: '20 Aug 2025',
    grade: 'A (82.0%)',
    center_name: 'Master Computer Institute, Nawabganj',
    status: 'Valid'
  },
  'MCI-CERT-10003': {
    certificate_no: 'MCI-CERT-10003',
    student_name: 'Rahul Verma',
    father_name: 'Dinesh Verma',
    course_name: 'Tally Prime + GST',
    duration: '3 Months',
    issue_date: '10 Nov 2025',
    grade: 'A+ (91.0%)',
    center_name: 'Master Computer Institute, Nawabganj',
    status: 'Valid'
  }
};

// Global Enquire Modal Functions
function openEnquireModal(courseName = '') {
  const modal = document.getElementById('enquireModal');
  const courseSelect = document.getElementById('modalEnqCourse');
  const msgBox = document.getElementById('modalEnquiryMessage');

  if (msgBox) {
    msgBox.style.display = 'none';
    msgBox.className = 'form-message';
  }

  if (courseSelect && courseName) {
    let matched = false;
    for (let opt of courseSelect.options) {
      if (opt.value.toLowerCase().includes(courseName.toLowerCase()) || courseName.toLowerCase().includes(opt.value.toLowerCase())) {
        opt.selected = true;
        matched = true;
        break;
      }
    }
  }

  if (modal) {
    modal.classList.add('is-active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
}

function closeEnquireModal() {
  const modal = document.getElementById('enquireModal');
  if (modal) {
    modal.classList.remove('is-active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
}

window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeEnquireModal();
});

document.addEventListener('click', (e) => {
  const modal = document.getElementById('enquireModal');
  if (modal && e.target === modal) {
    closeEnquireModal();
  }
});

function showFormMsg(el, msg, type) {
  if (!el) return;
  el.className = `form-message ${type}`;
  el.textContent = msg;
  el.style.display = 'block';
}

// 1. Static Course Enquiry Handler (Modal)
async function handleStaticEnquiry(e) {
  e.preventDefault();
  const form = e.target;
  const submitBtn = document.getElementById('modalEnqSubmitBtn');
  const msgBox = document.getElementById('modalEnquiryMessage');

  const name = (form.fullName?.value || '').trim();
  const mobile = (form.mobile?.value || '').trim();
  const email = (form.email?.value || '').trim();
  const course = (form.course?.value || '').trim();
  const branch = (form.branch?.value || 'Master Computer Institute — Nawabganj').trim();
  const batch = (form.batch?.value || 'Morning').trim();
  const message = (form.message?.value || '').trim();

  if (!name) {
    showFormMsg(msgBox, 'Please enter your full name.', 'error');
    form.fullName?.focus();
    return;
  }
  const cleanMobile = mobile.replace(/[^0-9]/g, '');
  if (cleanMobile.length < 10) {
    showFormMsg(msgBox, 'Please enter a valid 10-digit mobile number.', 'error');
    form.mobile?.focus();
    return;
  }
  if (!course) {
    showFormMsg(msgBox, 'Please select a course.', 'error');
    form.course?.focus();
    return;
  }

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span>Connecting to Institute...</span>';

  // If Formspree endpoint is configured, try submitting
  if (FORMSPREE_ENDPOINT) {
    try {
      const res = await fetch(FORMSPREE_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name, mobile, email, course, branch, batch, message })
      });
      if (res.ok) {
        showFormMsg(msgBox, 'Enquiry submitted successfully! Our admissions counselor will contact you shortly.', 'success');
        form.reset();
        setTimeout(closeEnquireModal, 2500);
        return;
      }
    } catch (err) {
      // Fallback to WhatsApp
    }
  }

  // Direct WhatsApp Enquiry connection
  const waText = encodeURIComponent(
    `*Course Enquiry — Master Computer Institute*\n` +
    `• Name: ${name}\n` +
    `• Mobile: ${mobile}\n` +
    (email ? `• Email: ${email}\n` : '') +
    `• Course: ${course}\n` +
    `• Center: ${branch}\n` +
    `• Batch: ${batch}\n` +
    (message ? `• Note: ${message}\n` : '')
  );

  window.open(`https://wa.me/919696525287?text=${waText}`, '_blank');
  showFormMsg(msgBox, 'Enquiry prepared. Continuing to WhatsApp to connect with our institute admissions desk...', 'success');
  
  setTimeout(() => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<span>Submit Enquiry</span>';
    closeEnquireModal();
  }, 2000);
}

// 2. Static Admission Application Handler (Homepage & Admission Page)
async function handleStaticAdmission(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
  const msg = form.querySelector('.form-message') || document.getElementById('homeAdmMsg') || document.getElementById('admPageMsg');

  const name = (form.fullName?.value || '').trim();
  const dob = (form.dob?.value || '').trim();
  const gender = (form.gender?.value || '').trim();
  const mobile = (form.mobile?.value || '').trim();
  const email = (form.email?.value || '').trim();
  const city = (form.city?.value || '').trim();
  const address = (form.address?.value || '').trim();
  const course = (form.course?.value || '').trim();
  const branch = (form.branch?.value || '').trim();
  const qualification = (form.qualification?.value || '').trim();
  const batch = (form.batch?.value || '').trim();
  const message = (form.message?.value || '').trim();

  if (!name || !mobile || !email || !course || !address) {
    if (msg) showFormMsg(msg, 'Please fill out all required fields marked with (*).', 'error');
    return;
  }

  const cleanMobile = mobile.replace(/[^0-9]/g, '');
  if (cleanMobile.length < 10) {
    if (msg) showFormMsg(msg, 'Please enter a valid 10-digit mobile number.', 'error');
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Processing Application...';
  }

  if (FORMSPREE_ENDPOINT) {
    try {
      const res = await fetch(FORMSPREE_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name, dob, gender, mobile, email, city, address, course, branch, qualification, batch, message })
      });
      if (res.ok) {
        if (msg) showFormMsg(msg, 'Admission application received! Our admissions office will verify your details and contact you.', 'success');
        form.reset();
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Enquire Now';
        }
        return;
      }
    } catch (err) {
      // Fallback
    }
  }

  // WhatsApp Fallback
  const waText = encodeURIComponent(
    `*Online Admission Application — Master Computer Institute*\n` +
    `• Applicant: ${name}\n` +
    `• Mobile: ${mobile}\n` +
    `• Email: ${email}\n` +
    `• Course: ${course}\n` +
    `• Branch: ${branch}\n` +
    `• City: ${city}\n` +
    `• Address: ${address}\n` +
    (qualification ? `• Qualification: ${qualification}\n` : '') +
    (batch ? `• Batch: ${batch}\n` : '') +
    (message ? `• Note: ${message}\n` : '')
  );

  window.open(`https://wa.me/919696525287?text=${waText}`, '_blank');
  if (msg) showFormMsg(msg, 'Application details prepared. Continuing to WhatsApp to complete your admission enquiry with the counselor...', 'success');

  if (btn) {
    btn.disabled = false;
    btn.textContent = 'Enquire Now';
  }
}

// 3. Static Contact Form Handler
async function handleStaticContact(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
  const msg = form.querySelector('.form-message') || document.getElementById('homeContactMsg') || document.getElementById('contactPageMsg');

  const name = (form.contactName?.value || '').trim();
  const email = (form.contactEmail?.value || '').trim();
  const mobile = (form.contactMobile?.value || '').trim();
  const subject = (form.contactSubject?.value || '').trim();
  const message = (form.contactMessage?.value || '').trim();

  if (!name || !email || !message) {
    if (msg) showFormMsg(msg, 'Please fill out your name, email and message.', 'error');
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Sending Message...';
  }

  if (FORMSPREE_ENDPOINT) {
    try {
      const res = await fetch(FORMSPREE_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name, email, mobile, subject, message })
      });
      if (res.ok) {
        if (msg) showFormMsg(msg, 'Thank you! Your message has been sent to Master Computer Institute.', 'success');
        form.reset();
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Send Message';
        }
        return;
      }
    } catch (err) {}
  }

  const waText = encodeURIComponent(
    `*Contact Inquiry — Master Computer Institute*\n` +
    `• Name: ${name}\n` +
    `• Email: ${email}\n` +
    (mobile ? `• Mobile: ${mobile}\n` : '') +
    (subject ? `• Subject: ${subject}\n` : '') +
    `• Message: ${message}\n`
  );

  window.open(`https://wa.me/919696525287?text=${waText}`, '_blank');
  if (msg) showFormMsg(msg, 'Connecting to Master Computer Institute helpline on WhatsApp...', 'success');

  if (btn) {
    btn.disabled = false;
    btn.textContent = 'Send Message';
  }
}

// 4. Static Certificate Verification
function handleStaticCertificateVerify(e, inputId, btnId, msgId, resultBoxId) {
  e.preventDefault();
  const input = document.getElementById(inputId);
  const btn = document.getElementById(btnId);
  const msg = document.getElementById(msgId);
  const resultBox = document.getElementById(resultBoxId);

  const certNo = (input?.value || '').trim().toUpperCase();

  if (!certNo) {
    if (msg) showFormMsg(msg, 'Please enter a certificate number.', 'error');
    if (resultBox) resultBox.style.display = 'none';
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Verifying Record...';
  }
  if (msg) msg.style.display = 'none';
  if (resultBox) resultBox.style.display = 'none';

  setTimeout(() => {
    const cert = REGISTERED_CERTIFICATES[certNo];

    if (cert) {
      // Populate fields on homepage verification box
      const resCertNo = document.getElementById('resCertNo');
      if (resCertNo) resCertNo.textContent = cert.certificate_no;
      const resStudentName = document.getElementById('resStudentName');
      if (resStudentName) resStudentName.textContent = cert.student_name;
      const resCourseName = document.getElementById('resCourseName');
      if (resCourseName) resCourseName.textContent = cert.course_name;
      const resDuration = document.getElementById('resDuration');
      if (resDuration) resDuration.textContent = cert.duration;
      const resIssueDate = document.getElementById('resIssueDate');
      if (resIssueDate) resIssueDate.textContent = cert.issue_date;
      const resGrade = document.getElementById('resGrade');
      if (resGrade) resGrade.textContent = cert.grade;
      const resCenter = document.getElementById('resCenter');
      if (resCenter) resCenter.textContent = cert.center_name;

      // Populate fields on dedicated certificate page card
      const cardCertNo = document.getElementById('cardCertNo');
      if (cardCertNo) cardCertNo.textContent = cert.certificate_no;
      const cardStudentName = document.getElementById('cardStudentName');
      if (cardStudentName) cardStudentName.textContent = cert.student_name;
      const cardCourseName = document.getElementById('cardCourseName');
      if (cardCourseName) cardCourseName.textContent = cert.course_name;
      const cardDuration = document.getElementById('cardDuration');
      if (cardDuration) cardDuration.textContent = cert.duration;
      const cardIssueDate = document.getElementById('cardIssueDate');
      if (cardIssueDate) cardIssueDate.textContent = cert.issue_date;
      const cardGrade = document.getElementById('cardGrade');
      if (cardGrade) cardGrade.textContent = cert.grade;
      const cardCenter = document.getElementById('cardCenter');
      if (cardCenter) cardCenter.textContent = cert.center_name;
      const cardTimestamp = document.getElementById('cardTimestamp');
      if (cardTimestamp) cardTimestamp.textContent = 'Verified: ' + new Date().toLocaleString();

      if (resultBox) resultBox.style.display = 'block';
    } else {
      if (msg) {
        showFormMsg(msg, `No certificate found matching "${certNo}". Please check the certificate number or contact Master Computer Institute.`, 'error');
      }
    }

    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Verify Certificate';
    }
  }, 400);
}

// 5. Static Student Login Handler (Disabled in static architecture)
function handleStaticStudentLogin(e) {
  e.preventDefault();
  const msg = document.getElementById('studentLoginMsg');
  if (msg) {
    showFormMsg(msg, 'Online student logins are currently disabled in this static release. Please contact your center front desk for batch schedules and study materials, or verify certificates via Certificate Verification.', 'info');
  }
}

