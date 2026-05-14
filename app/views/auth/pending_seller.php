<?php
// app/views/auth/pending_seller.php
// Shown after seller registration while awaiting admin approval
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Pending - ECommerce</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
<div class="container" style="max-width:600px;margin:80px auto;text-align:center;">
    <div class="card" style="padding:40px;">
        <h2>Application Under Review</h2>
        <p style="font-size:1.1em;margin:20px 0;">
            Your seller application is pending admin approval.
            You will be able to access your seller dashboard once an administrator reviews and approves your account.
        </p>
        <p>Please check back later or contact support if you have not heard back within 2 business days.</p>
        <a href="<?php echo BASE_URL; ?>?c=auth&a=login" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#333;color:#fff;text-decoration:none;border-radius:4px;">Go to Login</a>
    </div>
</div>
</body>
</html>
