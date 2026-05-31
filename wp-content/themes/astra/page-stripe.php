<?php
/**
Template Name: Stripe Payment
*/
get_header();

$hashKey='WH20I7WZK18N63U2';
$cipher="AES-128-ECB";
$adcrypttext=base64_decode($_GET['key']);
$dcrypthash = openssl_decrypt($adcrypttext, $cipher, $hashKey,OPENSSL_RAW_DATA);
parse_str($dcrypthash, $getRequest);
?>
<style>
.form-label{top: 26px !important;}
</style>
<div class="loader">
  <div class="loading_icon">
    <img src="<?php bloginfo('stylesheet_directory'); ?>/assets/images/loader.gif" alt="Loader">
  </div>
</div>
<div class="margintp"> 
  <section class="wow fadeIn no-inset" id="contacts">
  <div class="container-fluid">
    <div class="row equalize">
      <div class="col-sm-3"></div>
      <div class="col-sm-6 wow fadeIn">
        <div class="inset-six-all text-center xs-no-padding-lr">
          <div class="text-medium-gray text-font-sec text-small text-uppercase offset-5px-bottom xs-offset-three-bottom">Use it</div>
          <h5 class="offset-10px-bottom text-font-sec text-medium text-uppercase xs-offset-ten-bottom"><?php the_title(); ?></h5>
          <?php if(have_posts()): while(have_posts()): the_post(); ?>
          <?php the_content(); ?>
          <?php wp_link_pages(array('before' => '<div class="page-links"><strong>Pages:</strong> ', 'after' => '</div>', 'next_or_number' => 'number')); ?>
          <?php endwhile; endif; ?>

          <div style="background-color:#FC0; border:#900 2px dotted; margin:25px 56px;" align="center"><span class="style5"><strong>Important:</strong> Use this payment option only when you receive instructions from our Team.<br />All transactions are secure and 256bits encrypted. Your information is not stored in any way.</span></div>
          <div>
          <?php if(isset($_SESSION['error'])){ ?><div class="alert alert-danger alert-dismissible fade show"><button class="close" data-dismiss="alert">&times;</button><?=$_SESSION['error']?></div><?php } ?>
          <?php if(isset($_SESSION['success'])){ ?><div class="alert alert-success alert-dismissible fade show"><button class="close" data-dismiss="alert">&times;</button><?=$_SESSION['success']?></div><?php } ?>
          <form name="payment-form" id="payment-form" class="rd-mailform text-left" method="post" action="<?php bloginfo('url'); ?>/process/do_stripe.php">
          <?php wp_nonce_field( 'athar_stripe', 'athar_stripe_nonce' ); ?>
            <div class="row">
              <div class="col-md-12 wow fadeIn center-col text-center">
                <div class="form-wrap">
                  <label class="form-label" for="contact-name">Name</label>
                  <input class="form-input" type="text" id="name" name="name" value="<?=$getRequest['name']?>" required>
                </div>
                <div class="form-wrap">
                  <label class="form-label" for="contact-email">Email</label>
                  <input class="form-input" type="email" name="email" id="email" value="<?=$getRequest['email']?>" data-constraints="@Required @Email">
                </div>
                <div class="form-wrap">
                  <label class="form-label" for="contact-phone">Phone</label>
                  <input class="form-input" type="text" name="phone" value="<?=$getRequest['phone']?>" data-constraints="@Required @Numeric">
                </div>
                <div class="form-wrap">
                  <label class="form-label" for="contact-amount">Amount</label>
                  <input class="form-input" type="number" value="<?=$getRequest['amount']?>" name="amount" data-constraints="@Required @Numeric">
                </div>
                <div class="form-wrap">
                  <label class="form-label" for="currency_code">Currency</label>
                  <select class="form-select" name="currency_code" id="currency_code">
                    <option value="USD" <?=$getRequest['currency_code']=='USD'?'selected':''?>>United States Dollar</option>
                    <option value="AED" <?=$getRequest['currency_code']=='AED'?'selected':''?>>United Arab Emirates Dirham</option>
                    <option value="PKR" <?=$getRequest['currency_code']=='PKR'?'selected':''?>>Pakistani Rupee</option>
                  </select>
                </div>
                <div class="form-wrap">
                  <label class="form-label" for="contact-message">Message</label>
                  <textarea class="form-input" name="message"><?=$getRequest['message']?></textarea>
                </div>
                <div class="form-button group-sm text-center text-lg-left">
                  <input type="submit" value="send" class="primary-btn offset-30px-top xs-offset-three-top">
                </div>
              </div>
            </div>
          </form>
          </div>
        </div>
      </div>
      <div class="col-sm-3"></div>
    </div>
    <div class="row" style="margin-bottom:40px;">
      <div class="col-sm-3"></div>
      <div class="col-sm-6" style="text-align: center;">
        <div style="background: linear-gradient(135deg, #635bff 0%, #1a1f71 100%); padding: 20px; border-radius: 8px; display: inline-block;">
          <svg width="60" height="25" viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.93 0 1.85 6.29.97 6.29 5.88z" fill="#fff"/>
          </svg>
        </div>
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
          <i class="fa fa-lock"></i> Secure 256-bit encrypted payment processing powered by Stripe
        </p>
      </div>
      <div class="col-sm-3"></div>
    </div>
    
  </div>
</section>
</div>
<?php get_footer(); 

?>
