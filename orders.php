<?php
$pageTitle = 'My Orders';
require_once 'includes/config.php';
requireLogin();
$pdo = getDB();
$uid = $_SESSION['user_id'];

// Single order detail view
$viewOrder = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
    $stmt->execute([intval($_GET['view']), $uid]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $itemsStmt = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        $itemsStmt->execute([$viewOrder['id']]);
        $viewOrder['items'] = $itemsStmt->fetchAll();
    }
}

// All orders list
$stmt = $pdo->prepare("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) as item_count FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC");
$stmt->execute([$uid]);
$orders = $stmt->fetchAll();

$statusClass = ['pending'=>'status-pending','processing'=>'status-processing','shipped'=>'status-shipped','delivered'=>'status-delivered','cancelled'=>'status-cancelled'];

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">My Orders</li>
        </ol></nav>
        <h1>My Orders</h1>
    </div>
</div>

<div class="container py-5">

<?php if ($viewOrder): ?>
<!-- ORDER DETAIL -->
<div class="mb-3">
    <a href="orders.php" class="btn btn-outline-pink btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Orders</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-xl">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold" style="font-family:var(--font-display);">Order #<?= sanitize($viewOrder['order_number']) ?></h5>
                    <span class="status-badge <?= $statusClass[$viewOrder['status']] ?? '' ?>"><?= ucfirst($viewOrder['status']) ?></span>
                </div>
                <table class="table" style="font-size:14px;">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($viewOrder['items'] as $item):
                        $img = 'assets/images/products/' . ($item['image'] ?? '');
                    ?>
                    <tr>
                        <td class="d-flex align-items-center gap-2">
                            <img src="<?= $img ?>" style="width:42px;height:42px;object-fit:cover;border-radius:8px;"
                                 onerror="this.src='https://placehold.co/42x42/FFF0F7/E91E8C?text=P'">
                            <?= sanitize($item['product_name']) ?>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= formatPrice((float)$item['unit_price']) ?></td>
                        <td class="fw-bold text-pink-color"><?= formatPrice((float)$item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-end border-top pt-3">
                    <div style="font-size:13px;color:var(--mid-grey);">Shipping: <?= formatPrice((float)$viewOrder['shipping_fee']) ?></div>
                    <div style="font-size:1.1rem;font-weight:700;color:var(--pink);">Total: <?= formatPrice((float)$viewOrder['total']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="order-summary-card">
            <h6 class="fw-bold mb-3">Order Info</h6>
            <div style="font-size:13px;" class="d-flex flex-column gap-2">
                <div><strong>Date:</strong> <?= date('d M Y', strtotime($viewOrder['created_at'])) ?></div>
                <div><strong>Payment:</strong> <?= ucwords(str_replace('_',' ',$viewOrder['payment_method'])) ?></div>
                <div><strong>Status:</strong> <span class="status-badge <?= $statusClass[$viewOrder['payment_status']] ?? '' ?>"><?= ucfirst($viewOrder['payment_status']) ?></span></div>
                <div><strong>Ship to:</strong> <?= sanitize($viewOrder['shipping_address']) ?>, <?= sanitize($viewOrder['shipping_city']) ?></div>
                <?php if ($viewOrder['notes']): ?>
                <div><strong>Notes:</strong> <?= sanitize($viewOrder['notes']) ?></div>
                <?php endif; ?>
            </div>
            <a href="https://wa.me/254758556523?text=Hi+Annyzbeauty,+I+have+a+query+about+order+<?= urlencode($viewOrder['order_number']) ?>" 
               class="btn btn-outline-pink btn-sm w-100 mt-3" target="_blank">
                <i class="bi bi-whatsapp me-1"></i>Query via WhatsApp
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ORDER LIST -->
<?php if (empty($orders)): ?>
<div class="text-center py-5">
    <i class="bi bi-bag-check" style="font-size:3.5rem;color:var(--pink-light);"></i>
    <h4 class="mt-3">No orders yet</h4>
    <p class="text-muted">You haven't placed any orders. Start shopping!</p>
    <a href="products.php" class="btn btn-pink-lg">Shop Now</a>
</div>
<?php else: ?>

<?php if (isset($_GET['new'])): ?>
<div class="alert" style="background:var(--pink-soft);border:1px solid var(--border);border-radius:12px;" role="alert">
    <i class="bi bi-check-circle-fill text-pink-color me-2"></i>
    <strong>Order placed!</strong> We'll send you payment instructions via email & WhatsApp. You can track your order here.
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-xl">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead><tr>
                    <th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong><?= sanitize($o['order_number']) ?></strong></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><?= $o['item_count'] ?> item<?= $o['item_count'] != 1 ? 's' : '' ?></td>
                    <td class="fw-bold" style="color:var(--pink);"><?= formatPrice((float)$o['total']) ?></td>
                    <td><?= ucwords(str_replace('_',' ',$o['payment_method'])) ?></td>
                    <td><span class="status-badge <?= $statusClass[$o['status']] ?? '' ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-sm btn-outline-pink">View</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
