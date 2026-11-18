<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Lindungi akses khusus Penjual / Admin
requireAdmin();

$pageTitle = 'Dashboard Kelola Produk Penjual';

// Statistik Ringkas
$statProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, SUM(stock) as total_stock FROM products"));
$statOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue FROM orders"));

// Filter Pencarian di Admin
$adminSearch = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = "1=1";
$params = [];
$types = "";

if (!empty($adminSearch)) {
    $where .= " AND (p.name LIKE ? OR p.brand LIKE ? OR c.name LIKE ?)";
    $like = "%$adminSearch%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE $where
        ORDER BY p.id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mb-5">
    <!-- Admin Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-1 rounded-pill fw-semibold mb-2">
                <i class="bi bi-shield-lock-fill me-1"></i> PANEL PENJUAL / ADMIN
            </span>
            <h2 class="fw-bold text-white mb-0">Manajemen Produk Raket Badminton</h2>
            <small class="text-secondary">Kelola katalog, harga, stok, dan spesifikasi raket secara real-time</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-info rounded-pill px-3">
                <i class="bi bi-receipt-cutoff me-1"></i> Daftar Transaksi (<?= $statOrders['total_orders'] ?? 0 ?>)
            </a>
            <a href="<?= BASE_URL ?>/admin/product_add.php" class="btn btn-primary-custom rounded-pill px-4">
                <i class="bi bi-plus-circle-fill me-2"></i> Tambah Raket Baru
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-card border-0 rounded-4 p-3 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-25 text-primary fs-3">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <small class="text-secondary d-block">Total Model Raket</small>
                    <h3 class="fw-bold text-white mb-0"><?= $statProducts['total'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-card border-0 rounded-4 p-3 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-25 text-warning fs-3">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <small class="text-secondary d-block">Total Unit Stok</small>
                    <h3 class="fw-bold text-white mb-0"><?= $statProducts['total_stock'] ?? 0 ?> <span class="fs-6 fw-normal text-secondary">unit</span></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-card border-0 rounded-4 p-3 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-25 text-info fs-3">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <div>
                    <small class="text-secondary d-block">Pesanan Masuk</small>
                    <h3 class="fw-bold text-white mb-0"><?= $statOrders['total_orders'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-card border-0 rounded-4 p-3 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-25 text-success fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <small class="text-secondary d-block">Total Omset Pesanan</small>
                    <h3 class="fw-bold text-white mb-0 fs-5"><?= formatRupiah($statOrders['total_revenue'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar Pencarian Data Produk -->
    <div class="card bg-card border-0 rounded-4 p-3 shadow-sm mb-4">
        <form method="GET" action="<?= BASE_URL ?>/admin/index.php" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control form-control-custom" placeholder="Cari berdasarkan nama raket, brand, atau kategori..." value="<?= htmlspecialchars($adminSearch) ?>">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom flex-grow-1">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <?php if (!empty($adminSearch)): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Data CRUD Produk -->
    <div class="card bg-card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Gambar</th>
                        <th>Nama Raket & Brand</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th class="text-center">Stok</th>
                        <th>Spesifikasi Teknis</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox display-4 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada produk raket badminton atau tidak ditemukan dengan kata kunci '<?= htmlspecialchars($adminSearch) ?>'.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?= getProductImageUrl($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="rounded-3" style="width: 50px; height: 50px; object-fit: contain; background: #0b1120; padding: 4px;">
                                </td>
                                <td>
                                    <div class="fw-bold text-white mb-1"><?= htmlspecialchars($p['name']) ?></div>
                                    <span class="badge badge-custom-brand"><?= htmlspecialchars($p['brand']) ?></span>
                                    <?php if ($p['is_featured']): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Unggulan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?= htmlspecialchars($p['category_name'] ?? 'Umum') ?></span>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= formatRupiah($p['price']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['stock'] > 5): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50"><?= $p['stock'] ?> unit</span>
                                    <?php elseif ($p['stock'] > 0): ?>
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50"><?= $p['stock'] ?> unit</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-secondary">
                                        <div><strong class="text-white">Grip:</strong> <?= htmlspecialchars($p['weight_grip']) ?> &bull; <strong class="text-white">Max:</strong> <?= htmlspecialchars($p['tension_lbs']) ?></div>
                                        <div><?= getBadgeBalance($p['balance_point']) ?> <?= getBadgeFlex($p['flexibility']) ?></div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/detail.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-outline-info" title="Lihat Tampilan Toko">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/product_edit.php?id=<?= $p['id'] ?>" class="btn btn-outline-warning" title="Edit Data Raket">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/product_delete.php?id=<?= $p['id'] ?>" class="btn btn-outline-danger" title="Hapus Raket" onclick="return confirm('Apakah Anda yakin ingin menghapus raket \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
