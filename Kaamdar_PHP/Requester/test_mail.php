<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'mail_config.php';

// Test email configuration
$to = 'kaamdarservices@gmail.com';
$subject = 'Test Email from Kaamdar';
$body = '<h1>Test Email</h1><p>This is a test email sent using PHPMailer via Composer.</p>';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $result = sendMail($to, $subject, $body);
    if ($result) {
        echo '<h3 style="color:green">Test email sent successfully!</h3>';
    } else {
        echo '<h3 style="color:red">Failed to send test email.</h3>';
        echo '<p>Check the mail_errors.log file for details.</p>';
    }
} catch (Exception $e) {
    echo '<h3 style="color:red">Error sending test email:</h3>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
} 