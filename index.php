<?php
session_start();
require_once __DIR__ . '/Business/MenuService.php';

$restaurantName = 'DineNest Restaurant';
$tagline        = 'Good food, made with heart';

$role = $_SESSION['role'] ?? null;   // Customer / Administrator / null
$name = $_SESSION['name'] ?? '';
$e = fn($s) => htmlspecialchars($s);
$popularDishes = [];
try {
    $popularDishes = (new MenuService())->getPopular();
} catch (PDOException $error) {
    $popularDishes = [];
}

$menuImageName = static function ($value): string {
    $filename = basename(str_replace('\\', '/', trim((string)$value)));
    return preg_match('/\.(jpe?g|png|webp)$/i', $filename) ? $filename : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($restaurantName) ?></title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>

<!-- ============ Navbar ============ -->
<?php $base = ''; include 'Presentation/includes/navbar.php'; ?>

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
            <?php if (!$popularDishes): ?>
                <p class="popular-empty">Our popular dishes are being selected. Please check back soon.</p>
            <?php endif; ?>
            <?php foreach ($popularDishes as $dish): ?>
                <?php $imageName = $menuImageName($dish['image'] ?? ''); ?>
                <article class="dish">
                    <?php if ($imageName !== ''): ?>
                        <div class="dish-img">
                            <img src="Assets/images/menu/<?= rawurlencode($imageName) ?>"
                                 alt="<?= $e($dish['foodName']) ?>" loading="lazy"
                                 onerror="this.parentElement.hidden=true;">
                        </div>
                    <?php endif; ?>
                    <div class="dish-body">
                        <p class="popular-category"><?= $e($dish['category']) ?></p>
                        <h3><?= $e($dish['foodName']) ?></h3>
                        <p class="dish-desc"><?= $e($dish['description'] ?? '') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($role === 'Administrator'): ?>
            <div class="popular-manage-link"><a href="presentation/menu/">Manage Popular Dishes in Menu</a></div>
        <?php endif; ?>
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
<?php include __DIR__ . '/Presentation/includes/site_footer.php'; ?>

</body>