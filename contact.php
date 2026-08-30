<?php
declare(strict_types=1);

header('Content-Type: application/json');

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

$to = CONTACT_TO_EMAIL;
$mailSubject = "Dan's Booms contact form: " . ($subject !== '' ? $subject : 'New message');
$body = "Name: $name\nEmail: $email\nSubject: $subject\n\n$message\n";
$sendingDomain = defined('CONTACT_SENDING_DOMAIN') ? CONTACT_SENDING_DOMAIN : 'dansbooms.com';
$fromAddress = "no-reply@$sendingDomain";
$headers = "From: $fromAddress\r\n" .
    "Reply-To: $name <$email>\r\n" .
    "Content-Type: text/plain; charset=UTF-8";

if (!mail($to, $mailSubject, $body, $headers, "-f$fromAddress")) {
    fail('Could not send message. Please try again later.', 500);
}

echo json_encode(['success' => true, 'message' => 'Thanks! Your message has been sent.']);
