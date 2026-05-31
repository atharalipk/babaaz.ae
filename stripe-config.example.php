<?php
/**
 * Stripe Configuration File - EXAMPLE TEMPLATE
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to: stripe-config.php
 * 2. Replace all placeholder values with your actual Stripe keys
 * 3. Keep stripe-config.php secure and never commit it to public repositories
 */

// Stripe API Keys
// Get these from: https://dashboard.stripe.com/apikeys

// Test Mode Keys (for testing)
define('STRIPE_TEST_SECRET_KEY', 'your_test_secret_key_here');
define('STRIPE_TEST_PUBLISHABLE_KEY', 'your_test_publishable_key_here');
define('STRIPE_TEST_WEBHOOK_SECRET', 'your_test_webhook_secret_here');

// Live Mode Keys (for production)
define('STRIPE_LIVE_SECRET_KEY', 'your_live_secret_key_here');
define('STRIPE_LIVE_PUBLISHABLE_KEY', 'your_live_publishable_key_here');
define('STRIPE_LIVE_WEBHOOK_SECRET', 'your_live_webhook_secret_here');

// Set to true for test mode, false for live mode
define('STRIPE_TEST_MODE', true);

// Get active keys based on mode
define('STRIPE_SECRET_KEY', STRIPE_TEST_MODE ? STRIPE_TEST_SECRET_KEY : STRIPE_LIVE_SECRET_KEY);
define('STRIPE_PUBLISHABLE_KEY', STRIPE_TEST_MODE ? STRIPE_TEST_PUBLISHABLE_KEY : STRIPE_LIVE_PUBLISHABLE_KEY);
define('STRIPE_WEBHOOK_SECRET', STRIPE_TEST_MODE ? STRIPE_TEST_WEBHOOK_SECRET : STRIPE_LIVE_WEBHOOK_SECRET);

// Payment Configuration
define('PAYMENT_AMOUNT', 10); // Amount in AED
define('PAYMENT_CURRENCY', 'AED'); // Currency code
define('PAYMENT_DESCRIPTION', 'Loan Eligibility Check & Credit Score');

// Site URLs (automatically detected, but you can override if needed)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
define('SITE_BASE_URL', $protocol . '://' . $host . '/');

// Success and Cancel URLs
define('STRIPE_SUCCESS_URL', SITE_BASE_URL . 'stripe-success.php?session_id={CHECKOUT_SESSION_ID}');
define('STRIPE_CANCEL_URL', SITE_BASE_URL . 'stripe-cancel.php');

// Email Configuration
define('PAYMENT_FROM_EMAIL', 'noreply@yourdomain.com');
define('PAYMENT_REPLY_TO_EMAIL', 'info@yourdomain.com');
define('PAYMENT_FROM_NAME', 'Your Company Name');
?>
