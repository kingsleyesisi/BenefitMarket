<?php
// mailer.php

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer library files (adjust the paths as needed)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/**
 * Sends a trade confirmation email to the specified recipient.
 *
 * @param string $toEmail   Recipient's email address.
 * @param string $toName    Recipient's name.
 * @param int    $tradeId   The unique trade ID.
 * @param string $tradeDetails  Details of the trade (e.g. asset, amount, entry price).
 * @return bool  Returns true on success, false on failure.
 */
function sendTradeConfirmationEmail($toEmail, $toName, $tradeId, $tradeDetails) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                            // Use SMTP
        $mail->Host       = 'smtp.example.com';                     // Set your SMTP server
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'your_username@example.com';            // SMTP username
        $mail->Password   = 'your_password';                        // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; use PHPMailer::ENCRYPTION_SMTPS for SSL
        $mail->Port       = 587;                                    // TCP port to connect to

        // Recipients
        $mail->setFrom('support@benefitsmart.xyz', 'Benefit Market Trade');
        $mail->addAddress($toEmail, $toName);                       // Add a recipient

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = "Trade Confirmation - Trade ID: $tradeId";
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>Trade Confirmation</title>
          <style>
            /* Minimal inline styles to mimic Tailwind CSS for email */
            .bg-gray-100 { background-color: #f7fafc; }
            .bg-white { background-color: #ffffff; }
            .text-gray-800 { color: #2d3748; }
            .text-gray-700 { color: #4a5568; }
            .text-gray-500 { color: #a0aec0; }
            .rounded-lg { border-radius: 0.5rem; }
            .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
            .max-w-xl { max-width: 36rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .p-6 { padding: 1.5rem; }
            .mb-4 { margin-bottom: 1rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mt-6 { margin-top: 1.5rem; }
            .mt-4 { margin-top: 1rem; }
            .text-2xl { font-size: 1.5rem; }
            .font-bold { font-weight: 700; }
            .w-full { width: 100%; }
            .border { border: 1px solid #e2e8f0; }
            .border-b { border-bottom: 1px solid #e2e8f0; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            table { border-collapse: collapse; }
            th { text-align: left; }
          </style>
        </head>
        <body class='bg-gray-100 p-6'>
          <div class='max-w-xl mx-auto bg-white rounded-lg shadow-lg p-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4'>Trade Confirmation</h1>
            <p class='text-gray-700 mb-2'>Dear $toName,</p>
            <p class='text-gray-700 mb-4'>Your trade has been successfully placed. Please review the details below:</p>
            
            <table class='w-full mb-4'>
              <tr>
                 <th class='py-2 px-4 border-b'>Trade ID</th>
                 <td class='py-2 px-4 border-b'>$tradeId</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Trade Category</th>
                 <td class='py-2 px-4 border-b'>$trade_category</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Trade Type</th>
                 <td class='py-2 px-4 border-b'>$trade_type</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Asset</th>
                 <td class='py-2 px-4 border-b'>$asset</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Lot Size</th>
                 <td class='py-2 px-4 border-b'>$lot_size</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Entry Price</th>
                 <td class='py-2 px-4 border-b'>$entry_price</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Amount (USD)</th>
                 <td class='py-2 px-4 border-b'>$amount</td>
              </tr>
              <tr>
                 <th class='py-2 px-4 border-b'>Trade Date</th>
                 <td class='py-2 px-4 border-b'>$trade_date</td>
              </tr>
            </table>
            
            <p class='text-gray-700 mb-4'>Please note that your USD balance has been updated accordingly.</p>
            <p class='text-gray-700'>If you have any questions, feel free to contact our support team.</p>
            <p class='mt-6 text-gray-700'>Thank you for trading with Benefit Market Trade!</p>
            <p class='mt-4 text-gray-500 text-sm'>This is an automated email. Please do not reply directly.</p>
          </div>
        </body>
        </html>
        ";
        $mail->AltBody = "Trade Confirmation: Your trade (Trade ID: $tradeId) has been successfully placed. Details: Category: $trade_category, Type: $trade_type, Asset: $asset, Lot Size: $lot_size, Entry Price: $entry_price, Amount: $amount USD, Trade Date: $trade_date. Thank you for trading with Benefit Market Trade!";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Trade Confirmation Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
