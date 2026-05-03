<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Get form data and sanitize
$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$interest = isset($_POST['interest']) ? htmlspecialchars(trim($_POST['interest'])) : '';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($interest) || empty($message)) {
    http_response_code(400);
    die('All fields are required');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die('Invalid email address');
}

// Configure email
$to = 'info@operashalem.com'; // Your email address
$subject = 'New Contact Form Submission - Opera Shalem';

// Build email body
$email_body = "New contact form submission from operashalem.com\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Area of Interest: $interest\n";
$email_body .= "Message:\n$message\n\n";
$email_body .= "---\n";
$email_body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";

// Email headers
$headers = "From: no-reply@operashalem.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
if (mail($to, $subject, $email_body, $headers)) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Thank you! We will be in touch soon.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}
?>
