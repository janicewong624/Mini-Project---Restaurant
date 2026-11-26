<?php
// 需要 $restaurantName、$base（沒有就用預設值）
$restaurantName = $restaurantName ?? 'DineNest Restaurant';
$fb = $base ?? '';
$e2 = fn($s) => htmlspecialchars((string)$s);
?>
<div class="site-footer" role="contentinfo">
    <div class="container sf-grid">

        <div class="sf-col sf-brand">
            <div class="sf-logo serif"><span class="gold">◆</span> <?= $e2($restaurantName) ?></div>
            <p>Good food, made with heart.</p>
            <p class="sf-hours">Open daily 10:00 AM – 10:00 PM</p>
        </div>

        <div class="sf-col">
            <h4>Contact</h4>
            <ul class="sf-list">
                <li>📍 No. 1, Jalan Contoh, Taman Contoh,<br>53300 Kuala Lumpur</li>
                <li>📞 <a href="tel:+60312345678">+603-1234 5678</a></li>
                <li>✉️ <a href="mailto:hello@dinenest.com">hello@dinenest.com</a></li>
            </ul>
        </div>

        <div class="sf-col">
            <h4>Explore</h4>
            <ul class="sf-list">
                <li><a href="<?= $fb ?>index.php">Home</a></li>
                <li><a href="<?= $fb ?>presentation/menu/">Menu</a></li>
                <li><a href="<?= $fb ?>presentation/feedback/feedback_add.php">Feedback</a></li>
                <li><a href="<?= $fb ?>presentation/about/">About Us</a></li>
            </ul>
        </div>

        <div class="sf-col">
            <h4>Payment</h4>
            <div class="pay-badges">
                <span class="pay pay-cash">💵 Cash</span>
                <span class="pay pay-tng">TNG eWallet</span>
                <span class="pay pay-visa">VISA</span>
                <span class="pay pay-mc"><i></i><i></i> Mastercard</span>
                <span class="pay pay-debit">Debit Card</span>
            </div>
            <h4 class="sf-follow">Follow Us</h4>
            <div class="sf-social">
                <a href="#" aria-label="Facebook" title="Facebook">
                    <svg viewBox="0 0 24 24"><path d="M14 8V6.5c0-.8.3-1.5 1.5-1.5H17V2h-2.5C11.8 2 10.5 3.7 10.5 6v2H8v3h2.5v10H14V11h2.7l.4-3z"/></svg>
                </a>
                <a href="#" aria-label="Instagram" title="Instagram">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2"/></svg>
                </a>
                <a href="#" aria-label="TikTok" title="TikTok">
                    <svg viewBox="0 0 24 24"><path d="M16.5 3c.3 2.2 1.7 3.7 4 3.9v3a7 7 0 0 1-4-1.3v6.2a6 6 0 1 1-6-6c.3 0 .6 0 .9.1v3.2a3 3 0 1 0 2.1 2.9V3z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="sf-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= $e2($restaurantName) ?>. All rights reserved.</span>
            <span class="sf-note">All prices are in Ringgit Malaysia and are subject to 10% service charge and 6% SST.</span>
        </div>
    </div>
</div>