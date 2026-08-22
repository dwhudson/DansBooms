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

    // TODO: Replace with a real submission once a backend endpoint exists.
    // The endpoint should verify `recaptchaResponse` server-side with your
    // reCAPTCHA secret key before sending the email.
    console.log('Contact form submitted (demo only, no backend wired up yet):', {
      name: form.name.value,
      email: form.email.value,
      subject: form.subject.value,
      message: form.message.value,
      recaptchaResponse,
    });

    formStatus.textContent = 'Thanks! This form is not yet connected to a backend, so no message was actually sent.';
    formStatus.classList.remove('d-none', 'alert-danger');
    formStatus.classList.add('alert', 'alert-info');

    form.reset();
    form.classList.remove('was-validated');
    if (typeof grecaptcha !== 'undefined') {
      grecaptcha.reset();
    }
  });
});
