<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Kelola Transaksi Pesanan';

// Update Status Pesanan jika ada form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['status'] ?? 'pending');
    $allowedStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderId);
        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', "Status pesanan ID #{$orderId} berhasil diubah menjadi '" . strtoupper($newStatus) . "'.");
        } else {
            setFlashMessage('danger', 'Gagal memperbarui status: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }
    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

// Pencarian
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = "1=1";
$params = [];
$types = "";

if (!empty($q)) {
    $where .= " AND (invoice_code LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $like = "%$q%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql = "SELECT * FROM orders WHERE $where ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-white mb-0"><i class="bi bi-receipt-cutoff text-info me-2"></i>Daftar Transaksi Pesanan</h2>
            <small class="text-secondary">Kelola pesanan masuk dari pembeli dan perbarui status pengiriman raket</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Kelola Produk
        </a>
    </div>

    <!-- Toolbar Filter -->
    <div class="card bg-card border-0 rounded-4 p-3 shadow-sm mb-4">
        <form method="GET" action="<?= BASE_URL ?>/admin/orders.php" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control form-control-custom" placeholder="Cari nomor invoice (INV-...), nama pembeli, atau email..." value="<?= htmlspecialchars($q) ?>">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom flex-grow-1">
                    <i class="bi bi-search me-1"></i> Cari Pesanan
                </button>
                <?php if (!empty($q)): ?>
                    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-secondary text-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Pesanan -->
    <div class="card bg-card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Data Pembeli</th>
                        <th>Logistik & Pembayaran</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-center">Status Pesanan</th>
                        <th class="text-center" style="width: 170px;">Ubah Status</th>
                        <th class="text-center" style="width: 70px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox display-4 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada transaksi pemesanan yang tercatat di database.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-white mb-1">
                                        <code><?= htmlspecialchars($ord['invoice_code']) ?></code>
                                    </div>
                                    <small class="text-secondary"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?></small>
                                </td>
                                <td>
                                    <strong class="text-white d-block"><?= htmlspecialchars($ord['customer_name']) ?></strong>
                                    <small class="text-secondary"><i class="bi bi-telephone text-info me-1"></i><?= htmlspecialchars($ord['customer_phone']) ?></small>
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
                                <td>
                                    <form method="POST" class="d-flex align-items-center gap-1">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <select name="status" class="form-select form-select-sm form-select-custom py-1 px-2" style="font-size: 0.8rem;">
                                            <option value="pending" <?= ($ord['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="paid" <?= ($ord['status'] === 'paid') ? 'selected' : '' ?>>Paid (Lunas)</option>
                                            <option value="shipped" <?= ($ord['status'] === 'shipped') ? 'selected' : '' ?>>Shipped (Dikirim)</option>
                                            <option value="completed" <?= ($ord['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= ($ord['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" value="1" class="btn btn-outline-primary btn-sm px-2" title="Simpan Status">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/invoice.php?inv=<?= urlencode($ord['invoice_code']) ?>" target="_blank" class="btn btn-outline-info btn-sm" title="Buka Faktur / Invoice">
                                        <i class="bi bi-receipt"></i>
                                    </a>
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
