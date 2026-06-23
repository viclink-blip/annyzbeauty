<!-- Footer -->
<footer class="site-footer">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- Brand -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-icon brand-icon-sm"><i class="bi bi-flower1"></i></div>
                        <span class="fw-bold fs-5 text-white">Annyz<span class="brand-accent-light">beauty</span></span>
                    </div>
                    <p class="footer-text">Your premier beauty destination in Kenya. We bring you the finest skincare, makeup, haircare, and fragrances to help you glow every day.</p>
                    <div class="social-links">
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-tiktok"></i></a>
                        <a href="https://wa.me/254758556523" class="social-btn"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/products.php">Shop All</a></li>
                        <li><a href="<?= SITE_URL ?>/products.php?featured=1">Featured</a></li>
                        <li><a href="<?= SITE_URL ?>/cart.php">My Cart</a></li>
                        <li><a href="<?= SITE_URL ?>/orders.php">My Orders</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Categories</h6>
                    <ul class="footer-links">
                        <?php
                        $pdo = getDB();
                        $cats = $pdo->query("SELECT name, slug FROM categories WHERE is_active=1 LIMIT 6")->fetchAll();
                        foreach ($cats as $c):
                        ?>
                        <li><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($c['slug']) ?>"><?= sanitize($c['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt-fill"></i> Nairobi, Kenya</li>
                        <li><i class="bi bi-telephone-fill"></i> +254 758 556 523</li>
                        <li><i class="bi bi-envelope-fill"></i> hello@annyzbeauty.com</li>
                        <li><i class="bi bi-clock-fill"></i> Mon–Sat: 8am – 8pm</li>
                    </ul>
                    <!-- Payment Methods -->
                    <div class="payment-badges mt-3">
                        <span class="payment-badge"><i class="bi bi-phone-fill"></i> M-Pesa</span>
                        <span class="payment-badge"><i class="bi bi-paypal"></i> PayPal</span>
                        <span class="payment-badge"><i class="bi bi-cash-stack"></i> COD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                <p class="mb-0">Made with <i class="bi bi-heart-fill text-pink"></i> in Nairobi, Kenya</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
