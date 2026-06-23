<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$pdo = getDB();

// Featured products
$featured = $pdo->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 AND p.is_active=1 LIMIT 8")->fetchAll();

// All categories
$categories = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

// New arrivals (latest 4)
$newArrivals = $pdo->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_active=1 ORDER BY p.created_at DESC LIMIT 4")->fetchAll();

// Counts for stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
?>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 hero-content">
                <div class="hero-eyebrow">
                    <i class="bi bi-stars"></i> Kenya's Premium Beauty Store
                </div>
                <h1 class="hero-title">
                    Discover Your<br>
                    <em>Natural Glow</em><br>
                    Within
                </h1>
                <p class="hero-subtitle">
                    Curated skincare, makeup, haircare, and fragrances for every skin tone and beauty ritual. Delivered across Kenya.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="products.php" class="btn btn-pink-lg">Shop Now <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="products.php?featured=1" class="btn btn-outline btn-lg" style="border:2px solid rgba(255,255,255,0.3);color:white;border-radius:12px;padding:14px 28px;">
                        Best Sellers
                    </a>
                </div>
                <div class="hero-stats">
                    <div>
                        <div class="hero-stat-num"><?= $totalProducts ?>+</div>
                        <div class="hero-stat-label">Products</div>
                    </div>
                    <div>
                        <div class="hero-stat-num">500+</div>
                        <div class="hero-stat-label">Happy Clients</div>
                    </div>
                    <div>
                        <div class="hero-stat-num">100%</div>
                        <div class="hero-stat-label">Authentic</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-image-wrap">
                <div class="hero-image-card">
                    <img src="assets/images/hero-beauty.jpg" alt="Beauty Products" onerror="this.src='https://placehold.co/400x480/E91E8C/white?text=Annyzbeauty'">
                </div>
                <div class="hero-float-badge top-right">
                    <span class="badge-dot"></span> Free Delivery over KSh 5,000
                </div>
                <div class="hero-float-badge bottom-left">
                    ⭐ 4.9 Rating · 500+ Reviews
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== TRUST BAR ===== -->
<section style="background:var(--pink-soft);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:20px 0;">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <i class="bi bi-truck text-pink-color fs-4"></i>
                <div class="fw-600 mt-1" style="font-size:13px;font-weight:600;">Fast Delivery</div>
                <div style="font-size:12px;color:var(--mid-grey);">Nairobi & Nationwide</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-shield-check text-pink-color fs-4"></i>
                <div class="fw-600 mt-1" style="font-size:13px;font-weight:600;">100% Authentic</div>
                <div style="font-size:12px;color:var(--mid-grey);">Genuine Products Only</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-arrow-counterclockwise text-pink-color fs-4"></i>
                <div class="fw-600 mt-1" style="font-size:13px;font-weight:600;">Easy Returns</div>
                <div style="font-size:12px;color:var(--mid-grey);">Within 7 Days</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-headset text-pink-color fs-4"></i>
                <div class="fw-600 mt-1" style="font-size:13px;font-weight:600;">24/7 Support</div>
                <div style="font-size:12px;color:var(--mid-grey);">WhatsApp & Phone</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="section-pad">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow">Explore</span>
            <h2 class="section-title">Shop by Category</h2>
            <div class="section-divider mx-auto"></div>
        </div>

        <?php
        $catIcons = ['skincare'=>'bi-droplet-fill','makeup'=>'bi-palette-fill','hair-care'=>'bi-scissors','fragrances'=>'bi-flower2','nail-care'=>'bi-brush-fill','body-care'=>'bi-heart-fill'];
        ?>
        <div class="row g-3 justify-content-center">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="text-decoration-none">
                    <div class="category-card">
                        <div class="cat-icon"><i class="bi <?= $catIcons[$cat['slug']] ?? 'bi-bag-fill' ?>"></i></div>
                        <div class="cat-name"><?= sanitize($cat['name']) ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section class="section-pad" style="background:var(--light-grey);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="section-eyebrow">Curated</span>
                <h2 class="section-title mb-0">Featured Products</h2>
                <div class="section-divider left"></div>
            </div>
            <a href="products.php?featured=1" class="btn btn-outline-pink">View All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($featured as $p): ?>
            <div class="col-6 col-md-4 col-lg-3 reveal">
                <?php include 'includes/product_card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== PROMO BANNER ===== -->
<section class="section-pad">
    <div class="container">
        <div class="promo-banner">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span style="background:rgba(233,30,140,0.25);color:var(--pink-light);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:5px 14px;border-radius:50px;display:inline-block;margin-bottom:14px;">
                        ⚡ Flash Sale
                    </span>
                    <h2 style="font-family:var(--font-display);font-size:2.4rem;color:white;font-weight:400;margin-bottom:10px;">
                        Up to <span style="color:var(--pink-light);">30% Off</span><br>Skincare Bundles
                    </h2>
                    <p style="color:rgba(255,255,255,0.65);font-size:14px;margin-bottom:24px;">
                        Grab our best skincare bundles at amazing discounts. Offer ends tonight!
                    </p>
                    <a href="products.php?category=skincare" class="btn btn-pink-lg">Shop Sale</a>
                </div>
                <div class="col-lg-5 text-center text-lg-end">
                    <p style="color:rgba(255,255,255,0.5);font-size:11px;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;">Offer Ends In</p>
                    <div class="promo-countdown d-flex justify-content-center justify-content-lg-end" id="promoCountdown">
                        <div class="countdown-unit">
                            <div class="countdown-num">12</div>
                            <div class="countdown-label">Hrs</div>
                        </div>
                        <div class="countdown-unit">
                            <div class="countdown-num">45</div>
                            <div class="countdown-label">Min</div>
                        </div>
                        <div class="countdown-unit">
                            <div class="countdown-num">00</div>
                            <div class="countdown-label">Sec</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== NEW ARRIVALS ===== -->
<section class="section-pad" style="background:var(--pink-soft);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="section-eyebrow">Fresh In</span>
                <h2 class="section-title mb-0">New Arrivals</h2>
                <div class="section-divider left"></div>
            </div>
            <a href="products.php" class="btn btn-outline-pink">All Products <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($newArrivals as $p): ?>
            <div class="col-6 col-md-4 col-lg-3 reveal">
                <?php include 'includes/product_card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section-pad">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow">Reviews</span>
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <?php
            $testimonials = [
                ['name'=>'Jane W.','city'=>'Nairobi','text'=>'The Vitamin C serum is absolutely amazing! My skin has never looked better. Fast delivery and excellent packaging too.','rating'=>5,'init'=>'JW'],
                ['name'=>'Amina H.','city'=>'Mombasa','text'=>'I ordered the Pink Blossom perfume and it smells divine. Got so many compliments at work. Will definitely order again!','rating'=>5,'init'=>'AH'],
                ['name'=>'Cynthia M.','city'=>'Kisumu','text'=>'Best beauty shop in Kenya! The argan oil serum transformed my hair from frizzy to silky smooth in just two weeks.','rating'=>5,'init'=>'CM'],
            ];
            foreach ($testimonials as $t):
            ?>
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <div class="product-rating mb-2">
                        <?= str_repeat('<i class="bi bi-star-fill"></i>', $t['rating']) ?>
                    </div>
                    <p class="testimonial-text"><?= $t['text'] ?></p>
                    <div class="testimonial-author">
                        <div class="reviewer-avatar"><?= $t['init'] ?></div>
                        <div>
                            <div class="reviewer-name"><?= $t['name'] ?></div>
                            <div class="reviewer-sub"><?= $t['city'] ?>, Kenya</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== NEWSLETTER ===== -->
<section style="background:linear-gradient(135deg,#E91E8C,#C2176F);padding:60px 0;">
    <div class="container text-center">
        <h2 style="font-family:var(--font-display);color:white;font-size:2.2rem;margin-bottom:10px;">Join the Beauty Club</h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:28px;font-size:15px;">Subscribe to get exclusive deals, beauty tips, and new arrivals straight to your inbox.</p>
        <form class="d-flex gap-2 justify-content-center flex-wrap" style="max-width:480px;margin:0 auto;">
            <input type="email" placeholder="Enter your email address" class="form-control" style="border-radius:12px;border:none;padding:12px 18px;flex:1;">
            <button type="submit" class="btn" style="background:var(--black);color:white;border-radius:12px;padding:12px 24px;font-weight:600;">
                Subscribe
            </button>
        </form>
        <p style="color:rgba(255,255,255,0.6);font-size:12px;margin-top:12px;">No spam, unsubscribe anytime.</p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
