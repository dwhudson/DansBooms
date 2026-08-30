<?php
declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/lib/PHPMailer/Exception.php';
require __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$config = __DIR__ . '/contact-config.php';
if (!file_exists($config)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server is not configured yet.']);
    exit;
}
require $config;

function fail(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed.', 405);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    fail('Please fill in all required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}
foreach ([$name, $email, $subject] as $field) {
    if (preg_match('/[\r\n]/', $field)) {
        fail('Invalid characters in submission.');
    }
}
if ($recaptchaResponse === '') {
    fail("Please confirm you're not a robot.");
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
    'secret' => RECAPTCHA_SECRET_KEY,
    'response' => $recaptchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]);
$verifyResult = json_decode((string) @file_get_contents($verifyUrl), true);
if (!is_array($verifyResult) || empty($verifyResult['success'])) {
    fail('reCAPTCHA verification failed. Please try again.');
}

if (!defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD') || SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
    fail('Email service is not configured yet.', 500);
}

$to = CONTACT_TO_EMAIL;
$mailSubject = "Dan's Booms contact form: " . ($subject !== '' ? $subject : 'New message');
$body = "Name: $name\nEmail: $email\nSubject: $subject\n\n$message\n";
$smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
$smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 465;
$smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_SMTPS;

$mailer = new PHPMailer(true);
try {
    $mailer->isSMTP();
    $mailer->Host = $smtpHost;
    $mailer->Port = $smtpPort;
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = $smtpSecure;
    $mailer->Username = SMTP_USERNAME;
    $mailer->Password = SMTP_PASSWORD;

    $mailer->setFrom(SMTP_USERNAME, "Dan's Booms");
    $mailer->addAddress($to);
    $mailer->addReplyTo($email, $name);
    $mailer->Subject = $mailSubject;
    $mailer->Body = $body;

    $mailer->send();
} catch (PHPMailerException $e) {
    error_log('Contact form mail error: ' . $mailer->ErrorInfo);
    fail('Could not send message. Please try again later.', 500);
}

echo json_encode(['success' => true, 'message' => 'Thanks! Your message has been sent.']);
