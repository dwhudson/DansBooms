<?php
// Copy this file to contact-config.php on the server (same directory)
// and fill in real values. contact-config.php is gitignored — never
// commit real secrets to this repo.

define('RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');
define('CONTACT_TO_EMAIL', 'you@example.com');

// Real mailbox created under cPanel > Email > Email Accounts.
// contact.php sends through this via authenticated SMTP (PHPMailer).
define('SMTP_USERNAME', 'no-reply@dansbooms.com');
define('SMTP_PASSWORD', 'YOUR_MAILBOX_PASSWORD');

// Usually "localhost" works when the mailbox lives on the same cPanel
// account as the site. Port 465 = implicit TLS (SMTPS), 587 = STARTTLS.
// define('SMTP_HOST', 'localhost');
// define('SMTP_PORT', 465);
// define('SMTP_SECURE', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS);
