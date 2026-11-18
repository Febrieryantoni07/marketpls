<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$invoiceCode = isset($_GET['inv']) ? trim($_GET['inv']) : '';

if (empty($invoiceCode)) {
    setFlashMessage('danger', 'Nomor invoice transaksi tidak valid.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil Data Pesanan
$orderSql = "SELECT * FROM orders WHERE invoice_code = ?";
$stmt = mysqli_prepare($conn, $orderSql);
mysqli_stmt_bind_param($stmt, "s", $invoiceCode);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$order) {
    setFlashMessage('danger', 'Data transaksi pesanan tidak ditemukan di database.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil Item Pesanan
$itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
$itemStmt = mysqli_prepare($conn, $itemsSql);
mysqli_stmt_bind_param($itemStmt, "i", $order['id']);
mysqli_stmt_execute($itemStmt);
$itemRes = mysqli_stmt_get_result($itemStmt);
$items = mysqli_fetch_all($itemRes, MYSQLI_ASSOC);
mysqli_stmt_close($itemStmt);

$pageTitle = 'Invoice ' . $order['invoice_code'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Action Bar (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary text-white btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-info btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Invoice / Simpan PDF
            </button>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-dark btn-sm border-secondary text-white">
                <i class="bi bi-list-check me-1"></i> Kelola di Admin
            </a>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="card bg-card card-print border-0 rounded-4 p-4 p-md-5 shadow-lg">
        <!-- Header Invoice -->
        <div class="row align-items-start border-bottom border-secondary border-opacity-25 pb-4 mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-3 p-2 bg-primary d-inline-flex">
                        <i class="bi bi-trophy-fill text-white fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-white mb-0">SmashZone</h3>
                        <small class="text-secondary">Pusat Raket Badminton Original</small>
                    </div>
                </div>
                <div class="text-secondary small mt-2">
                    Jl. Bulutangkis Raya No. 10, Jakarta Selatan<br>
                    WhatsApp: 0812-3456-7890 | Email: cs@smashzone.com
                </div>
            </div>

            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <h4 class="fw-bold text-primary mb-1">INVOICE PEMESANAN</h4>
                <div class="h5 fw-bold text-white mb-1"><code><?= htmlspecialchars($order['invoice_code']) ?></code></div>
                <div class="text-secondary small mb-2">Tanggal: <?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB</div>
                <div><?= getOrderStatusBadge($order['status']) ?></div>
            </div>
        </div>

        <!-- Info Pembeli & Pengiriman -->
        <div class="row g-4 mb-4 pb-4 border-bottom border-secondary border-opacity-25">
            <div class="col-md-6">
                <h6 class="text-white-50 text-uppercase fw-bold small mb-2">Tujuan Pengiriman:</h6>
                <div class="h6 fw-bold text-white mb-1"><?= htmlspecialchars($order['customer_name']) ?></div>
                <div class="text-secondary small mb-1">
                    <i class="bi bi-telephone text-info me-1"></i><?= htmlspecialchars($order['customer_phone']) ?> | 
                    <i class="bi bi-envelope text-info me-1"></i><?= htmlspecialchars($order['customer_email']) ?>
                </div>
                <div class="text-secondary small">
                    <i class="bi bi-geo-alt text-danger me-1"></i><?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                </div>
            </div>

            <div class="col-md-6">
                <h6 class="text-white-50 text-uppercase fw-bold small mb-2">Metode Logistik & Pembayaran:</h6>
                <ul class="list-unstyled text-secondary small mb-0">
                    <li class="mb-1"><strong class="text-white">Ekspedisi:</strong> <?= htmlspecialchars($order['courier']) ?></li>
                    <li class="mb-1"><strong class="text-white">Metode Bayar:</strong> <?= htmlspecialchars($order['payment_method']) ?></li>
                    <?php if (!empty($order['notes'])): ?>
                        <li class="mt-2 p-2 rounded bg-dark border border-secondary border-opacity-25 text-warning">
                            <strong>Catatan Tambahan:</strong> "<?= htmlspecialchars($order['notes']) ?>"
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Tabel Rincian Item Raket -->
        <div class="table-responsive mb-4">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Raket Badminton</th>
                        <th class="text-center">Harga Satuan</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($items as $item): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong class="text-white"><?= htmlspecialchars($item['product_name']) ?></strong>
                            </td>
                            <td class="text-center"><?= formatRupiah($item['price']) ?></td>
                            <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                            <td class="text-end fw-bold text-primary"><?= formatRupiah($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold text-secondary">Biaya Pipa Packaging:</td>
                        <td class="text-end text-success fw-bold">GRATIS</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end fw-bold text-secondary">Ongkos Kirim:</td>
                        <td class="text-end text-success fw-bold">GRATIS</td>
                    </tr>
                    <tr class="fs-5">
                        <td colspan="4" class="text-end fw-extrabold text-white">Total Tagihan:</td>
                        <td class="text-end fw-extrabold text-primary"><?= formatRupiah($order['total_amount']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Instruksi Pembayaran -->
        <div class="alert alert-dark border-secondary border-opacity-50 rounded-4 p-4 mb-4">
            <h6 class="fw-bold text-white mb-2"><i class="bi bi-wallet2 text-warning me-2"></i>Instruksi Pembayaran:</h6>
            <p class="text-secondary small mb-3">
                Silakan lakukan transfer pembayaran sejumlah <strong><?= formatRupiah($order['total_amount']) ?></strong> ke salah satu rekening resmi SmashZone berikut:
            </p>
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-25">
                        <small class="text-secondary d-block">Bank Central Asia (BCA)</small>
                        <strong class="text-white fs-6">8801-2345-6789</strong>
                        <div class="text-muted small">a.n. SmashZone Official</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-25">
                        <small class="text-secondary d-block">Bank Mandiri</small>
                        <strong class="text-white fs-6">137-00-1234567-8</strong>
                        <div class="text-muted small">a.n. SmashZone Official</div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-secondary small">
                <i class="bi bi-info-circle text-info me-1"></i> Setelah melakukan transfer, simpan bukti pembayaran dan konfirmasi melalui WhatsApp di 0812-3456-7890 dengan menyertakan nomor invoice <code><?= htmlspecialchars($order['invoice_code']) ?></code>.
            </div>
        </div>

        <div class="text-center text-secondary small pt-3 border-top border-secondary border-opacity-25">
            Terima kasih telah berbelanja di <strong>SmashZone Badminton Marketplace</strong>.<br>
            Selamat bertanding dan raih kemenangan di lapangan! 🏸
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
