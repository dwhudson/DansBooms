document.addEventListener('DOMContentLoaded', () => {
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  const form = document.getElementById('contactForm');
  if (!form) return;

  const recaptchaFeedback = document.getElementById('recaptchaFeedback');
  const formStatus = document.getElementById('formStatus');

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    event.stopPropagation();

    let isValid = form.checkValidity();
    form.classList.add('was-validated');

    // grecaptcha is provided by the reCAPTCHA script (https://www.google.com/recaptcha/api.js)
    const recaptchaResponse = typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : '';
    const recaptchaValid = recaptchaResponse.length > 0;
    recaptchaFeedback.classList.toggle('d-none', recaptchaValid);

    if (!isValid || !recaptchaValid) {
      return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    const formData = new FormData(form);
    formData.set('g-recaptcha-response', recaptchaResponse);

    fetch('contact.php', { method: 'POST', body: formData })
      .then((response) => response.json().catch(() => ({
        success: false,
        message: 'Unexpected response from the server.',
      })))
      .then((data) => {
        formStatus.textContent = data.message
          || (data.success ? 'Thanks! Your message has been sent.' : 'Something went wrong. Please try again.');
        formStatus.classList.remove('d-none', 'alert-danger', 'alert-info');
        formStatus.classList.add('alert', data.success ? 'alert-info' : 'alert-danger');

        if (data.success) {
          form.reset();
          form.classList.remove('was-validated');
        }
        if (typeof grecaptcha !== 'undefined') {
          grecaptcha.reset();
        }
      })
      .catch(() => {
        formStatus.textContent = 'Could not reach the server. Please try again later.';
        formStatus.classList.remove('d-none', 'alert-info');
        formStatus.classList.add('alert', 'alert-danger');
      })
      .finally(() => {
        submitButton.disabled = false;
      });
  });
});
