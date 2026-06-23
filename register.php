<?php
$pageTitle = 'Create Account';
require_once 'includes/config.php';

if (isLoggedIn()) redirect(SITE_URL . '/profile.php');

$errors = [];
$data   = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['first_name'] = trim($_POST['first_name'] ?? '');
    $data['last_name']  = trim($_POST['last_name']  ?? '');
    $data['email']      = trim($_POST['email']      ?? '');
    $data['phone']      = trim($_POST['phone']      ?? '');
    $password           = $_POST['password']  ?? '';
    $confirm            = $_POST['confirm']   ?? '';

    if (empty($data['first_name'])) $errors[] = 'First name is required.';
    if (empty($data['last_name']))  $errors[] = 'Last name is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo = getDB();
        $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare("INSERT INTO users (first_name,last_name,email,phone,password) VALUES (?,?,?,?,?)");
            $ins->execute([$data['first_name'], $data['last_name'], $data['email'], $data['phone'], $hash]);
            $uid = $pdo->lastInsertId();
            $_SESSION['user_id']   = $uid;
            $_SESSION['user_name'] = $data['first_name'];
            $_SESSION['user_email']= $data['email'];
            flashMessage('success', 'Welcome to Annyzbeauty, ' . $data['first_name'] . '! 🌸');
            redirect(SITE_URL . '/index.php');
        }
    }
}
require_once 'includes/header.php';
?>

<div style="background:var(--pink-soft);min-height:calc(100vh - 200px);padding:50px 0;">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="brand-icon mx-auto mb-3"><i class="bi bi-flower1"></i></div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-sub">Join Annyzbeauty and start glowing today ✨</p>
            </div>

            <?php if ($errors): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= sanitize($data['first_name']) ?>" placeholder="Jane" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= sanitize($data['last_name']) ?>" placeholder="Wanjiku" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($data['email']) ?>" placeholder="jane@example.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?= sanitize($data['phone']) ?>" placeholder="+254 7XX XXX XXX">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd" class="form-control" placeholder="Min. 8 characters" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('pwd','eyePwd')">
                                <i id="eyePwd" class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm" id="pwd2" class="form-control" placeholder="Re-enter password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('pwd2','eyePwd2')">
                                <i id="eyePwd2" class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" required id="terms">
                            <label class="form-check-label" for="terms" style="font-size:13px;">
                                I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-pink-lg w-100">Create My Account</button>
                    </div>
                </div>
            </form>

            <p class="text-center mt-4 mb-0" style="font-size:14px;">
                Already have an account? <a href="login.php" class="fw-600" style="color:var(--pink);">Sign In</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text'; icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password'; icon.className = 'bi bi-eye';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
