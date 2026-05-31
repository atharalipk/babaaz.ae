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

// Get customer details from session
$customerName = isset($_SESSION['name']) ? $_SESSION['name'] : '';
$customerEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$customerPhone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';

// Check if session data exists
if (empty($customerEmail)) {
    die("Error: Customer information not found in session. Please complete the form first.");
}

// Create Stripe Checkout Session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => strtolower(PAYMENT_CURRENCY),
                'product_data' => [
                    'name' => PAYMENT_DESCRIPTION,
                    'description' => 'Get your loan eligibility and credit score',
                ],
                'unit_amount' => PAYMENT_AMOUNT * 100, // Convert AED to fils (smallest currency unit)
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => STRIPE_SUCCESS_URL,
        'cancel_url' => STRIPE_CANCEL_URL,
        'customer_email' => $customerEmail,
        'metadata' => [
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
        ],
    ]);
    
    $sessionId = $checkout_session->id;
    
} catch (Exception $e) {
    die("Error creating checkout session: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/WebPage" lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Payment - Loan Eligibility Check</title>
<meta name="description" content="Complete your payment to get your loan eligibility check and credit score" />
<meta name="keywords" content="loan eligibility, credit score, payment" />
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
 fbq('track', 'Lead');
</script>
<noscript>
 <img height="1" width="1" 
src="https://www.facebook.com/tr?id=1745071249154015&ev=PageView
&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->

<!--Start of FB Like Box-->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8&appId=1094459403946552";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!--End of FB Like Box-->

<!--Start of Pinterest Code-->
<meta name="p:domain_verify" content="dffec708b0354feab2dd62a143eea4b0"/>
<!--End of Pinterest Code-->

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
	'value': 1.0,
	'currency': 'AED'
});
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<link href="<?=$site_url?>css/minify.css.php" type="text/css" rel="stylesheet">
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="https://js.stripe.com/v3/"></script>
	
</head>
<body>

<?php include('trackers-in-boby.php'); ?>

<?php include('header.php'); ?>
<div class="body_bg">
  <div class="container">
    <div class="row">
      <div class="col-lg-7 ">
        <h1>Complete Your Payment</h1>
        <p>You're about to get your loan eligibility check and credit score for only AED 10.</p>
        <br />
        <p><a class="btn btn-success noUnderline" href="<?=$site_url?>personal-loan.html">Personal Loan</a>&nbsp;<a class="btn btn-success noUnderline" href="<?=$site_url?>car-loan.html">Car Loan</a>&nbsp;<a class="btn btn-success noUnderline" href="<?=$site_url?>credit-card.html">Credit Cards</a>&nbsp;<a class="btn btn-success noUnderline" href="<?=$site_url?>business-loan.html">Business Loan</a>&nbsp;<a class="btn btn-success noUnderline" href="<?=$site_url?>mortgage-loan.html">Mortgage Loan</a></p>
        <p><a class="btn btn-success noUnderline" href="<?=$site_url?>">Back to Home Page</a> OR <a class="btn btn-success noUnderline" href="<?=$site_url?>contact.html">Contact Us</a></p>
      </div>
      <?php include("rightnav.php"); ?>
    </div>
  </div>
  <div class="margin_bottom15"></div>
  <?php include('footer.php'); ?>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
  <script type="text/javascript">
  var stripe = Stripe('<?=STRIPE_PUBLISHABLE_KEY?>');
  
  $.confirm({
    theme: 'modern',
    columnClass: 'col-md-6 col-md-offset-3',
    closeIcon: true,
    animation: 'scale',
    type: 'green',
    draggable: false,
    title: '<img src="<?=$site_url?>images/logo.png"><br/><br/>' + 'Pay <span>And</span> Get',
    autoOpen: true,
    content:  '<span>Get your</span> loan eligibility <span>&</span> credit score <span>in</span> <?=PAYMENT_CURRENCY?> <?=PAYMENT_AMOUNT?> <span>only</span>: <button id="checkout-button" class="btn btn-success">Pay & Get Now</button>',
    onContentReady: function () {
      var self = this;
      $('#checkout-button').on('click', function(e) {
        e.preventDefault();
        stripe.redirectToCheckout({
          sessionId: '<?=$sessionId?>'
        }).then(function (result) {
          if (result.error) {
            alert(result.error.message);
          }
        });
      });
    }
  });
  </script>
</div>
</body>
</html>
<?php exit; ?>
