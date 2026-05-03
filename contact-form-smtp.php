<?php
// SMTP Contact Form Handler for Opera Shalem
// Uses PHPMailer for reliable email delivery

// Enable error reporting for debugging (disable in production)
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
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if using Composer)
// require 'vendor/autoload.php';

// OR load PHPMailer manually (download from https://github.com/PHPMailer/PHPMailer)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.ionos.com'; // IONOS SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contactform@operashalem.com'; // Your IONOS email
    $mail->Password   = 'YOUR_EMAIL_PASSWORD_HERE';    // Your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Email settings
    $mail->setFrom('contactform@operashalem.com', 'Opera Shalem Website');
    $mail->addAddress('info@operashalem.com', 'Opera Shalem');
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(false);
    $mail->Subject = 'New Contact Form Submission - Opera Shalem';
    
    $mail->Body = "New contact form submission from operashalem.com\n\n";
    $mail->Body .= "Name: $name\n";
    $mail->Body .= "Email: $email\n";
    $mail->Body .= "Area of Interest: $interest\n";
    $mail->Body .= "Message:\n$message\n\n";
    $mail->Body .= "---\n";
    $mail->Body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";

    // Send email
    $mail->send();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Thank you! We will be in touch soon.']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
    
    // Log the error (don't show to user)
    error_log("Mailer Error: {$mail->ErrorInfo}");
}
?>
