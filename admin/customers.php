<?php
$adminTitle = 'Customers';
require_once 'includes/admin_header.php';
$pdo = getDB();

// Toggle active
if (isset($_GET['toggle'])) {
    $cid  = intval($_GET['toggle']);
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id=?");
    $stmt->execute([$cid]);
    $cur  = $stmt->fetchColumn();
    $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$cur ? 0 : 1, $cid]);
    flashMessage('success', 'Customer status updated.');
    redirect(SITE_URL . '/admin/customers.php');
}

$search  = trim($_GET['search'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 15; $offset = ($page-1)*$perPage;

$where = ['1=1']; $params = [];
if ($search) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params  = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
$whereStr = implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $whereStr");
$total->execute($params); $total = $total->fetchColumn();
$totalPages = ceil($total/$perPage);

$customers = $pdo->prepare("
    SELECT u.*,
           COUNT(o.id) as order_count,
           COALESCE(SUM(o.total),0) as total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id=u.id
    WHERE $whereStr
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$customers->execute($params);
$customers = $customers->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1" style="font-family:var(--font-display);font-size:1.4rem;">Customers</h5>
        <p class="text-muted mb-0" style="font-size:13px;"><?= $total ?> registered customers</p>
    </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm rounded-xl mb-4">
    <div class="card-body p-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:300px;"
                   placeholder="Search by name, email or phone..." value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-sm btn-pink">Search</button>
            <?php if ($search): ?><a href="customers.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-xl">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead><tr>
                    <th style="padding-left:20px;">Customer</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No customers found.</td></tr>
                <?php endif; ?>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td style="padding-left:20px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="reviewer-avatar" style="width:36px;height:36px;font-size:13px;flex-shrink:0;">
                                <?= strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:13.5px;"><?= sanitize($c['first_name'].' '.$c['last_name']) ?></div>
                                <div style="font-size:11px;color:var(--mid-grey);"><?= sanitize($c['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;"><?= sanitize($c['phone'] ?? '—') ?></td>
                    <td>
                        <a href="orders.php?search=<?= urlencode($c['first_name'].' '.$c['last_name']) ?>" style="font-weight:600;color:var(--pink);">
                            <?= $c['order_count'] ?>
                        </a>
                    </td>
                    <td style="font-weight:600;"><?= formatPrice((float)$c['total_spent']) ?></td>
                    <td style="font-size:12px;color:var(--mid-grey);"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <span class="status-badge <?= $c['is_active'] ? 'status-delivered' : 'status-cancelled' ?>">
                            <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a href="customers.php?toggle=<?= $c['id'] ?>"
                           class="btn btn-sm <?= $c['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                           onclick="return confirm('<?= $c['is_active'] ? 'Deactivate' : 'Activate' ?> this customer?')">
                            <?= $c['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
