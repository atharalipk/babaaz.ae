<?php
session_start();

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

// Get site URL
$site_url = home_url('/');

// Include Stripe Configuration
require_once('stripe-config.php');

// Require Stripe PHP Library via Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/stripe-php/init.php')) {
    require_once(__DIR__ . '/stripe-php/init.php');
} else {
    die('Error: Stripe PHP library not found. Please install it first.<br><br>Run: <code>composer require stripe/stripe-php</code><br>Or download from: <a href="https://github.com/stripe/stripe-php/releases">https://github.com/stripe/stripe-php/releases</a>');
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

global $wpdb;

// Get the session ID from URL
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : '';

$paymentSuccess = false;
$customerName = '';
$customerEmail = '';
$amountPaid = '';
$paymentId = '';

if (!empty($session_id)) {
    try {
        // Retrieve the session from Stripe
        $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
        
        if ($checkout_session->payment_status == 'paid') {
            $paymentSuccess = true;
            $customerName = $checkout_session->metadata->customer_name;
            $customerEmail = $checkout_session->customer_email;
            $amountPaid = number_format($checkout_session->amount_total / 100, 2); // Convert from fils to AED
            $paymentId = $checkout_session->payment_intent;
            
            // Check if we have a payment record ID from the form submission
            if (isset($_SESSION['payment_record_id']) && !empty($_SESSION['payment_record_id'])) {
                // Update existing payment record
                $wpdb->update(
                    $wpdb->prefix . 'payments',
                    array(
                        'payment_id' => $paymentId,
                        'payment_status' => 'completed',
                        'updated_at' => current_time('mysql')
                    ),
                    array('id' => $_SESSION['payment_record_id']),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
                
                // Clear the session variable
                unset($_SESSION['payment_record_id']);
            } else {
                // Insert new payment record (for backward compatibility with direct payment links)
                $wpdb->insert(
                    $wpdb->prefix . 'payments',
                    array(
                        'added_date' => current_time('mysql'),
                        'ip_address' => $_SERVER['REMOTE_ADDR'],
                        'name' => $customerName,
                        'email' => $customerEmail,
                        'phone' => $checkout_session->metadata->customer_phone,
                        'amount' => $amountPaid,
                        'currency' => strtoupper($checkout_session->currency),
                        'payment_id' => $paymentId,
                        'payment_method' => 'stripe',
                        'payment_status' => 'completed'
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
                );
            }
            
            // Fire conversion tracking
            $conversionTracked = true;
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/WebPage" lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Payment Successful - Loan Eligibility Check</title>
<meta name="description" content="Your payment has been successfully processed" />
<meta name="keywords" content="payment success, loan eligibility" />
<meta name="robots" content="noindex,nofollow" />

<!-- Favicon -->
<link rel="shortcut icon" href="https://www.emiratesloan.info/images/favicon.png" type="image/x-icon">
<link rel="icon" href="https://www.emiratesloan.info/images/favicon.png" type="image/x-icon">
<link rel="icon" type="image/png" sizes="32x32" href="https://www.emiratesloan.info/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="https://www.emiratesloan.info/images/favicon-16x16.png">
<!-- Favicon -->

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
 fbq('init', '1745071249154015'); 
 fbq('track', 'PageView');
 fbq('track', 'Purchase', {value: <?=$amountPaid?>, currency: 'AED'});
</script>
<noscript>
 <img height="1" width="1" 
src="https://www.facebook.com/tr?id=1745071249154015&ev=PageView
&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-43182678-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-43182678-1');
  gtag('config', 'AW-981764355');
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->

<!-- MS clarity -->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "5j3ngih7lr");
</script>
<!-- MS clarity -->

<!-- Event snippet for EL conversion page -->
<script>
gtag('event', 'conversion', {
	'send_to': 'AW-981764355/dkdjCJmIk2IQg5KS1AM',
	'value': <?=$amountPaid?>,
	'currency': 'AED',
    'transaction_id': '<?=$paymentId?>'
});
</script>

<link href="<?=$site_url?>css/minify.css.php" type="text/css" rel="stylesheet">
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<style>
.success-box {
    background: #f0f9ff;
    border: 2px solid #10b981;
    border-radius: 10px;
    padding: 30px;
    margin: 30px 0;
    text-align: center;
}
.success-icon {
    font-size: 60px;
    color: #10b981;
    margin-bottom: 20px;
}
.payment-details {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
}
.payment-details h3 {
    color: #10b981;
    margin-bottom: 15px;
}
.payment-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}
.payment-row:last-child {
    border-bottom: none;
}
.payment-label {
    font-weight: 600;
    color: #374151;
}
.payment-value {
    color: #6b7280;
}
</style>
	
</head>
<body>

<?php include('trackers-in-boby.php'); ?>

<?php include('header.php'); ?>
<div class="body_bg">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <?php if ($paymentSuccess): ?>
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h1 style="color: #10b981;">Payment Successful!</h1>
            <p style="font-size: 18px; margin-top: 15px;">Thank you for your payment. Your transaction has been completed successfully.</p>
        </div>
        
        <div class="payment-details">
            <h3>Payment Details</h3>
            <div class="payment-row">
                <span class="payment-label">Name:</span>
                <span class="payment-value"><?=htmlspecialchars($customerName)?></span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Email:</span>
                <span class="payment-value"><?=htmlspecialchars($customerEmail)?></span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Amount Paid:</span>
                <span class="payment-value">AED <?=$amountPaid?></span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Transaction ID:</span>
                <span class="payment-value"><?=htmlspecialchars($paymentId)?></span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Service:</span>
                <span class="payment-value">Loan Eligibility Check & Credit Score</span>
            </div>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <p style="font-size: 16px; margin-bottom: 20px;">
                We will process your eligibility check and send the results to your email within 24-48 hours.
            </p>
            <p><a class="btn btn-success noUnderline" href="<?=$site_url?>">Back to Home Page</a> OR <a class="btn btn-success noUnderline" href="<?=$site_url?>contact.html">Contact Us</a></p>
        </div>
        
        <?php else: ?>
        <div class="success-box" style="border-color: #ef4444; background: #fef2f2;">
            <div class="success-icon" style="color: #ef4444;">✗</div>
            <h1 style="color: #ef4444;">Payment Verification Failed</h1>
            <p style="font-size: 18px; margin-top: 15px;">
                <?php if (isset($errorMessage)): ?>
                    Error: <?=htmlspecialchars($errorMessage)?>
                <?php else: ?>
                    We couldn't verify your payment. Please contact us if you have been charged.
                <?php endif; ?>
            </p>
            <p style="margin-top: 20px;">
                <a class="btn btn-success noUnderline" href="<?=$site_url?>">Back to Home Page</a> OR 
                <a class="btn btn-success noUnderline" href="<?=$site_url?>contact.html">Contact Us</a>
            </p>
        </div>
        <?php endif; ?>
        
        <br />
        <p style="text-align: center;">
            <a class="btn btn-success noUnderline" href="<?=$site_url?>personal-loan.html">Personal Loan</a>&nbsp;
            <a class="btn btn-success noUnderline" href="<?=$site_url?>car-loan.html">Car Loan</a>&nbsp;
            <a class="btn btn-success noUnderline" href="<?=$site_url?>credit-card.html">Credit Cards</a>&nbsp;
            <a class="btn btn-success noUnderline" href="<?=$site_url?>business-loan.html">Business Loan</a>&nbsp;
            <a class="btn btn-success noUnderline" href="<?=$site_url?>mortgage-loan.html">Mortgage Loan</a>
        </p>
      </div>
    </div>
  </div>
  <div class="margin_bottom15"></div>
  <?php include('footer.php'); ?>
</div>
</body>
</html>
