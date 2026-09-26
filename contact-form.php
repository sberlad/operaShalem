<?php
// Contact form handler for Opera Shalem
// Called via fetch() from form-handler.js (expects JSON), or as a plain
// form POST when JavaScript is unavailable (redirects back to the page).

ini_set('display_errors', 0);
error_reporting(E_ALL);

$wants_json = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

function respond($status, $success, $message) {
    global $wants_json;
    http_response_code($status);
    if ($wants_json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'message' => $message]);
    } else {
        header('Location: index.html?sent=' . ($success ? '1' : '0') . '#partner', true, 303);
    }
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed');
}

// Honeypot: real visitors never see or fill this field
if (!empty($_POST['website'])) {
    respond(200, true, 'Thank you! We will be in touch soon.');
}

// Plain-text email, so no HTML escaping; strip line breaks from single-line fields
function clean_line($value) {
    return trim(preg_replace('/[\r\n\t]+/', ' ', (string) $value));
}

$name = isset($_POST['name']) ? clean_line($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$interest = isset($_POST['interest']) ? clean_line($_POST['interest']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$allowed_interests = [
    'founding' => 'Founding Support',
    'connections' => 'Strategic Connections',
    'technology' => 'Technology Collaboration',
    'general' => 'General Inquiry',
];

// Validate required fields
if ($name === '' || $email === '' || $interest === '' || $message === '') {
    respond(400, false, 'Please fill in all fields.');
}

if (!isset($allowed_interests[$interest])) {
    respond(400, false, 'Please choose an area of interest.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, false, 'Please enter a valid email address.');
}

if (mb_strlen($name) > 200 || mb_strlen($message) > 5000) {
    respond(400, false, 'Your message is too long.');
}

// Configure email
$to = 'info@operashalem.com';
$subject = 'New Contact Form Submission - Opera Shalem';

// Build email body
$email_body = "New contact form submission from operashalem.com\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Area of Interest: {$allowed_interests[$interest]}\n";
$email_body .= "Message:\n$message\n\n";
$email_body .= "---\n";
$email_body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";

// Email headers
$headers = "From: no-reply@operashalem.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
if (mail($to, $subject, $email_body, $headers)) {
    respond(200, true, 'Thank you! We will be in touch soon.');
} else {
    error_log('Opera Shalem contact form: mail() failed');
    respond(500, false, 'Failed to send message. Please email us directly at info@operashalem.com');
}
