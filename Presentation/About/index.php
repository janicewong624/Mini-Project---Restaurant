<?php
session_start();
$pageTitle = 'About Us';
$bodyClass = 'about-page';
include __DIR__ . '/../includes/header.php';
?>
</div></main>

<div class="about-wrap">
    <div class="about-head" data-reveal="up">
        <h1>About <?= $e($restaurantName) ?></h1>
        <div class="line"></div>
        <p>We started with a simple idea: good food, cooked with heart, shared at a table where everyone feels at home.
           Every dish on our menu is prepared fresh daily with quality ingredients and a lot of care.</p>
    </div>

    <div class="about-row">
        <div class="about-photo" data-reveal="left">
            <img src="<?= $base ?>assets/images/about1.jpg" alt="Our signature dish"
                 onerror="this.outerHTML='<div class=&quot;ph&quot;>🍛</div>'">
        </div>
        <div class="about-text" data-reveal="right">
            <div class="about-badge"><small>★ OUR PROMISE ★</small>Fresh, Homemade &amp; Friendly</div>
            <p>Discover the flavours our guests keep coming back for. From comforting rice dishes to spicy noodles and
               refreshing drinks, every recipe is the result of patient experimenting, until it tastes just right.</p>
            <p>Dine in with friends and family, or take your favourites home. Order from your table, pay at the counter
               with cash or online payment, and tell us how we did.</p>
            <a href="<?= $base ?>presentation/menu/" class="btn btn-gold">View Our Menu</a>
        </div>
    </div>

    <div class="about-values">
        <div class="about-value" data-reveal="zoom" style="--d:0s"><span class="ic">🌿</span><h3>Fresh Ingredients</h3><p>Prepared fresh every day.</p></div>
        <div class="about-value" data-reveal="zoom" style="--d:.15s"><span class="ic">👨‍🍳</span><h3>Cooked With Heart</h3><p>Recipes perfected with care.</p></div>
        <div class="about-value" data-reveal="zoom" style="--d:.3s"><span class="ic">🤝</span><h3>Friendly Service</h3><p>A warm welcome, every visit.</p></div>
    </div>

    <div class="about-row reverse">
        <div class="about-photo" data-reveal="right">
            <img src="<?= $base ?>assets/images/about2.jpg" alt="Visit us"
                 onerror="this.outerHTML='<div class=&quot;ph&quot;>🍜</div>'">
        </div>
        <div class="about-text" data-reveal="left">
            <h3 class="serif" style="color:var(--soft-green-dark);font-size:1.6rem;margin-bottom:10px;">Visit Us</h3>
            <p>Open daily 10:00 AM – 10:00 PM. Come hungry, leave happy. We look forward to seeing you.</p>
            <p>Book a table or order online to skip the wait.</p>
        </div>
    </div>
</div>

<div class="container"><div>
<?php include __DIR__ . '/../includes/footer.php'; ?>