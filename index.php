<?php
session_start();

/* ====== 店名在這裡改一次就好 ====== */
$restaurantName = 'Your Restaurant';
$tagline        = 'Good food, made with heart';
/* ================================= */

$role = $_SESSION['role'] ?? null;   // Customer / Administrator / null
$name = $_SESSION['name'] ?? '';
$e = fn($s) => htmlspecialchars($s);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($restaurantName) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ============ Navbar ============ -->
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <span class="logo-mark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M7 3v8a2 2 0 0 0 2 2v8M11 3v8M7 7h4M17 3c-2 2-3 5-3 8h3v10"/>
                </svg>
            </span>
            <span class="logo-text"><?= $e($restaurantName) ?></span>
        </a>

        <div class="nav-links">
            <a href="index.php">Home</a>

            <?php if ($role === 'Customer'): ?>
                <a href="presentation/menu/">Menu</a>
                <a href="presentation/order/">My Orders</a>
                <a href="presentation/feedback/feedback_add.php">Feedback</a>
                <span class="nav-user">Hi, <?= $e($name) ?></span>
                <a href="presentation/auth/logout.php" class="btn-nav">Logout</a>

            <?php elseif ($role === 'Administrator'): ?>
                <a href="presentation/customer/">Customers</a>
                <a href="presentation/menu/">Menu</a>
                <a href="presentation/table/">Tables</a>
                <a href="presentation/order/">Orders</a>
                <a href="presentation/payment/payment_list.php">Payments</a>
                <a href="presentation/feedback/feedback_list.php">Feedback</a>
                <a href="presentation/auth/logout.php" class="btn-nav">Logout</a>

            <?php else: ?>
                <a href="presentation/auth/register.php">Register</a>
                <a href="presentation/auth/login.php" class="btn-nav">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ============ Hero ============ -->
<header class="hero">
    <div class="hero-inner">
        <div class="eyebrow">Welcome to</div>
        <h1><?= $e($restaurantName) ?></h1>
        <div class="divider"></div>
        <p><?= $e($tagline) ?>. Order from your table, enjoy your meal, and pay at the counter when you're done.</p>

        <?php if ($role): ?>
            <a href="presentation/menu/" class="btn btn-gold">VIEW MENU</a>
        <?php else: ?>
            <a href="presentation/auth/login.php" class="btn btn-gold">LOGIN TO ORDER</a>
            <a href="presentation/auth/register.php" class="btn btn-line">CREATE ACCOUNT</a>
        <?php endif; ?>
    </div>
    <div class="scroll-hint"></div>
</header>

<!-- ============ Info cards ============ -->
<div class="container">
    <div class="info-cards">
        <div class="info-card">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <h3>Opening Hours</h3>
            <p>Daily 10:00 AM – 10:00 PM</p>
        </div>
        <div class="info-card">
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 9v.01M18 15v.01"/></svg>
            <h3>Pay at Counter</h3>
            <p>Cash or online payment</p>
        </div>
        <div class="info-card">
            <svg viewBox="0 0 24 24"><path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.5 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/></svg>
            <h3>Rate Your Meal</h3>
            <p>Share your feedback with us</p>
        </div>
    </div>
</div>

<!-- ============ Popular dishes ============ -->
<section>
    <div class="container">
        <div class="section-title">
            <div class="eyebrow">Our Specials</div>
            <h2>Popular Dishes</h2>
            <div class="line"></div>
        </div>
        <div class="dishes">
            <div class="dish"><div class="dish-img">🍗</div><div class="dish-body"><h3>Chicken Rice</h3><p>Steamed rice with roasted chicken</p><span class="price">RM 12.00</span></div></div>
            <div class="dish"><div class="dish-img">🍜</div><div class="dish-body"><h3>Curry Noodles</h3><p>Rich, spicy and comforting</p><span class="price">RM 10.00</span></div></div>
            <div class="dish"><div class="dish-img">🍟</div><div class="dish-body"><h3>Crispy Fries</h3><p>Perfect for sharing</p><span class="price">RM 7.00</span></div></div>
            <div class="dish"><div class="dish-img">🧋</div><div class="dish-body"><h3>Iced Milk Tea</h3><p>Cool, sweet and creamy</p><span class="price">RM 6.00</span></div></div>
        </div>
    </div>
</section>

<!-- ============ How it works ============ -->
<section class="process">
    <div class="container">
        <div class="section-title">
            <div class="eyebrow">Simple &amp; Easy</div>
            <h2>How It Works</h2>
            <div class="line"></div>
        </div>
        <div class="steps">
            <div class="step"><div class="num">01</div><h3>Login</h3><p>Sign in to your account</p></div>
            <div class="step"><div class="num">02</div><h3>Order</h3><p>Pick your dishes from the menu</p></div>
            <div class="step"><div class="num">03</div><h3>Pay</h3><p>Pay at the counter and get your receipt</p></div>
            <div class="step"><div class="num">04</div><h3>Rate Us</h3><p>Tell us how we did</p></div>
        </div>
    </div>
</section>

<!-- ============ Feedback ============ -->
<section class="feedback-band">
    <div class="container">
        <div class="section-title">
            <div class="eyebrow">Your Opinion Matters</div>
            <h2>How Was Your Meal?</h2>
            <div class="line"></div>
        </div>
        <div class="stars">★★★★★</div>
        <p>Your feedback helps us serve you better.</p>
        <?php if ($role === 'Customer'): ?>
            <a href="presentation/feedback/feedback_add.php" class="btn btn-gold">LEAVE FEEDBACK</a>
        <?php elseif (!$role): ?>
            <a href="presentation/auth/login.php" class="btn btn-gold">LOGIN TO LEAVE FEEDBACK</a>
        <?php endif; ?>
    </div>
</section>

<!-- ============ Footer ============ -->
<footer>
    <div class="container">
        <div class="serif"><span class="gold">◆</span> <?= $e($restaurantName) ?> <span class="gold">◆</span></div>
        <p>Open daily 10:00 AM – 10:00 PM</p>
        <p class="copy">© <?= date('Y') ?> <?= $e($restaurantName) ?> · AMCS2194 Mini Project</p>
    </div>
</footer>

</body>