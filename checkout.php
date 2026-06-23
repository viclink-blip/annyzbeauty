<?php
$pageTitle = 'Checkout';
require_once 'includes/config.php';
requireLogin();
$pdo = getDB();
$uid = $_SESSION['user_id'];

// Fetch cart
$stmt = $pdo->prepare("SELECT c.*, p.name, p.image, p.price, p.sale_price, p.stock_quantity FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

if (empty($items)) { redirect(SITE_URL . '/cart.php'); }

$subtotal = 0;
foreach ($items as $item) {
    $price = (!empty($item['sale_price']) && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
$total    = $subtotal + $shipping;

// Load user info
$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? 'Kenya');
    $payment = $_POST['payment_method'] ?? 'mpesa';
    $notes   = trim($_POST['notes'] ?? '');

    if (empty($address)) $errors[] = 'Shipping address is required.';
    if (empty($city))    $errors[] = 'City is required.';

    if (empty($errors)) {
        // Generate unique order number
        do {
            $orderNum = generateOrderNumber();
            $exists   = $pdo->prepare("SELECT id FROM orders WHERE order_number=?");
            $exists->execute([$orderNum]);
        } while ($exists->fetch());

        // Create order
        $ins = $pdo->prepare("INSERT INTO orders (order_number,user_id,subtotal,shipping_fee,total,payment_method,shipping_address,shipping_city,shipping_country,notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $ins->execute([$orderNum,$uid,$subtotal,$shipping,$total,$payment,$address,$city,$country,$notes]);
        $orderId = $pdo->lastInsertId();

        // Insert order items
        $insItem = $pdo->prepare("INSERT INTO order_items (order_id,product_id,product_name,quantity,unit_price,total_price) VALUES (?,?,?,?,?,?)");
        foreach ($items as $item) {
            $unitPrice = (!empty($item['sale_price']) && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
            $insItem->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $unitPrice, $unitPrice * $item['quantity']]);
            // Reduce stock
            $pdo->prepare("UPDATE products SET stock_quantity=stock_quantity-? WHERE id=?")->execute([$item['quantity'],$item['product_id']]);
        }

        // Clear cart
        $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$uid]);

        flashMessage('success', "Order #{$orderNum} placed successfully! We'll contact you shortly. 🎉");
        redirect(SITE_URL . '/orders.php?new=' . $orderId);
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol></nav>
        <h1>Checkout</h1>
    </div>
</div>

<div class="container py-5">
    <?php if ($errors): ?>
    <div class="alert alert-danger mb-4">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST">
    <div class="row g-4">
        <!-- Left: Shipping Details -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-xl mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="font-family:var(--font-display);font-size:1.3rem;">
                        <i class="bi bi-geo-alt-fill text-pink-color me-2"></i>Shipping Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['first_name']) ?>" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['last_name']) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. 123 Moi Avenue, Westlands"
                                   value="<?= sanitize($user['address'] ?? '') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="Nairobi"
                                   value="<?= sanitize($user['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Country</label>
                            <select name="country" class="form-select">
                                <option value="Kenya" selected>Kenya</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Tanzania">Tanzania</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Order Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any special delivery instructions..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card border-0 shadow-sm rounded-xl">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="font-family:var(--font-display);font-size:1.3rem;">
                        <i class="bi bi-credit-card-fill text-pink-color me-2"></i>Payment Method
                    </h5>
                    <div class="row g-3">
                        <?php
                        $methods = [
                            'mpesa'           => ['icon'=>'bi-phone-fill','label'=>'M-Pesa','sub'=>'Pay via M-Pesa Paybill/Till'],
                            'paypal'          => ['icon'=>'bi-paypal','label'=>'PayPal','sub'=>'Pay via PayPal (masilavincent32@gmail.com)'],
                            'cash_on_delivery'=> ['icon'=>'bi-cash-stack','label'=>'Cash on Delivery','sub'=>'Pay when you receive your order'],
                        ];
                        foreach ($methods as $val => $m):
                        ?>
                        <div class="col-12">
                            <label class="d-flex align-items-center gap-3 p-3 border rounded-3 cursor-pointer"
                                   style="cursor:pointer;border-color:var(--border) !important;transition:all .2s;"
                                   onmouseover="this.style.borderColor='var(--pink)'"
                                   onmouseout="this.style.borderColor=''">
                                <input type="radio" name="payment_method" value="<?= $val ?>"
                                       <?= $val==='mpesa' ? 'checked' : '' ?>>
                                <i class="bi <?= $m['icon'] ?> fs-5 text-pink-color"></i>
                                <div>
                                    <div class="fw-600"><?= $m['label'] ?></div>
                                    <div style="font-size:12px;color:var(--mid-grey);"><?= $m['sub'] ?></div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="alert mt-3 mb-0" style="background:var(--pink-soft);border:1px solid var(--border);border-radius:10px;font-size:13px;">
                        <i class="bi bi-info-circle text-pink-color me-1"></i>
                        After placing your order you will receive payment instructions via email and WhatsApp. Your order will be confirmed once payment is verified.
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary -->
        <div class="col-lg-5">
            <div class="order-summary-card">
                <h5 class="fw-bold mb-4" style="font-family:var(--font-display);font-size:1.3rem;">Your Order</h5>

                <?php foreach ($items as $item):
                    $unitPrice = (!empty($item['sale_price']) && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
                    $lineTotal = $unitPrice * $item['quantity'];
                    $img = 'assets/images/products/' . ($item['image'] ?? '');
                ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= $img ?>" alt="<?= sanitize($item['name']) ?>"
                         style="width:52px;height:52px;object-fit:cover;border-radius:10px;background:white;"
                         onerror="this.src='https://placehold.co/52x52/FFF0F7/E91E8C?text=IMG'">
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;"><?= sanitize($item['name']) ?></div>
                        <div style="font-size:12px;color:var(--mid-grey);">Qty: <?= $item['quantity'] ?></div>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:var(--pink);"><?= formatPrice((float)$lineTotal) ?></div>
                </div>
                <?php endforeach; ?>

                <hr style="border-color:var(--border);">
                <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?= $shipping === 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span style="color:var(--pink);"><?= formatPrice($total) ?></span>
                </div>

                <button type="submit" class="btn btn-pink-lg w-100 mt-4">
                    <i class="bi bi-lock-fill me-2"></i>Place Order — <?= formatPrice($total) ?>
                </button>
                <p class="text-center mt-2 mb-0" style="font-size:12px;color:var(--mid-grey);">
                    <i class="bi bi-shield-check me-1"></i>Secure & encrypted checkout
                </p>
            </div>
        </div>
    </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
