<?php
$adminTitle = 'Categories';
require_once 'includes/admin_header.php';
$pdo = getDB();

// Delete
if (isset($_GET['delete'])) {
    $catId = intval($_GET['delete']);
    $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=? AND is_active=1");
    $check->execute([$catId]);
    if ($check->fetchColumn() > 0) {
        flashMessage('error', 'Cannot delete: category has active products.');
    } else {
        $pdo->prepare("UPDATE categories SET is_active=0 WHERE id=?")->execute([$catId]);
        flashMessage('success', 'Category deleted.');
    }
    redirect(SITE_URL . '/admin/categories.php');
}

$errors = []; $editCat = null;
if (isset($_GET['edit'])) {
    $editCat = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $editCat->execute([intval($_GET['edit'])]);
    $editCat = $editCat->fetch();
}

// Save / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']        ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $editId= intval($_POST['edit_id']   ?? 0);

    if (!$name) $errors[] = 'Category name is required.';

    if (empty($errors)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        if ($editId) {
            $pdo->prepare("UPDATE categories SET name=?,slug=?,description=? WHERE id=?")->execute([$name,$slug,$desc,$editId]);
            flashMessage('success', 'Category updated.');
        } else {
            $pdo->prepare("INSERT INTO categories (name,slug,description) VALUES (?,?,?)")->execute([$name,$slug,$desc]);
            flashMessage('success', 'Category added.');
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id AND is_active=1) as product_count FROM categories c WHERE c.is_active=1 ORDER BY c.sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0" style="font-family:var(--font-display);font-size:1.4rem;">Categories</h5>
</div>

<div class="row g-4">
    <!-- Category Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-xl">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><?= $editCat ? 'Edit Category' : 'Add New Category' ?></h6>
                <?php if ($errors): ?>
                <div class="alert alert-danger mb-3"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="POST">
                    <?php if ($editCat): ?>
                    <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="<?= sanitize($editCat['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= sanitize($editCat['description'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-pink flex-grow-1">
                            <?= $editCat ? 'Update' : 'Add Category' ?>
                        </button>
                        <?php if ($editCat): ?>
                        <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-xl">
            <div class="card-body p-0">
                <table class="table admin-table mb-0">
                    <thead><tr>
                        <th style="padding-left:20px;">Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($cats as $c): ?>
                    <tr>
                        <td style="padding-left:20px;">
                            <div style="font-weight:600;"><?= sanitize($c['name']) ?></div>
                            <?php if ($c['description']): ?>
                            <div style="font-size:11px;color:var(--mid-grey);"><?= sanitize(substr($c['description'],0,50)) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <td><code style="font-size:12px;"><?= sanitize($c['slug']) ?></code></td>
                        <td>
                            <span class="badge bg-secondary"><?= $c['product_count'] ?></span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete-confirm">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/products.php?category=<?= $c['slug'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
