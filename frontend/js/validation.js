document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form[data-validate="true"]');

  forms.forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const message = form.querySelector('.form-message');
      const inputs = form.querySelectorAll('input, select, textarea');
      let valid = true;
      let firstErrorField = null;

      inputs.forEach((field) => {
        const value = field.value.trim();
        const name = field.name || field.id;
        const required = field.hasAttribute('required');
        const type = field.getAttribute('type');

        if (required && !value) {
          valid = false;
          field.setAttribute('aria-invalid', 'true');
          if (!firstErrorField) firstErrorField = field;
        } else if (type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          valid = false;
          field.setAttribute('aria-invalid', 'true');
          if (!firstErrorField) firstErrorField = field;
        } else if (name && /(mobile|phone)/i.test(name) && value && !/^[0-9+\s-]{10,15}$/.test(value)) {
          valid = false;
          field.setAttribute('aria-invalid', 'true');
          if (!firstErrorField) firstErrorField = field;
        } else {
          field.removeAttribute('aria-invalid');
        }
      });

      if (!valid) {
        if (message) {
          message.className = 'form-message error';
          message.textContent = 'Please correct the highlighted fields and try again.';
          message.style.display = 'block';
        }
        if (firstErrorField) firstErrorField.focus();
        return;
      }

      if (message) {
        message.className = 'form-message success';
        message.textContent = form.dataset.successMessage || 'Submitted successfully.';
        message.style.display = 'block';
      }

      form.reset();
    });
  });
});
