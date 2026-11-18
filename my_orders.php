<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$pageTitle = 'Riwayat Pesanan Saya';

// Ambil pesanan milik pengguna yang login (berdasarkan email atau telepon)
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE customer_email = ? OR customer_phone = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "ss", $currentUser['email'], $currentUser['phone']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-white mb-0"><i class="bi bi-bag-check-fill text-info me-2"></i>Pesanan Saya</h2>
            <small class="text-secondary">Pantau status pengiriman raket dan faktur transaksi atas nama <strong><?= htmlspecialchars($currentUser['name']) ?></strong></small>
        </div>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary text-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Belanja Lagi
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card bg-card border-0 rounded-4 p-5 text-center my-4">
            <div class="mb-3">
                <i class="bi bi-box2 text-secondary display-1"></i>
            </div>
            <h4 class="fw-bold text-white mb-2">Belum Ada Transaksi Pesanan</h4>
            <p class="text-secondary mb-4">Anda belum melakukan pemesanan raket badminton. Pilih raket favorit Anda di katalog sekarang!</p>
            <div>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary-custom btn-lg px-4">
                    <i class="bi bi-shop me-2"></i> Lihat Katalog Raket
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card bg-card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Tanggal Pesan</th>
                            <th>Kurir & Metode Bayar</th>
                            <th class="text-end">Total Belanja</th>
                            <th class="text-center">Status Pesanan</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td>
                                    <strong class="text-white"><code><?= htmlspecialchars($ord['invoice_code']) ?></code></strong>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?> WIB</span>
                                </td>
                                <td>
                                    <div class="small text-white"><?= htmlspecialchars($ord['courier']) ?></div>
                                    <small class="text-secondary"><?= htmlspecialchars($ord['payment_method']) ?></small>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    <?= formatRupiah($ord['total_amount']) ?>
                                </td>
                                <td class="text-center">
                                    <?= getOrderStatusBadge($ord['status']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/invoice.php?inv=<?= urlencode($ord['invoice_code']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-receipt me-1"></i> Faktur
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
