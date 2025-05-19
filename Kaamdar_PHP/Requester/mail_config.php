<?php
// Require Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file for mail errors
$logFile = __DIR__ . '/mail_errors.log';

// Mail configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'kaamdarservices@gmail.com');
define('SMTP_PASSWORD', 'tzij wmxt rdja jauu'); // Updated app password
define('SMTP_FROM_EMAIL', 'kaamdarservices@gmail.com');
define('SMTP_FROM_NAME', 'Kaamdar');

/**
 * Log error message to file
 */
function logError($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML supported)
 * @param string $toName Recipient name (optional)
 * @return bool True if email was sent successfully, false otherwise
 */
function sendMail($to, $subject, $body, $toName = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Debug output
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->Debugoutput = function($str, $level) {
            logError("SMTP Debug: $str");
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Additional settings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Debug log
        logError("Attempting to send email to: " . $to);
        logError("SMTP Configuration: Host=" . SMTP_HOST . ", Port=" . SMTP_PORT . ", Username=" . SMTP_USERNAME);
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        // Send email
        $result = $mail->send();
        logError("Email sent successfully to: " . $to);
        return true;
    } catch (Exception $e) {
        logError("Mail Error: " . $mail->ErrorInfo);
        logError("Detailed error: " . $e->getMessage());
        logError("Stack trace: " . $e->getTraceAsString());
        logError("SMTP Debug Output: " . print_r($mail->ErrorInfo, true));
        return false;
    }
}

/**
 * Send order confirmation email
 * 
 * @param string $to Recipient email address
 * @param string $orderId Order ID
 * @param array $orderDetails Order details
 * @return bool True if email was sent successfully, false otherwise
 */
function sendOrderConfirmation($to, $orderId, $orderDetails) {
    $subject = "Order Confirmation - Order #$orderId";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #f3961c 0%, #e67e22 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
            }
            .content {
                padding: 30px;
            }
            .order-info {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 25px;
            }
            .order-info h2 {
                color: #f3961c;
                margin-top: 0;
                font-size: 20px;
                margin-bottom: 15px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .info-item {
                margin-bottom: 10px;
            }
            .info-label {
                font-weight: 600;
                color: #666;
                font-size: 14px;
                margin-bottom: 5px;
            }
            .info-value {
                color: #333;
                font-size: 16px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin: 25px 0;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .items-table th {
                background: #f3961c;
                color: white;
                padding: 15px;
                text-align: left;
                font-weight: 500;
            }
            .items-table td {
                padding: 15px;
                border-bottom: 1px solid #eee;
            }
            .items-table tr:last-child td {
                border-bottom: none;
            }
            .total-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-top: 25px;
                text-align: right;
            }
            .total-amount {
                font-size: 24px;
                color: #f3961c;
                font-weight: 600;
            }
            .footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                font-size: 13px;
                color: #666;
                border-top: 1px solid #eee;
            }
            .logo {
                max-width: 150px;
                margin-bottom: 15px;
            }
            .button {
                display: inline-block;
                padding: 12px 25px;
                background: #f3961c;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
                font-weight: 500;
            }
            .button:hover {
                background: #e67e22;
            }
            @media only screen and (max-width: 600px) {
                .container {
                    margin: 10px;
                }
                .info-grid {
                    grid-template-columns: 1fr;
                }
                .items-table {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Confirmation</h1>
                <p>Thank you for choosing Kaamdar!</p>
            </div>
            
            <div class='content'>
                <div class='order-info'>
                    <h2>Order Details</h2>
                    <div class='info-grid'>
                        <div class='info-item'>
                            <div class='info-label'>Order ID</div>
                            <div class='info-value'>#$orderId</div>
                        </div>
                        <div class='info-item'>
                            <div class='info-label'>Order Date</div>
                            <div class='info-value'>" . date('F j, Y, g:i a') . "</div>
                        </div>
                        <div class='info-item'>
                            <div class='info-label'>Payment Method</div>
                            <div class='info-value'>" . $orderDetails['payment_method'] . "</div>
                        </div>
                        <div class='info-item'>
                            <div class='info-label'>Order Status</div>
                            <div class='info-value'>" . $orderDetails['order_status'] . "</div>
                        </div>
                    </div>
                </div>

                <h2 style='color: #333; margin-top: 30px;'>Order Items</h2>
                <table class='items-table'>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    foreach ($orderDetails['items'] as $item) {
        $body .= "
                        <tr>
                            <td>" . htmlspecialchars($item['pname']) . "</td>
                            <td>" . $item['quantity'] . "</td>
                            <td>NPR " . number_format($item['price'], 2) . "</td>
                            <td>NPR " . number_format($item['quantity'] * $item['price'], 2) . "</td>
                        </tr>";
    }
    
    $body .= "
                    </tbody>
                </table>

                <div class='total-section'>
                    <div class='info-label'>Total Amount</div>
                    <div class='total-amount'>NPR " . number_format($orderDetails['total_amount'], 2) . "</div>
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                    <p>If you have any questions about your order, please contact us.</p>
                    <a href='mailto:support@kaamdar.com' class='button'>Contact Support</a>
                </div>
            </div>

            <div class='footer'>
                <p>This is an automated email, please do not reply.</p>
                <p>&copy; " . date('Y') . " Kaamdar. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendMail($to, $subject, $body);
}
?> 