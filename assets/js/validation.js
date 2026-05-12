/**
 * QNU SMS - Form Validation JS
 */

(function () {
  'use strict';

  // ── Validate email ───────────────────────────────────────────
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  // ── Validate phone (VN) ──────────────────────────────────────
  function isValidPhone(phone) {
    return /^(0|\+84)[0-9]{9,10}$/.test(phone.trim());
  }

  // ── Show / hide error ────────────────────────────────────────
  function showError(input, msg) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    let err = input.parentElement.querySelector('.form-error');
    if (!err) {
      err = document.createElement('span');
      err.className = 'form-error';
      input.parentElement.appendChild(err);
    }
    err.textContent = msg;
    err.style.display = 'block';
  }

  function clearError(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    const err = input.parentElement.querySelector('.form-error');
    if (err) err.style.display = 'none';
  }

  // ── Validate single field ────────────────────────────────────
  function validateField(input) {
    const val = input.value.trim();
    const type = input.dataset.validate;

    if (!type) return true;

    if (type.includes('required') && val === '') {
      showError(input, 'Trường này là bắt buộc.');
      return false;
    }
    if (type.includes('email') && val && !isValidEmail(val)) {
      showError(input, 'Địa chỉ email không hợp lệ.');
      return false;
    }
    if (type.includes('phone') && val && !isValidPhone(val)) {
      showError(input, 'Số điện thoại không hợp lệ (VD: 0912345678).');
      return false;
    }
    if (type.includes('minlen')) {
      const min = parseInt(input.dataset.minlen || 6);
      if (val.length < min) {
        showError(input, `Tối thiểu ${min} ký tự.`);
        return false;
      }
    }

    clearError(input);
    return true;
  }

  // ── Attach live validation ────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-validate]').forEach(input => {
      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('is-invalid')) validateField(input);
      });
    });

    // Validate on form submit
    document.querySelectorAll('form[data-validate-form]').forEach(form => {
      form.addEventListener('submit', function (e) {
        let valid = true;
        form.querySelectorAll('[data-validate]').forEach(input => {
          if (!validateField(input)) valid = false;
        });
        if (!valid) {
          e.preventDefault();
          // Scroll tới lỗi đầu tiên
          const firstErr = form.querySelector('.is-invalid');
          if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    });
  });

})();
