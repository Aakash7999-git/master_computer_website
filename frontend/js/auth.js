document.addEventListener('DOMContentLoaded', () => {
  const authKey = 'mciAuthSession';
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const defaultRole = 'student';

  const hashPassword = async (password) => {
    if (!window.crypto?.subtle) return null;
    const encodedPassword = new TextEncoder().encode(password);
    const digest = await window.crypto.subtle.digest('SHA-256', encodedPassword);
    return Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('');
  };

  const setMessage = (form, type, text) => {
    const message = form.querySelector('.form-message');
    if (!message) return;
    message.className = 'form-message ' + type;
    message.textContent = text;
  };

  const renderNavState = () => {
    const nav = document.getElementById('authNavActions');
    if (!nav) return;

    const storedUser = JSON.parse(localStorage.getItem(authKey) || 'null');
    if (storedUser && storedUser.authenticated) {
      nav.innerHTML = `
        <span class="welcome-badge">Welcome, ${storedUser.username}</span>
        <button type="button" class="btn btn-outline" data-auth="logout">Logout</button>
      `;

      const logoutBtn = nav.querySelector('[data-auth="logout"]');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
          localStorage.removeItem(authKey);
          window.location.href = 'index.html';
        });
      }
      return;
    }

    nav.innerHTML = `
      <a href="admission.html" class="btn btn-primary">Apply Now</a>
    `;
  };

  const validateField = (field) => {
    const value = (field.value || '').trim();
    const name = (field.name || field.id || '').toLowerCase();
    let isValid = true;

    if (field.hasAttribute('required') && !value) {
      isValid = false;
    } else if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      isValid = false;
    } else if (name.includes('mobile') && value && !/^[0-9+\s-]{10,15}$/.test(value)) {
      isValid = false;
    } else if (name.includes('password') && value && value.length < 6) {
      isValid = false;
    }

    field.setAttribute('aria-invalid', String(!isValid));
    const errorNode = field.closest('.field')?.querySelector('.inline-error');
    if (errorNode) {
      errorNode.classList.toggle('visible', !isValid);
      if (!isValid) {
        if (field.type === 'email') {
          errorNode.textContent = 'Please enter a valid email address.';
        } else if (name.includes('mobile')) {
          errorNode.textContent = 'Please enter a valid mobile number.';
        } else if (name.includes('password')) {
          errorNode.textContent = 'Password must be at least 6 characters.';
        } else {
          errorNode.textContent = 'This field is required.';
        }
      }
    }
    return isValid;
  };

  if (currentPage === 'index.html') {
    renderNavState();
    return;
  }

  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const roleButtons = document.querySelectorAll('.role-option');
  const authModeSwitch = document.querySelector('[data-auth-mode="register"]');

  const updateTeacherFields = (role) => {
    const submitButton = registerForm?.querySelector('.auth-btn');

    if (submitButton) {
      submitButton.textContent = 'Register';
    }
  };

  const switchMode = (mode) => {
    if (loginForm) loginForm.classList.toggle('active', mode === 'login');
    if (registerForm) registerForm.classList.toggle('active', mode === 'register');

    const switchText = document.getElementById('switchText');
    if (switchText) {
      switchText.textContent = mode === 'login' ? "Don't have an account?" : 'Already have an account?';
    }

    const trigger = document.querySelector('[data-auth-mode="register"]');
    if (trigger) {
      trigger.textContent = mode === 'login' ? 'Register Now' : 'Login';
      trigger.setAttribute('data-auth-mode', mode === 'login' ? 'register' : 'login');
    }
  };

  roleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const role = button.dataset.role;
      roleButtons.forEach((option) => {
        const isActive = option.dataset.role === role;
        option.classList.toggle('active', isActive);
        option.setAttribute('aria-pressed', String(isActive));
      });
      updateTeacherFields(role);
    });
  });

  const passwordToggles = document.querySelectorAll('.password-toggle');
  passwordToggles.forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.closest('.password-wrap')?.querySelector('input');
      if (!input) return;
      const nextType = input.type === 'password' ? 'text' : 'password';
      input.type = nextType;
      button.textContent = nextType === 'password' ? '👁' : '🙈';
    });
  });

  if (authModeSwitch) {
    authModeSwitch.addEventListener('click', () => {
      const nextMode = authModeSwitch.getAttribute('data-auth-mode') === 'register' ? 'register' : 'login';
      switchMode(nextMode);
    });
  }

  if (loginForm) {
    loginForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const username = loginForm.querySelector('#loginUsername');
      const password = loginForm.querySelector('#loginPassword');
      const selectedRole = document.querySelector('.role-option.active')?.dataset.role || defaultRole;
      let valid = true;

      if (!validateField(username)) valid = false;
      if (!validateField(password)) valid = false;

      if (!valid) {
        setMessage(loginForm, 'error', 'Please enter your username and password.');
        return;
      }

      const existingSession = JSON.parse(localStorage.getItem(authKey) || 'null');
      const passwordHash = await hashPassword(password.value);
      const matchedUser = existingSession && passwordHash && existingSession.role === selectedRole && existingSession.username === username.value.trim() && existingSession.passwordHash === passwordHash;

      if (!matchedUser) {
        setMessage(loginForm, 'error', 'Invalid username or password.');
        return;
      }

      const nextSession = {
        authenticated: true,
        userId: existingSession.userId || Date.now(),
        username: existingSession.username,
        role: existingSession.role,
        fullName: existingSession.fullName || existingSession.username
      };

      localStorage.setItem(authKey, JSON.stringify(nextSession));
      loginForm.querySelector('button[type="submit"]').disabled = true;
      setMessage(loginForm, 'success', 'Login successful. Redirecting to the website...');
      setTimeout(() => {
        window.location.href = 'index.html';
      }, 700);
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const role = document.querySelector('.role-option.active')?.dataset.role || defaultRole;
      const fields = registerForm.querySelectorAll('input, select');
      let valid = true;

      fields.forEach((field) => {
        if (field.id === 'qualification' || field.id === 'specialization') {
          if (role === 'teacher' && !field.value.trim()) {
            validateField(field);
            valid = false;
          }
          return;
        }

        if (!validateField(field)) {
          valid = false;
        }
      });

      if (!valid) {
        setMessage(registerForm, 'error', 'Please correct the highlighted fields and try again.');
        return;
      }

      const storedUser = JSON.parse(localStorage.getItem(authKey) || 'null');
      const username = registerForm.querySelector('#registerUsername').value.trim();
      const existingUser = storedUser && storedUser.username === username && storedUser.role === role;
      if (existingUser) {
        setMessage(registerForm, 'error', 'This username is already registered.');
        return;
      }

      const password = registerForm.querySelector('#registerPassword');
      const passwordHash = await hashPassword(password.value);
      if (!passwordHash) {
        setMessage(registerForm, 'error', 'Secure password storage is unavailable in this browser.');
        return;
      }

      const newUser = {
        authenticated: false,
        userId: `${role}-${Date.now()}`,
        username,
        role,
        fullName: username,
        email: registerForm.querySelector('#email').value.trim(),
        mobile: '',
        passwordHash
      };

      localStorage.setItem(authKey, JSON.stringify(newUser));
      registerForm.querySelector('button[type="submit"]').disabled = true;
      setMessage(registerForm, 'success', 'Account created successfully. Please login to continue.');

      setTimeout(() => {
        switchMode('login');
        registerForm.reset();
        const submit = registerForm.querySelector('button[type="submit"]');
        if (submit) submit.disabled = false;
      }, 1200);
    });
  }

  switchMode('login');
  updateTeacherFields('student');
  const storedUser = JSON.parse(localStorage.getItem(authKey) || 'null');
  if (storedUser && storedUser.authenticated) {
    window.location.href = 'index.html';
  }
});
