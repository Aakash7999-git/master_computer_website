/**
 * Master Computer Institute - Static Frontend Forms
 * Handles client-side validation and WhatsApp / Formspree integration for enquiries and admissions
 */

// Formspree endpoint (optional - user can insert their endpoint ID e.g. 'https://formspree.io/f/my-form-id')
const FORMSPREE_ENDPOINT = ''; // Leave empty for direct WhatsApp submission

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

