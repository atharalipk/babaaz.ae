<?php
/**
 * Stripe Webhook Handler
 * This file handles webhook events from Stripe for payment verification
 * 
 * Setup Instructions:
 * 1. Add this webhook URL in your Stripe Dashboard: https://yourdomain.com/stripe-webhook.php
 * 2. Select the following events: checkout.session.completed, payment_intent.succeeded, payment_intent.payment_failed
 * 3. Copy the webhook signing secret and add it to this file
 */

// Don't start session for webhooks
// Load WordPress
require_once(__DIR__ . '/wp-load.php');

// Include Stripe Configuration
require_once('stripe-config.php');

// Require Stripe PHP Library via Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/stripe-php/init.php')) {
    require_once(__DIR__ . '/stripe-php/init.php');
} else {
    http_response_code(500);
    die('Error: Stripe PHP library not found');
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

global $wpdb;

// Retrieve the request's body and parse it as JSON
$payload = @file_get_contents('php://input');
$sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
$event = null;

// Log webhook for debugging (optional)
$log_file = 'stripe-webhook-log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Webhook received\n", FILE_APPEND);

try {
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, STRIPE_WEBHOOK_SECRET
    );
    
    file_put_contents($log_file, "Event type: " . $event->type . "\n", FILE_APPEND);
    
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    file_put_contents($log_file, "Invalid payload: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    file_put_contents($log_file, "Invalid signature: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Payment is successful and the subscription is created
        if ($session->payment_status == 'paid') {
            $customerName = isset($session->metadata->customer_name) ? $session->metadata->customer_name : '';
            $customerEmail = $session->customer_email;
            $customerPhone = isset($session->metadata->customer_phone) ? $session->metadata->customer_phone : '';
            $amountPaid = $session->amount_total / 100; // Convert from fils to AED
            $currency = strtoupper($session->currency);
            $paymentId = $session->payment_intent;
            $sessionId = $session->id;
            $recordId = isset($session->metadata->payment_record_id) ? $session->metadata->payment_record_id : null;
            
            // Check if we have the payment record ID from metadata
            $existing = null;
            if ($recordId) {
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}payments WHERE id = %d",
                    $recordId
                ));
            }
            
            // If not found by record ID, check by payment_id
            if (!$existing) {
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}payments WHERE payment_id = %s",
                    $paymentId
                ));
            }
            
            // Also check for pending payment by email (from form submission)
            if (!$existing) {
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}payments WHERE customer_email = %s AND payment_status = 'pending' ORDER BY created_at DESC LIMIT 1",
                    $customerEmail
                ));
            }
            
            if ($existing) {
                // Update existing payment record
                $wpdb->update(
                    $wpdb->prefix . 'payments',
                    array(
                        'payment_id' => $paymentId,
                        'session_id' => $sessionId,
                        'payment_status' => 'completed',
                        'webhook_received' => 1
                    ),
                    array('id' => $existing),
                    array('%s', '%s', '%s', '%d'),
                    array('%d')
                );
                
                file_put_contents($log_file, "Payment updated: " . $paymentId . " (Record ID: " . $existing . ")\n", FILE_APPEND);
            } else {
                // Payment doesn't exist, insert it
                $wpdb->insert(
                    $wpdb->prefix . 'payments',
                    array(
                        'customer_name' => $customerName,
                        'customer_email' => $customerEmail,
                        'customer_phone' => $customerPhone,
                        'amount' => $amountPaid,
                        'currency' => $currency,
                        'payment_id' => $paymentId,
                        'session_id' => $sessionId,
                        'payment_status' => 'completed',
                        'webhook_received' => 1,
                        'created_at' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%s')
                );
                
                file_put_contents($log_file, "Payment recorded: " . $paymentId . "\n", FILE_APPEND);
            }
            
            // Send confirmation email using WordPress wp_mail
            $to = $customerEmail;
            $subject = "Payment Confirmation - Loan Eligibility Check";
            $message = "Dear " . $customerName . ",\n\n";
            $message .= "Thank you for your payment of AED " . number_format($amountPaid, 2) . ".\n\n";
            $message .= "Your loan eligibility check is being processed and results will be sent to you within 24-48 hours.\n\n";
            $message .= "Transaction ID: " . $paymentId . "\n\n";
            $message .= "Best regards,\n";
            $message .= "Emirates Loan Team";
            
            $headers = array(
                'From: ' . PAYMENT_FROM_NAME . ' <' . PAYMENT_FROM_EMAIL . '>',
                'Reply-To: ' . PAYMENT_REPLY_TO_EMAIL
            );
            
            wp_mail($to, $subject, $message, $headers);
            
            file_put_contents($log_file, "Confirmation email sent to: " . $customerEmail . "\n", FILE_APPEND);
        }
        break;
        
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        file_put_contents($log_file, "Payment succeeded: " . $paymentIntent->id . "\n", FILE_APPEND);
        
        // Update payment status if needed
        $wpdb->update(
            $wpdb->prefix . 'payments',
            array('payment_status' => 'completed'),
            array('payment_id' => $paymentIntent->id),
            array('%s'),
            array('%s')
        );
        break;
        
    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object;
        file_put_contents($log_file, "Payment failed: " . $paymentIntent->id . "\n", FILE_APPEND);
        
        // Update payment status
        $wpdb->update(
            $wpdb->prefix . 'payments',
            array('payment_status' => 'failed'),
            array('payment_id' => $paymentIntent->id),
            array('%s'),
            array('%s')
        );
        break;
        
    default:
        file_put_contents($log_file, "Unhandled event type: " . $event->type . "\n", FILE_APPEND);
}

file_put_contents($log_file, "Webhook processed successfully\n\n", FILE_APPEND);

http_response_code(200);
?>
