# Complete Payment Flow - From External Site to Stripe

## 🔄 Payment Flow Overview

```
External Site → Encrypted Link → WordPress Page → Form Submission → Database Insert → Stripe Checkout → Success → Database Update → Webhook Confirmation
```

---

## 📝 Step-by-Step Flow

### **Step 1: External Site (Client's Site)**

Generate encrypted payment link with customer data:

```php
$hashKey = 'WH20I7WZK18N63U2';

$paramMap = array();
$paramMap['name']  = $_SESSION['name'];
$paramMap['email']  = $_SESSION['email'];
$paramMap['phone'] = $_SESSION['phone'];
$paramMap['amount'] = '10';
$paramMap['currency_code'] = 'AED';
$paramMap['message'] = 'Loan eligibility check request';

// Creating string to be encoded
$mapString = '';
foreach ($paramMap as $key => $val) {
  $mapString .=  $key.'='.$val.'&';
}
$mapString = substr($mapString, 0, -1);

$cipher = "AES-128-ECB";
$crypttext = openssl_encrypt($mapString, $cipher, $hashKey, OPENSSL_RAW_DATA);
$hashRequest = urlencode(base64_encode($crypttext));

// Payment link
$paymentLink = "https://babaaz.ae/stripe-payment?key=" . $hashRequest;
```

**HTML Link:**
```html
<a target="_blank" href="https://babaaz.ae/stripe-payment?key=<?=$hashRequest?>">
    Pay & Get Eligibility Check
</a>
```

---

### **Step 2: WordPress Page (page-stripe.php)**

**URL:** `https://babaaz.ae/stripe-payment?key=ENCRYPTED_DATA`

**Template Name:** Stripe Payment

**What it does:**
1. Decrypts the key parameter
2. Extracts customer data (name, email, phone, amount, currency, message)
3. Displays a form pre-filled with this data
4. User can review and modify if needed
5. Form submits to `/process/do_stripe.php`

**Key Code:**
```php
$hashKey = 'WH20I7WZK18N63U2';
$cipher = "AES-128-ECB";
$adcrypttext = base64_decode($_GET['key']);
$dcrypthash = openssl_decrypt($adcrypttext, $cipher, $hashKey, OPENSSL_RAW_DATA);
parse_str($dcrypthash, $getRequest);
// Now $getRequest contains: name, email, phone, amount, currency_code, message
```

---

### **Step 3: Form Processing (do_stripe.php)**

**File:** `/process/do_stripe.php`

**What it does:**
1. Validates WordPress nonce (security)
2. Validates all required fields
3. Sanitizes user input
4. **Inserts record into `wp_payments` table** with status: `pending`
5. Stores customer data in PHP session
6. Stores database record ID in session
7. Redirects to `stripe-checkout.php`

**Database Insert:**
```php
$wpdb->insert(
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
        'payment_status' => 'pending' // Initial status
    )
);

$_SESSION['payment_record_id'] = $wpdb->insert_id;
```

---

### **Step 4: Stripe Checkout (stripe-checkout.php)**

**What it does:**
1. Retrieves customer data from session
2. Creates Stripe Checkout Session
3. Displays payment modal/popup
4. User enters credit card details on Stripe's secure page
5. After payment:
   - **Success:** Redirects to `stripe-success.php`
   - **Cancel:** Redirects to `stripe-cancel.php`

---

### **Step 5: Payment Success (stripe-success.php)**

**URL:** `https://babaaz.ae/stripe-success.php?session_id=STRIPE_SESSION_ID`

**What it does:**
1. Retrieves Stripe session using session_id
2. Verifies payment status = 'paid'
3. Gets payment details from Stripe
4. **Updates the pending payment record** with:
   - `payment_id` (from Stripe)
   - `payment_status` = 'completed'
   - `updated_at` timestamp
5. Displays success page with transaction details

**Key Logic:**
```php
if (isset($_SESSION['payment_record_id'])) {
    // Update existing record
    $wpdb->update(
        $wpdb->prefix . 'payments',
        array(
            'payment_id' => $paymentId,
            'payment_status' => 'completed'
        ),
        array('id' => $_SESSION['payment_record_id'])
    );
}
```

---

### **Step 6: Webhook Confirmation (stripe-webhook.php)**

**URL:** `https://babaaz.ae/stripe-webhook.php` (configured in Stripe Dashboard)

**What it does:**
1. Receives webhook from Stripe (server-to-server)
2. Verifies webhook signature (security)
3. Checks event type: `checkout.session.completed`
4. **Finds existing payment record** (by payment_id or email)
5. Updates record with:
   - `webhook_received` = 1
   - Confirms status = 'completed'
6. Sends confirmation email to customer

**Why webhook?**
- Success page can be skipped/closed by user
- Webhook is server-to-server (more reliable)
- Ensures payment is always recorded

---

## 📊 Database Table Structure

**Table:** `wp_payments`

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| added_date | DATETIME | When record created |
| ip_address | VARCHAR(50) | Customer IP |
| name | VARCHAR(255) | Customer name |
| email | VARCHAR(255) | Customer email |
| phone | VARCHAR(50) | Customer phone |
| amount | DECIMAL(10,2) | Payment amount |
| currency | VARCHAR(10) | Currency code (AED) |
| message | TEXT | Customer message |
| payment_id | VARCHAR(255) | Stripe payment intent ID |
| session_id | VARCHAR(255) | Stripe session ID |
| payment_method | VARCHAR(50) | Always 'stripe' |
| payment_status | ENUM | pending → completed |
| webhook_received | TINYINT | 0 → 1 when webhook received |
| created_at | DATETIME | Creation timestamp |
| updated_at | TIMESTAMP | Auto-update timestamp |

---

## 🔐 Security Features

1. **Encrypted Link:** Customer data encrypted with AES-128-ECB
2. **WordPress Nonce:** CSRF protection on form submission
3. **Data Sanitization:** All inputs sanitized with WordPress functions
4. **Webhook Signature:** Stripe webhook signature verification
5. **SQL Injection Protection:** Using `$wpdb->prepare()` and `$wpdb->insert()`
6. **No Card Data on Server:** Card details only on Stripe's servers

---

## 🎯 Payment Status Flow

```
External Site Request
        ↓
pending (inserted by do_stripe.php)
        ↓
User pays on Stripe
        ↓
completed (updated by stripe-success.php)
        ↓
completed + webhook_received=1 (confirmed by stripe-webhook.php)
```

---

## 🧪 Testing

### **Test Link Generation:**

Use this on your external site:

```php
<?php
session_start();
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['phone'] = '971501234567';

$hashKey = 'WH20I7WZK18N63U2';

$paramMap = array(
    'name'  => $_SESSION['name'],
    'email'  => $_SESSION['email'],
    'phone' => $_SESSION['phone'],
    'amount' => '10',
    'currency_code' => 'AED',
    'message' => 'Test payment'
);

$mapString = '';
foreach ($paramMap as $key => $val) {
    $mapString .=  $key.'='.$val.'&';
}
$mapString = substr($mapString, 0, -1);

$cipher = "AES-128-ECB";
$crypttext = openssl_encrypt($mapString, $cipher, $hashKey, OPENSSL_RAW_DATA);
$hashRequest = urlencode(base64_encode($crypttext));

echo '<a href="https://babaaz.ae/stripe-payment?key=' . $hashRequest . '">Test Payment Link</a>';
?>
```

### **Test Cards:**
- Success: `4242 4242 4242 4242`
- Decline: `4000 0000 0000 0002`

---

## 📁 Files Involved

| File | Purpose |
|------|---------|
| `page-stripe.php` | WordPress template - decrypts & shows form |
| `process/do_stripe.php` | Form handler - validates & stores to DB |
| `stripe-checkout.php` | Creates Stripe session & shows payment |
| `stripe-success.php` | Success page - updates DB record |
| `stripe-cancel.php` | Cancel page |
| `stripe-webhook.php` | Webhook handler - confirms payment |
| `stripe-config.php` | Stripe API keys & config |

---

## ⚙️ Configuration

### **1. Hash Key (Must Match on Both Sites)**

**External Site & babaaz.ae:**
```php
$hashKey = 'WH20I7WZK18N63U2';
```

### **2. WordPress Page**

Create a WordPress page with:
- **Template:** Stripe Payment (page-stripe.php)
- **Slug:** `stripe-payment`
- **URL:** `https://babaaz.ae/stripe-payment`

### **3. Database Table**

Run the SQL from `stripe-payments-table.sql` in your WordPress database.

### **4. Stripe Webhook**

In Stripe Dashboard:
- **URL:** `https://babaaz.ae/stripe-webhook.php`
- **Events:** `checkout.session.completed`

---

## ✅ Verification Checklist

- [ ] Hash key same on both sites
- [ ] WordPress page created with correct template
- [ ] Database table created
- [ ] Stripe API keys configured
- [ ] Webhook setup in Stripe Dashboard
- [ ] Test payment successful
- [ ] Database record created and updated
- [ ] Confirmation email received

---

## 🐛 Troubleshooting

**Problem:** "Invalid request" error
- Check WordPress nonce is being passed in form

**Problem:** "Please complete all required fields"
- Verify all fields have data after decryption

**Problem:** Payment successful but DB not updated
- Check session variables are set
- Verify payment_record_id in session
- Check webhook is receiving events

**Problem:** Decryption fails
- Verify hash key matches on both sites (case-sensitive)
- Check URL encoding of encrypted data

---

This is the complete flow! All files are ready to use. 🎉
