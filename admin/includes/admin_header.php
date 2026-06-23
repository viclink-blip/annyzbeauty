<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdminLogin();
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? sanitize($adminTitle) . ' | ' : '' ?>Annyzbeauty Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=DM+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-icon brand-icon-sm"><i class="bi bi-flower1"></i></div>
            <div>
                <span style="color:white;font-family:var(--font-display);font-size:1.1rem;font-weight:600;">
                    Annyz<span style="color:var(--pink-light);">beauty</span>
                </span>
                <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:1px;">ADMIN PANEL</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="dashboard.php" class="sidebar-link <?= $currentAdmin==='dashboard.php'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <div class="sidebar-section">Catalogue</div>
        <a href="products.php" class="sidebar-link <?= $currentAdmin==='products.php'?'active':'' ?>">
            <i class="bi bi-bag-fill"></i> Products
        </a>
        <a href="product_add.php" class="sidebar-link <?= $currentAdmin==='product_add.php'||$currentAdmin==='product_edit.php'?'active':'' ?>">
            <i class="bi bi-plus-circle-fill"></i> Add Product
        </a>
        <a href="categories.php" class="sidebar-link <?= $currentAdmin==='categories.php'?'active':'' ?>">
            <i class="bi bi-grid-fill"></i> Categories
        </a>
        <div class="sidebar-section">Sales</div>
        <a href="orders.php" class="sidebar-link <?= $currentAdmin==='orders.php'?'active':'' ?>">
            <i class="bi bi-receipt"></i> Orders
        </a>
        <a href="customers.php" class="sidebar-link <?= $currentAdmin==='customers.php'?'active':'' ?>">
            <i class="bi bi-people-fill"></i> Customers
        </a>
        <div class="sidebar-section">Account</div>
        <a href="<?= SITE_URL ?>" class="sidebar-link" target="_blank">
            <i class="bi bi-shop"></i> View Store
        </a>
        <a href="logout.php" class="sidebar-link" style="color:rgba(255,80,80,0.8);">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>

<!-- Admin Main Wrapper -->
<div class="admin-main">
    <!-- Topbar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-lg-none">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 fw-bold"><?= $adminTitle ?? 'Dashboard' ?></h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span style="font-size:13px;color:var(--mid-grey);">
                <i class="bi bi-person-circle me-1"></i>
                <?= sanitize($_SESSION['admin_name'] ?? 'Admin') ?>
            </span>
        </div>
    </div>
    <!-- Flash -->
    <?php $flash = getFlashMessage(); if ($flash): ?>
    <div class="mx-4 mt-3">
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?> alert-dismissible fade show">
            <?= sanitize($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    <!-- Content starts -->
    <div class="admin-content">
