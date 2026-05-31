<?php
session_start();

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

// Get site URL
$site_url = home_url('/');
?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/WebPage" lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Payment Cancelled - Loan Eligibility Check</title>
<meta name="description" content="Your payment has been cancelled" />
<meta name="keywords" content="payment cancelled, loan eligibility" />
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

<link href="<?=$site_url?>css/minify.css.php" type="text/css" rel="stylesheet">
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<style>
.cancel-box {
    background: #fffbeb;
    border: 2px solid #f59e0b;
    border-radius: 10px;
    padding: 30px;
    margin: 30px 0;
    text-align: center;
}
.cancel-icon {
    font-size: 60px;
    color: #f59e0b;
    margin-bottom: 20px;
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
        <div class="cancel-box">
            <div class="cancel-icon">⚠</div>
            <h1 style="color: #f59e0b;">Payment Cancelled</h1>
            <p style="font-size: 18px; margin-top: 15px;">
                Your payment has been cancelled. No charges have been made to your account.
            </p>
            <p style="font-size: 16px; margin-top: 20px;">
                If you experienced any issues during checkout, please feel free to try again or contact our support team.
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <p>
                <a class="btn btn-success noUnderline" href="<?=$site_url?>stripe-checkout.php">Try Again</a>&nbsp;
                <a class="btn btn-primary noUnderline" href="<?=$site_url?>">Back to Home Page</a>&nbsp;
                <a class="btn btn-info noUnderline" href="<?=$site_url?>contact.html">Contact Us</a>
            </p>
        </div>
        
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
