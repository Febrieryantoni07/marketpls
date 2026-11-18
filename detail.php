<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlashMessage('danger', 'Produk tidak valid atau tidak ditemukan.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil Data Produk
$sql = "SELECT p.*, c.name AS category_name, c.description AS category_desc, u.name AS seller_name, u.phone AS seller_phone
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
    setFlashMessage('danger', 'Raket badminton yang Anda cari tidak ditemukan dalam database.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = $product['name'] . ' (' . $product['brand'] . ')';

// Ambil Ulasan Produk
$revSql = "SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC";
$revStmt = mysqli_prepare($conn, $revSql);
mysqli_stmt_bind_param($revStmt, "i", $id);
mysqli_stmt_execute($revStmt);
$revResult = mysqli_stmt_get_result($revStmt);
$reviews = mysqli_fetch_all($revResult, MYSQLI_ASSOC);
mysqli_stmt_close($revStmt);

// Ambil Rekomendasi Raket Terkait (Kategori yang sama)
$relSql = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4";
$relStmt = mysqli_prepare($conn, $relSql);
mysqli_stmt_bind_param($relStmt, "ii", $product['category_id'], $id);
mysqli_stmt_execute($relStmt);
$relResult = mysqli_stmt_get_result($relStmt);
$relatedProducts = mysqli_fetch_all($relResult, MYSQLI_ASSOC);
mysqli_stmt_close($relStmt);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-secondary text-decoration-none"><i class="bi bi-house-door me-1"></i>Katalog</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?category=<?= $product['category_id'] ?>" class="text-secondary text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Main Product Detail Section -->
    <div class="row g-4 mb-5">
        <!-- Kolom Gambar -->
        <div class="col-lg-5">
            <div class="card border-0 bg-card rounded-4 p-4 text-center shadow-lg position-relative">
                <span class="badge badge-custom-brand position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fs-6">
                    <?= htmlspecialchars($product['brand']) ?>
                </span>
                <?php if ($product['is_featured']): ?>
                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-star-fill me-1"></i> Unggulan
                    </span>
                <?php endif; ?>

                <div class="my-4 py-3">
                    <img src="<?= getProductImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded-4" style="max-height: 380px; object-fit: contain;">
                </div>

                <div class="d-flex justify-content-center gap-2 mt-2">
                    <span class="badge bg-dark border border-secondary text-secondary px-3 py-2">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Original 100%
                    </span>
                    <span class="badge bg-dark border border-secondary text-secondary px-3 py-2">
                        <i class="bi bi-shield-check text-primary me-1"></i> Safe Packing
                    </span>
                </div>
            </div>
        </div>

        <!-- Kolom Informasi & Spesifikasi -->
        <div class="col-lg-7">
            <div class="card border-0 bg-card rounded-4 p-4 p-md-5 shadow-lg h-100 d-flex flex-column">
                <div class="mb-3">
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-1 rounded-pill fw-semibold">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </span>
                </div>

                <h2 class="fw-bold text-white mb-2"><?= htmlspecialchars($product['name']) ?></h2>

                <!-- Rating Singkat & Info Penjual -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 text-secondary small">
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                        <span class="text-white fw-bold ms-1">4.9</span>
                        <span class="text-secondary">(<?= count($reviews) ?> Ulasan Pembeli)</span>
                    </div>
                    <span>•</span>
                    <div>
                        <i class="bi bi-shop text-info me-1"></i>
                        Penjual: <strong class="text-white"><?= htmlspecialchars($product['seller_name'] ?? 'SmashZone Official') ?></strong>
                    </div>
                </div>

                <!-- Harga -->
                <div class="d-flex align-items-baseline gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <span class="display-6 fw-bold text-primary"><?= formatRupiah($product['price']) ?></span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i> Tersedia <?= $product['stock'] ?> unit
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 rounded-pill">
                            <i class="bi bi-x-circle-fill me-1"></i> Stok Habis
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Grid Spesifikasi Raket Badminton -->
                <h6 class="fw-bold text-white-50 text-uppercase letter-spacing-1 mb-3">Spesifikasi Badminton Teknis:</h6>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-6">
                        <div class="spec-box">
                            <div class="spec-title"><i class="bi bi-feather me-1"></i> Berat & Ukuran Grip</div>
                            <div class="spec-val"><?= htmlspecialchars($product['weight_grip']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6">
                        <div class="spec-box">
                            <div class="spec-title"><i class="bi bi-speedometer2 me-1"></i> Tarikan Maksimal</div>
                            <div class="spec-val"><?= htmlspecialchars($product['tension_lbs']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6">
                        <div class="spec-box">
                            <div class="spec-title"><i class="bi bi-bullseye me-1"></i> Titik Keseimbangan</div>
                            <div class="spec-val"><?= htmlspecialchars($product['balance_point']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6">
                        <div class="spec-box">
                            <div class="spec-title"><i class="bi bi-arrows-collapse me-1"></i> Kelenturan Shaft</div>
                            <div class="spec-val"><?= htmlspecialchars($product['flexibility']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Form Tambah ke Keranjang Belanja -->
                <form action="<?= BASE_URL ?>/cart.php" method="GET" class="mt-auto">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto">
                            <label class="form-label text-secondary mb-0 fw-semibold">Jumlah Beli:</label>
                        </div>
                        <div class="col-auto">
                            <div class="input-group" style="width: 140px;">
                                <button type="button" class="btn btn-outline-secondary text-white" onclick="changeQty(-1, <?= $product['stock'] ?>)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" id="productQty" name="qty" class="form-control form-control-custom text-center fw-bold" value="1" min="1" max="<?= max(1, $product['stock']) ?>" <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                                <button type="button" class="btn btn-outline-secondary text-white" onclick="changeQty(1, <?= $product['stock'] ?>)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($product['stock'] > 0): ?>
                            <button type="submit" class="btn btn-primary-custom btn-lg px-4 flex-grow-1">
                                <i class="bi bi-bag-plus-fill me-2"></i> Tambah ke Keranjang
                            </button>
                            <button type="submit" name="direct_checkout" value="1" class="btn btn-success btn-lg px-4 fw-bold flex-grow-1">
                                <i class="bi bi-credit-card-2-front-fill me-2"></i> Beli Sekarang
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                                <i class="bi bi-x-circle me-2"></i> Produk Sedang Habis
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deskripsi & Ulasan Tab -->
    <div class="card border-0 bg-card rounded-4 p-4 p-md-5 shadow-sm mb-5">
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-desc-tab" data-bs-toggle="pill" data-bs-target="#pills-desc" type="button" role="tab" aria-selected="true">
                    <i class="bi bi-file-text me-1"></i> Deskripsi Produk
                </button>
            </li>
            <li class="nav-item ms-2" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-reviews-tab" data-bs-toggle="pill" data-bs-target="#pills-reviews" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-chat-left-heart me-1"></i> Ulasan Pembeli (<?= count($reviews) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- Tab Deskripsi -->
            <div class="tab-pane fade show active" id="pills-desc" role="tabpanel">
                <h5 class="fw-bold text-white mb-3">Tentang Raket <?= htmlspecialchars($product['name']) ?></h5>
                <div class="text-secondary leading-relaxed" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>

                <div class="alert alert-dark border-secondary border-opacity-50 mt-4 rounded-3 d-flex align-items-center gap-3">
                    <i class="bi bi-info-circle-fill text-info fs-3"></i>
                    <div>
                        <strong>Catatan Pemasangan Senar:</strong>
                        <div class="text-secondary small">Jika ingin dipasangkan senar raket (BG66 Ultimax, Exbolt, dll.), silakan tuliskan permintaan tarikan LBS pada kolom catatan saat proses Checkout.</div>
                    </div>
                </div>
            </div>

            <!-- Tab Ulasan -->
            <div class="tab-pane fade" id="pills-reviews" role="tabpanel">
                <?php if (empty($reviews)): ?>
                    <p class="text-secondary mb-0">Belum ada ulasan untuk raket ini. Jadilah pembeli pertama yang memberikan ulasan!</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-white"><i class="bi bi-person-circle text-info me-2"></i><?= htmlspecialchars($rev['user_name']) ?></strong>
                                    <div class="text-warning small">
                                        <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-secondary small mb-1"><?= htmlspecialchars($rev['comment']) ?></p>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($rev['created_at']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Raket Terkait Lainnya -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mb-4">
            <h4 class="fw-bold text-white mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Rekomendasi Raket Sejenis</h4>
            <div class="row g-3">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="racket-card p-3">
                            <div class="text-center mb-2">
                                <img src="<?= getProductImageUrl($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" style="height: 140px; object-fit: contain;">
                            </div>
                            <h6 class="fw-bold text-white mb-1" style="font-size: 0.95rem;">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $rel['id'] ?>" class="text-white text-decoration-none hover-primary">
                                    <?= htmlspecialchars($rel['name']) ?>
                                </a>
                            </h6>
                            <div class="text-primary fw-bold mb-2"><?= formatRupiah($rel['price']) ?></div>
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= $rel['id'] ?>" class="btn btn-outline-secondary btn-sm text-white-50 w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
