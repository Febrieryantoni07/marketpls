<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Katalog Raket Badminton Original';

// Tangkap Parameter Filter
$searchKeyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brandFilter = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest';

// Bangun Query Produk
$whereClauses = ["1=1"];
$params = [];
$types = "";

if (!empty($searchKeyword)) {
    $whereClauses[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $like = "%$searchKeyword%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($categoryFilter > 0) {
    $whereClauses[] = "p.category_id = ?";
    $params[] = $categoryFilter;
    $types .= "i";
}

if (!empty($brandFilter)) {
    $whereClauses[] = "p.brand = ?";
    $params[] = $brandFilter;
    $types .= "s";
}

// Pengurutan (Sorting)
$orderBy = "p.id DESC";
if ($sortBy === 'price_asc') {
    $orderBy = "p.price ASC";
} elseif ($sortBy === 'price_desc') {
    $orderBy = "p.price DESC";
} elseif ($sortBy === 'name_asc') {
    $orderBy = "p.name ASC";
} elseif ($sortBy === 'stock_desc') {
    $orderBy = "p.stock DESC";
}

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(" AND ", $whereClauses) . "
        ORDER BY $orderBy";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Ambil Seluruh Kategori untuk Navigasi Filter
$catQuery = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");
$categories = mysqli_fetch_all($catQuery, MYSQLI_ASSOC);

// Ambil Brand Unik untuk Filter Brand
$brandQuery = mysqli_query($conn, "SELECT DISTINCT brand FROM products ORDER BY brand ASC");
$brands = mysqli_fetch_all($brandQuery, MYSQLI_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Hero Banner -->
    <div class="hero-banner mb-5 text-white position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-patch-check-fill me-1"></i> 100% Produk Original & Bergaransi
                </span>
                <h1 class="display-5 fw-extrabold mb-3">
                    Temukan <span class="text-primary">Raket Impian</span> Untuk Performa Terbaik di Lapangan!
                </h1>
                <p class="lead text-secondary mb-4 fs-6">
                    Pilihan raket badminton terlengkap dari Yonex, Victor, Li-Ning, Mizuno, dan Felet. Dilengkapi spesifikasi teknis lengkap: bobot (3U/4U), tarikan maksimal (LBS), titik keseimbangan (Balance Point), dan kelenturan shaft.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#katalogSection" class="btn btn-primary-custom btn-lg px-4 shadow">
                        <i class="bi bi-lightning-charge-fill me-2"></i> Jelajahi Koleksi Raket
                    </a>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-shop-window me-2"></i> Masuk Dashboard Penjual
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center position-relative">
                <img src="<?= BASE_URL ?>/assets/images/products/BADMINTON_LOGO.jpg" alt="Badminton Racket" class="img-fluid rounded-4 shadow-lg" style="max-height: 320px; transform: rotate(0deg);">
            </div>
        </div>

        <!-- Highlight Bar -->
        <div class="row g-3 mt-4 pt-4 border-top border-secondary border-opacity-25">
            <div class="col-md-4 col-sm-6 d-flex align-items-center gap-3">
                <i class="bi bi-shield-lock-fill fs-2 text-warning"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Garansi Keaslian 100%</h6>
                    <small class="text-secondary">Produk resmi berhologram asli</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 d-flex align-items-center gap-3">
                <i class="bi bi-boxes fs-2 text-info"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Pengemasan Ekstra Aman</h6>
                    <small class="text-secondary">Pipa paralon tebal + bubble wrap</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 d-flex align-items-center gap-3">
                <i class="bi bi-speedometer fs-2 text-success"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Pengiriman Cepat</h6>
                    <small class="text-secondary">Dukungan JNE, J&T, & SiCepat</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div id="katalogSection" class="card border-0 bg-card rounded-4 p-4 shadow-sm mb-4">
        <form method="GET" action="<?= BASE_URL ?>/index.php#katalogSection" class="row g-3 align-items-center">
            <!-- Pencarian Kata Kunci -->
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control form-control-custom" placeholder="Cari nama raket, seri, atau brand..." value="<?= htmlspecialchars($searchKeyword) ?>">
                </div>
            </div>

            <!-- Filter Kategori -->
            <div class="col-lg-3 col-md-3 col-sm-6">
                <select name="category" class="form-select form-select-custom">
                    <option value="0">Semua Tipe Karakter</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Brand -->
            <div class="col-lg-2 col-md-3 col-sm-6">
                <select name="brand" class="form-select form-select-custom">
                    <option value="">Semua Brand</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= htmlspecialchars($b['brand']) ?>" <?= ($brandFilter == $b['brand']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['brand']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Urutan (Sort) -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <select name="sort" class="form-select form-select-custom">
                    <option value="latest" <?= ($sortBy === 'latest') ? 'selected' : '' ?>>Terbaru</option>
                    <option value="price_asc" <?= ($sortBy === 'price_asc') ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                    <option value="price_desc" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
                    <option value="name_asc" <?= ($sortBy === 'name_asc') ? 'selected' : '' ?>>Nama A - Z</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-lg-1 col-md-6 col-sm-6 d-grid">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-funnel-fill"></i>
                </button>
            </div>
        </form>

        <!-- Quick Tag Filter Kategori -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top border-secondary border-opacity-25 align-items-center">
            <small class="text-secondary fw-semibold me-2">Karakter Main:</small>
            <a href="<?= BASE_URL ?>/index.php#katalogSection" class="badge rounded-pill text-decoration-none <?= ($categoryFilter == 0) ? 'bg-primary text-white' : 'bg-dark text-secondary border border-secondary' ?> px-3 py-2">
                Semua
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/index.php?category=<?= $cat['id'] ?>#katalogSection" class="badge rounded-pill text-decoration-none <?= ($categoryFilter == $cat['id']) ? 'bg-primary text-white' : 'bg-dark text-secondary border border-secondary' ?> px-3 py-2">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Product Grid Listing -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-white mb-0">Daftar Raket Badminton</h3>
            <small class="text-secondary">Menampilkan <?= count($products) ?> raket pilihan</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/product_add.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Raket Baru
        </a>
    </div>

    <?php if (empty($products)): ?>
        <div class="card bg-card border-0 rounded-4 p-5 text-center my-4">
            <div class="mb-3">
                <i class="bi bi-search text-secondary display-3"></i>
            </div>
            <h4 class="fw-bold text-white">Tidak Ada Raket yang Cocok</h4>
            <p class="text-secondary mb-4">Coba sesuaikan kata kunci atau bersihkan filter pencarian Anda.</p>
            <div>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary-custom px-4">
                    <i class="bi bi-arrow-counterclockwise me-2"></i> Reset Filter
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $item): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="racket-card">
                        <!-- Image Area -->
                        <div class="img-wrapper">
                            <span class="badge badge-custom-brand position-absolute top-0 start-0 m-3 rounded-pill px-2 py-1">
                                <?= htmlspecialchars($item['brand']) ?>
                            </span>
                            <?php if ($item['is_featured']): ?>
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 rounded-pill px-2 py-1 fw-bold">
                                    <i class="bi bi-star-fill"></i> Pilihan Atlet
                                </span>
                            <?php endif; ?>

                            <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="d-block w-100 text-center">
                                <img src="<?= getProductImageUrl($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid">
                            </a>
                        </div>

                        <!-- Card Body -->
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <!-- Category Badge -->
                            <div class="mb-2">
                                <small class="text-info fw-semibold"><?= htmlspecialchars($item['category_name'] ?? 'Badminton') ?></small>
                            </div>

                            <!-- Product Title -->
                            <h5 class="fw-bold text-white mb-2" style="font-size: 1.05rem; line-height: 1.4;">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="text-white text-decoration-none hover-primary">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                            </h5>

                            <!-- Badges Specs -->
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="spec-pill"><i class="bi bi-weight me-1"></i><?= htmlspecialchars($item['weight_grip']) ?></span>
                                <span class="spec-pill"><i class="bi bi-activity me-1"></i><?= htmlspecialchars($item['tension_lbs']) ?></span>
                                <?= getBadgeBalance($item['balance_point']) ?>
                            </div>

                            <!-- Price and Stock -->
                            <div class="mt-auto pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="price-tag"><?= formatRupiah($item['price']) ?></div>
                                    <small class="text-<?= ($item['stock'] > 0) ? 'secondary' : 'danger' ?>">
                                        <?= ($item['stock'] > 0) ? 'Stok: ' . $item['stock'] . ' unit' : 'Stok Habis' ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 mt-3">
                                <div class="btn-group w-100">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="btn btn-outline-secondary btn-sm text-white-50">
                                        <i class="bi bi-info-circle me-1"></i> Detail
                                    </a>
                                    <?php if ($item['stock'] > 0): ?>
                                        <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $item['id'] ?>" class="btn btn-primary-custom btn-sm">
                                            <i class="bi bi-bag-plus-fill me-1"></i> + Keranjang
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Habis</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
