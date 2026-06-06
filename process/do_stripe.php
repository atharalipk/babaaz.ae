<?php
session_start();
include_once('../wp-load.php');

// Include Stripe Configuration
require_once('../stripe-config.php');

// Require Stripe PHP Library via Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/../stripe-php/init.php')) {
    require_once(__DIR__ . '/../stripe-php/init.php');
} else {
    $_SESSION['error'] = "Stripe library not found. Please contact administrator.";
    wp_redirect( wp_get_referer() );
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if our nonce is set.
if ( ! isset( $_POST['athar_stripe_nonce'] ) ) {
	$_SESSION['error'] = "Invalid request.";
	wp_redirect( wp_get_referer() );
	exit;
}

// Verify that the nonce is valid.
if ( ! wp_verify_nonce( $_POST['athar_stripe_nonce'], 'athar_stripe' ) ) {
	$_SESSION['error'] = "Security verification failed.";
	wp_redirect( wp_get_referer() );
	exit;
}

// Validate required fields
if(empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['amount'])){
	$_SESSION['error'] = "Please complete all required fields.";
	wp_redirect( wp_get_referer() );
	exit;
}

// Validate email format
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	$_SESSION['error'] = "Invalid email address.";
	wp_redirect( wp_get_referer() );
	exit;
}

// Get customer data
$customerName = sanitize_text_field($_POST['name']);
$customerEmail = sanitize_email($_POST['email']);
$customerPhone = sanitize_text_field($_POST['phone']);
$amount = floatval($_POST['amount']);
$currency = sanitize_text_field($_POST['currency_code']);
$message = sanitize_textarea_field($_POST['message']);

// Insert payment record into database
global $wpdb;
$insert = $wpdb->insert(
	$wpdb->prefix . 'payments',
	array(
		'customer_name' => $customerName,
		'customer_email' => $customerEmail,
		'customer_phone' => $customerPhone,
		'amount' => $amount,
		'currency' => $currency,
		'payment_id' => 'pending_' . uniqid('', true),
		'payment_status' => 'pending',
		'created_at' => current_time('mysql'),
	),
	array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
);

if($insert){
	$insert_id = $wpdb->insert_id;
	
	// Store payment record ID in session for later reference
	$_SESSION['payment_record_id'] = $insert_id;
	
	// Create Stripe Checkout Session
	try {
		$checkout_session = \Stripe\Checkout\Session::create([
			'payment_method_types' => ['card'],
			'line_items' => [[
				'price_data' => [
					'currency' => strtolower($currency),
					'product_data' => [
						'name' => 'Loan Eligibility Check & Credit Score',
						'description' => !empty($message) ? $message : 'Payment for loan eligibility check',
					],
					'unit_amount' => $amount * 100, // Convert to smallest currency unit (fils for AED)
				],
				'quantity' => 1,
			]],
			'mode' => 'payment',
			'success_url' => home_url('/stripe-success.php?session_id={CHECKOUT_SESSION_ID}'),
			'cancel_url' => home_url('/stripe-cancel.php'),
			'customer_email' => $customerEmail,
			'metadata' => [
				'customer_name' => $customerName,
				'customer_phone' => $customerPhone,
				'customer_email' => $customerEmail,
				'payment_record_id' => $insert_id,
			],
		]);
		
		// Redirect to Stripe hosted checkout page
		header("Location: " . $checkout_session->url);
		exit;
		
	} catch (Exception $e) {
		$_SESSION['error'] = "Error creating payment session: " . $e->getMessage();
		wp_redirect( wp_get_referer() );
		exit;
	}
	
}else{
	$_SESSION['error'] = "Error! Please try again.";
	wp_redirect( wp_get_referer() );
	exit;
}
?>
