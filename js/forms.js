/**
 * Master Computer Institute - Static Frontend Forms
 * Official Formspree Email Integration & WhatsApp Alternative
 */

// Centralized Official Formspree Endpoint
const FORMSPREE_ENDPOINT = 'https://formspree.io/f/xdeelbla';

// Official Institute Helpline
const MCI_WHATSAPP_NUMBER = '919696525287';

// Global Enquire Modal Functions
function openEnquireModal(courseName = '') {
  const modal = document.getElementById('enquireModal');
  const courseSelect = document.getElementById('modalEnqCourse');
  const msgBox = document.getElementById('modalEnquiryMessage');

  if (msgBox) {
    msgBox.style.display = 'none';
    msgBox.className = 'form-message';
    msgBox.textContent = '';
  }

  if (courseSelect && courseName) {
    for (let opt of courseSelect.options) {
      if (opt.value.toLowerCase().includes(courseName.toLowerCase()) || courseName.toLowerCase().includes(opt.value.toLowerCase())) {
        opt.selected = true;
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
  const submitBtn = document.getElementById('modalEnqSubmitBtn') || form.querySelector('button[type="submit"]');
  const msgBox = document.getElementById('modalEnquiryMessage') || form.querySelector('.form-message');

  const name = (form.fullName?.value || '').trim();
  const mobile = (form.mobile?.value || '').trim();
  const email = (form.email?.value || '').trim();
  const course = (form.course?.value || '').trim();
  const branch = (form.branch?.value || 'Master Computer Institute — Nawabganj').trim();
  const batch = (form.batch?.value || 'Morning').trim();
  const message = (form.message?.value || '').trim();

  if (!name || name.length < 2) {
    showFormMsg(msgBox, 'Please enter your full name (at least 2 characters).', 'error');
    form.fullName?.focus();
    return;
  }

  const cleanMobile = mobile.replace(/[^0-9]/g, '');
  if (!/^[6-9]\d{9}$/.test(cleanMobile)) {
    showFormMsg(msgBox, 'Please enter a valid 10-digit mobile number.', 'error');
    form.mobile?.focus();
    return;
  }

  if (!course) {
    showFormMsg(msgBox, 'Please select a course to continue.', 'error');
    form.course?.focus();
    return;
  }

  // Prevent duplicate submissions
  const originalBtnContent = submitBtn ? submitBtn.innerHTML : 'Submit Enquiry';
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Submitting Enquiry...</span>';
  }
  if (msgBox) msgBox.style.display = 'none';

  const payload = {
    _subject: 'Course Enquiry (Modal) — Master Computer Institute',
    'Student Name': name,
    'Mobile Number': cleanMobile,
    'Email Address': email || 'Not Provided',
    'Selected Course': course,
    'Preferred Center': branch,
    'Preferred Batch': batch,
    'Message / Questions': message || 'None',
    'Submitted At': new Date().toLocaleString()
  };

  try {
    const res = await fetch(FORMSPREE_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      showFormMsg(msgBox, 'Thank you! Your course enquiry has been submitted successfully. Our admissions counselor will contact you shortly.', 'success');
      form.reset();
      setTimeout(() => {
        closeEnquireModal();
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnContent;
        }
      }, 3000);
    } else {
      let errData = {};
      try { errData = await res.json(); } catch (_) {}
      const errMsg = errData.errors?.map(e => e.message).join(', ') || 'Form submission failed. Please try again.';
      showFormMsg(msgBox, `${errMsg} Alternatively, contact us directly on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnContent;
      }
    }
  } catch (err) {
    showFormMsg(msgBox, `Network error submitting enquiry. Please check your internet connection or reach our helpline directly on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnContent;
    }
  }
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

  if (!name || !mobile || !email || !course || !address || !qualification) {
    if (msg) showFormMsg(msg, 'Please fill out all required fields marked with (*).', 'error');
    return;
  }

  const cleanMobile = mobile.replace(/[^0-9]/g, '');
  if (!/^[6-9]\d{9}$/.test(cleanMobile)) {
    if (msg) showFormMsg(msg, 'Please enter a valid 10-digit mobile number.', 'error');
    form.mobile?.focus();
    return;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    if (msg) showFormMsg(msg, 'Please enter a valid email address.', 'error');
    form.email?.focus();
    return;
  }

  // Prevent duplicate submissions
  const originalBtnContent = btn ? btn.innerHTML : 'Enquire Now';
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Submitting Application...';
  }
  if (msg) msg.style.display = 'none';

  const payload = {
    _subject: 'Online Admission Application — Master Computer Institute',
    'Applicant Name': name,
    'Date of Birth': dob || 'Not Provided',
    'Gender': gender || 'Not Specified',
    'Mobile Number': cleanMobile,
    'Email Address': email,
    'City': city || 'Nawabganj',
    'Residential Address': address,
    'Applied Course': course,
    'Training Center': branch || 'Master Computer Institute — Nawabganj',
    'Educational Qualification': qualification,
    'Preferred Batch': batch || 'Morning',
    'Learning Goals / Notes': message || 'None',
    'Submitted At': new Date().toLocaleString()
  };

  try {
    const res = await fetch(FORMSPREE_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      if (msg) showFormMsg(msg, 'Admission application received! Our admissions office will verify your details and contact you for enrollment.', 'success');
      form.reset();
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
      }
    } else {
      let errData = {};
      try { errData = await res.json(); } catch (_) {}
      const errMsg = errData.errors?.map(e => e.message).join(', ') || 'Application submission failed. Please try again.';
      if (msg) showFormMsg(msg, `${errMsg} Alternatively, reach out directly on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
      }
    }
  } catch (err) {
    if (msg) showFormMsg(msg, `Network error submitting application. Please check your internet connection or contact our office on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalBtnContent;
    }
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

  if (!name || name.length < 2) {
    if (msg) showFormMsg(msg, 'Please enter your name (at least 2 characters).', 'error');
    form.contactName?.focus();
    return;
  }

  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    if (msg) showFormMsg(msg, 'Please enter a valid email address.', 'error');
    form.contactEmail?.focus();
    return;
  }

  if (!message || message.length < 10) {
    if (msg) showFormMsg(msg, 'Please enter a message of at least 10 characters.', 'error');
    form.contactMessage?.focus();
    return;
  }

  // Prevent duplicate submissions
  const originalBtnContent = btn ? btn.innerHTML : 'Send Message';
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Sending Message...';
  }
  if (msg) msg.style.display = 'none';

  const cleanMobile = mobile.replace(/[^0-9]/g, '');

  const payload = {
    _subject: `Contact Inquiry: ${subject || 'New Message'} — Master Computer Institute`,
    'Sender Name': name,
    'Email Address': email,
    'Mobile Number': cleanMobile || 'Not Provided',
    'Inquiry Subject': subject || 'General Inquiry',
    'Message': message,
    'Submitted At': new Date().toLocaleString()
  };

  try {
    const res = await fetch(FORMSPREE_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      if (msg) showFormMsg(msg, 'Thank you! Your message has been sent successfully. Our team will get back to you shortly.', 'success');
      form.reset();
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
      }
    } else {
      let errData = {};
      try { errData = await res.json(); } catch (_) {}
      const errMsg = errData.errors?.map(e => e.message).join(', ') || 'Message delivery failed. Please try again.';
      if (msg) showFormMsg(msg, `${errMsg} Alternatively, message our team directly on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
      }
    }
  } catch (err) {
    if (msg) showFormMsg(msg, `Network error sending message. Please check your internet connection or reach our desk directly on WhatsApp at +91 ${MCI_WHATSAPP_NUMBER}.`, 'error');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalBtnContent;
    }
  }
}
