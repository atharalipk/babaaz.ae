<?php
session_start();
include_once('../wp-load.php');

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
		'added_date' => current_time('mysql'),
		'ip_address' => $_SERVER['REMOTE_ADDR'],
		'name' => $customerName,
		'email' => $customerEmail,
		'phone' => $customerPhone,
		'amount' => $amount,
		'currency' => $currency,
		'message' => $message,
		'payment_method' => 'stripe',
		'payment_status' => 'pending'
	),
	array('%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
);

if($insert){
	$insert_id = $wpdb->insert_id;
	
	// Store customer data in session for Stripe checkout
	$_SESSION['name'] = $customerName;
	$_SESSION['email'] = $customerEmail;
	$_SESSION['phone'] = $customerPhone;
	$_SESSION['payment_record_id'] = $insert_id; // Store DB record ID for later reference
	
	// Redirect to Stripe checkout
	header("Location: " . home_url('/stripe-checkout.php'));
	exit;
	
}else{
	$_SESSION['error'] = "Error! Please try again.";
	wp_redirect( wp_get_referer() );
	exit;
}
?>
