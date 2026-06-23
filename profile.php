<?php
$pageTitle = 'My Profile';
require_once 'includes/config.php';
requireLogin();
$pdo = getDB();
$uid = $_SESSION['user_id'];

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

$errors = []; $success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first   = trim($_POST['first_name'] ?? '');
    $last    = trim($_POST['last_name']  ?? '');
    $phone   = trim($_POST['phone']      ?? '');
    $address = trim($_POST['address']    ?? '');
    $city    = trim($_POST['city']       ?? '');
    $country = trim($_POST['country']    ?? 'Kenya');
    $newPwd  = $_POST['new_password']    ?? '';
    $confirm = $_POST['confirm_password']?? '';

    if (empty($first)) $errors[] = 'First name is required.';
    if (!empty($newPwd)) {
        if (strlen($newPwd) < 8) $errors[] = 'New password must be at least 8 characters.';
        if ($newPwd !== $confirm)  $errors[] = 'Passwords do not match.';
        if (!password_verify($_POST['current_password'] ?? '', $user['password'])) $errors[] = 'Current password is incorrect.';
    }

    if (empty($errors)) {
        if (!empty($newPwd)) {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET first_name=?,last_name=?,phone=?,address=?,city=?,country=?,password=? WHERE id=?")
                ->execute([$first,$last,$phone,$address,$city,$country,$hash,$uid]);
        } else {
            $pdo->prepare("UPDATE users SET first_name=?,last_name=?,phone=?,address=?,city=?,country=? WHERE id=?")
                ->execute([$first,$last,$phone,$address,$city,$country,$uid]);
        }
        $_SESSION['user_name'] = $first;
        $success = true;
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
    }
}

// Stats
$orderCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?"); $orderCount->execute([$uid]);
$orderCount = $orderCount->fetchColumn();
$totalSpent = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=? AND payment_status='paid'"); $totalSpent->execute([$uid]);
$totalSpent = $totalSpent->fetchColumn();

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol></nav>
        <h1>My Profile</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Left: Profile Info Card -->
        <div class="col-lg-3">
            <div class="text-center" style="background:var(--pink-soft);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;">
                <div class="reviewer-avatar mx-auto mb-3" style="width:72px;height:72px;font-size:24px;background:linear-gradient(135deg,var(--pink),var(--pink-dark));">
                    <?= strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p style="font-size:12px;color:var(--mid-grey);"><?= sanitize($user['email']) ?></p>
                <p style="font-size:12px;color:var(--mid-grey);">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                <hr style="border-color:var(--border);">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:600;color:var(--pink);"><?= $orderCount ?></div>
                        <div style="font-size:11px;color:var(--mid-grey);">Orders</div>
                    </div>
                    <div class="col-6">
                        <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:600;color:var(--pink);"><?= formatPrice((float)$totalSpent) ?></div>
                        <div style="font-size:11px;color:var(--mid-grey);">Spent</div>
                    </div>
                </div>
                <hr style="border-color:var(--border);">
                <nav class="d-flex flex-column gap-1">
                    <a href="profile.php" class="btn btn-pink btn-sm">Edit Profile</a>
                    <a href="orders.php" class="btn btn-outline-pink btn-sm">My Orders</a>
                    <a href="logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
                </nav>
            </div>
        </div>

        <!-- Right: Edit Form -->
        <div class="col-lg-9">
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="bi bi-check-circle me-2"></i>Profile updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if ($errors): ?>
            <div class="alert alert-danger mb-4">
                <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Personal Info -->
                <div class="card border-0 shadow-sm rounded-xl mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4" style="font-family:var(--font-display);">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= sanitize($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= sanitize($user['last_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" readonly style="background:#f8f8f8;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="+254 7XX XXX XXX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card border-0 shadow-sm rounded-xl mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4" style="font-family:var(--font-display);">Delivery Address</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="address" class="form-control" value="<?= sanitize($user['address'] ?? '') ?>" placeholder="123 Moi Avenue">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= sanitize($user['city'] ?? '') ?>" placeholder="Nairobi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select name="country" class="form-select">
                                    <option <?= ($user['country']??'Kenya')==='Kenya'?'selected':'' ?>>Kenya</option>
                                    <option <?= ($user['country']??'')==='Uganda'?'selected':'' ?>>Uganda</option>
                                    <option <?= ($user['country']??'')==='Tanzania'?'selected':'' ?>>Tanzania</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card border-0 shadow-sm rounded-xl mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1" style="font-family:var(--font-display);">Change Password</h5>
                        <p style="font-size:13px;color:var(--mid-grey);" class="mb-4">Leave blank to keep your current password.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Min. 8 chars">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-pink-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
