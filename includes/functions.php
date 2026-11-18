<?php
/**
 * Helper Functions - SmashZone Badminton Marketplace
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Format Angka ke Format Rupiah
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Sanitasi Input Form
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Dapatkan Total Jumlah Item di Keranjang
 */
function getCartCount() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (int)($item['quantity'] ?? 1);
    }
    return $total;
}

/**
 * Dapatkan Total Harga Seluruh Item di Keranjang
 */
function getCartTotal() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $price = (float)($item['price'] ?? 0);
        $qty = (int)($item['quantity'] ?? 1);
        $total += ($price * $qty);
    }
    return $total;
}

/**
 * Set Pesan Notifikasi Flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Tampilkan Pesan Notifikasi Flash
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        $icon = 'info-circle-fill';
        if ($type === 'success') $icon = 'check-circle-fill';
        if ($type === 'danger') $icon = 'exclamation-octagon-fill';
        if ($type === 'warning') $icon = 'exclamation-triangle-fill';

        echo "
        <div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center shadow-sm gap-2' role='alert'>
            <i class='bi bi-{$icon} fs-5'></i>
            <div>{$message}</div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        unset($_SESSION['flash']);
    }
}

/**
 * Dapatkan Path Gambar Produk
 */
function getProductImageUrl($imageName) {
    global $isLocal;
    $base = defined('BASE_URL') ? BASE_URL : '';
    
    // Jika berupa URL eksternal (http:// atau https://)
    if (filter_var($imageName, FILTER_VALIDATE_URL)) {
        return $imageName;
    }

    $imagePath = __DIR__ . '/../assets/images/products/' . $imageName;
    if (!empty($imageName) && file_exists($imagePath)) {
        return $base . '/assets/images/products/' . $imageName;
    }

    // Default Fallback
    return $base . '/assets/images/products/default_racket.svg';
}

/**
 * Badge Keseimbangan Raket (Balance Point)
 */
function getBadgeBalance($balance) {
    $bal = strtolower($balance);
    if (strpos($bal, 'heavy') !== false) {
        return "<span class='badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50'><i class='bi bi-fire me-1'></i>" . htmlspecialchars($balance) . "</span>";
    } elseif (strpos($bal, 'light') !== false) {
        return "<span class='badge bg-info bg-opacity-25 text-info border border-info border-opacity-50'><i class='bi bi-lightning-charge-fill me-1'></i>" . htmlspecialchars($balance) . "</span>";
    } else {
        return "<span class='badge bg-success bg-opacity-25 text-success border border-success border-opacity-50'><i class='bi bi-bullseye me-1'></i>" . htmlspecialchars($balance) . "</span>";
    }
}

/**
 * Badge Fleksibilitas Shaft
 */
function getBadgeFlex($flex) {
    $fl = strtolower($flex);
    if (strpos($fl, 'stiff') !== false) {
        return "<span class='badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50'><i class='bi bi-shield-fill-check me-1'></i>" . htmlspecialchars($flex) . "</span>";
    } else {
        return "<span class='badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50'><i class='bi bi-arrow-repeat me-1'></i>" . htmlspecialchars($flex) . "</span>";
    }
}

/**
 * Format Status Pesanan
 */
function getOrderStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Menunggu Pembayaran</span>';
        case 'paid':
            return '<span class="badge bg-info text-dark"><i class="bi bi-wallet2 me-1"></i>Sudah Dibayar</span>';
        case 'shipped':
            return '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Sedang Dikirim</span>';
        case 'completed':
            return '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
        case 'cancelled':
            return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Dibatalkan</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

/**
 * Cek apakah pengguna saat ini sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Dapatkan data pengguna yang sedang login
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Cek apakah pengguna saat ini adalah Penjual / Admin
 */
function isAdmin() {
    if (!isLoggedIn()) return false;
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['seller', 'admin']);
}

/**
 * Lindungi halaman yang mewajibkan pengguna login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $redirect = !empty($currentUri) ? '?redirect=' . urlencode($currentUri) : '';
        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $base . '/login.php' . $redirect);
        exit;
    }
}

/**
 * Lindungi halaman khusus Penjual / Admin
 */
function requireAdmin() {
    $base = defined('BASE_URL') ? BASE_URL : '';
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Silakan login sebagai Penjual/Admin untuk mengakses panel ini.');
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $redirect = !empty($currentUri) ? '?redirect=' . urlencode($currentUri) : '';
        header('Location: ' . $base . '/login.php' . $redirect);
        exit;
    }
    if (!isAdmin()) {
        setFlashMessage('danger', 'Akses ditolak! Anda login sebagai Pembeli, halaman ini khusus untuk Penjual/Admin.');
        header('Location: ' . $base . '/index.php');
        exit;
    }
}
?>