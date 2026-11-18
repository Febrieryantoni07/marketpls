<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = getCurrentUser();
$userIsLoggedIn = isLoggedIn();
$userIsAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>SmashZone | Marketplace Raket Badminton Original</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #0284c7, #10b981); width: 40px; height: 40px;">
                    <i class="bi bi-trophy-fill text-white fs-5"></i>
                </div>
                <div>
                    <span class="brand-title">SmashZone</span>
                    <small class="d-block text-secondary" style="font-size: 0.68rem; letter-spacing: 1px; margin-top: -4px;">BADMINTON MARKETPLACE</small>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Items -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage === 'index.php') ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/index.php">
                            <i class="bi bi-grid-fill me-1"></i> Katalog Raket
                        </a>
                    </li>
                    <?php if ($userIsAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'admin') !== false) ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/admin/index.php">
                                <i class="bi bi-gear-wide-connected me-1"></i> Kelola Produk (Admin CRUD)
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/setup.php">
                            <i class="bi bi-database-check me-1"></i> Status DB
                        </a>
                    </li>
                </ul>

                <!-- Right Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Shopping Cart Button -->
                    <a href="<?= BASE_URL ?>/cart.php" class="btn btn-dark rounded-pill px-3 py-2 position-relative border border-secondary border-opacity-50 text-white d-flex align-items-center gap-2 me-1" title="Keranjang Belanja">
                        <i class="bi bi-bag-check-fill text-warning fs-5"></i>
                        <span class="d-none d-sm-inline fw-semibold">Keranjang</span>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- User Auth Section -->
                    <?php if ($userIsLoggedIn): ?>
                        <!-- User Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-dark rounded-pill px-3 py-2 border border-secondary border-opacity-50 text-white d-flex align-items-center gap-2 dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle bg-primary bg-opacity-25 text-info d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="fw-semibold small d-none d-md-inline"><?= htmlspecialchars($currentUser['name']) ?></span>
                                <span class="badge <?= $userIsAdmin ? 'bg-warning text-dark' : 'bg-info text-dark' ?> rounded-pill" style="font-size: 0.65rem;">
                                    <?= $userIsAdmin ? 'ADMIN' : 'USER' ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark bg-card border-secondary shadow-lg rounded-3 py-2" aria-labelledby="userDropdown" style="min-width: 230px;">
                                <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                                    <strong class="text-white d-block small"><?= htmlspecialchars($currentUser['name']) ?></strong>
                                    <small class="text-secondary" style="font-size: 0.75rem;"><?= htmlspecialchars($currentUser['email']) ?></small>
                                </li>
                                
                                <?php if ($userIsAdmin): ?>
                                    <li><h6 class="dropdown-header text-uppercase text-warning fw-bold" style="font-size: 0.7rem;">Menu Penjual / Admin</h6></li>
                                    <li>
                                        <a class="dropdown-item py-2 small" href="<?= BASE_URL ?>/admin/index.php">
                                            <i class="bi bi-speedometer2 text-info me-2"></i> Dashboard Kelola Produk
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2 small" href="<?= BASE_URL ?>/admin/product_add.php">
                                            <i class="bi bi-plus-circle text-success me-2"></i> Tambah Raket Baru
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2 small" href="<?= BASE_URL ?>/admin/orders.php">
                                            <i class="bi bi-receipt-cutoff text-warning me-2"></i> Kelola Transaksi Masuk
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                                <?php endif; ?>

                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= BASE_URL ?>/my_orders.php">
                                        <i class="bi bi-clock-history text-info me-2"></i> Riwayat Pesanan Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 small" href="<?= BASE_URL ?>/cart.php">
                                        <i class="bi bi-bag-check text-warning me-2"></i> Keranjang Belanja (<?= $cartCount ?>)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                                <li>
                                    <a class="dropdown-item py-2 small text-danger" href="<?= BASE_URL ?>/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar / logout?')">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login & Register Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-semibold">
                                <i class="bi bi-person-plus-fill me-1"></i> Daftar
                            </a>
                            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary-custom btn-sm rounded-pill px-3 fw-bold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container with Flash Message -->
    <main class="py-4">
        <div class="container">
            <?php displayFlashMessage(); ?>
        </div>
