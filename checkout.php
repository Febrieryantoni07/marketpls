<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    setFlashMessage('warning', 'Keranjang belanja Anda masih kosong. Silakan pilih raket terlebih dahulu.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$totalAmount = getCartTotal();
$totalCount = getCartCount();
$errors = [];

// Proses Submit Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $customerEmail = sanitize($_POST['customer_email'] ?? '');
    $customerPhone = sanitize($_POST['customer_phone'] ?? '');
    $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
    $courier = sanitize($_POST['courier'] ?? 'JNE Regular');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'Transfer Bank BCA');
    $notes = sanitize($_POST['notes'] ?? '');

    // Validasi Form
    if (empty($customerName)) $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Alamat email tidak valid.';
    if (empty($customerPhone)) $errors[] = 'Nomor WhatsApp / telepon wajib diisi.';
    if (empty($shippingAddress)) $errors[] = 'Alamat pengiriman lengkap wajib diisi.';

    if (empty($errors)) {
        // Mulai Transaksi Database
        mysqli_begin_transaction($conn);

        try {
            // 1. Validasi Stok Terakhir Seluruh Item di Database
            foreach ($cartItems as $pId => $item) {
                $checkStmt = mysqli_prepare($conn, "SELECT name, stock FROM products WHERE id = ? FOR UPDATE");
                mysqli_stmt_bind_param($checkStmt, "i", $pId);
                mysqli_stmt_execute($checkStmt);
                $res = mysqli_stmt_get_result($checkStmt);
                $dbProd = mysqli_fetch_assoc($res);
                mysqli_stmt_close($checkStmt);

                if (!$dbProd || $dbProd['stock'] < $item['quantity']) {
                    $available = $dbProd ? $dbProd['stock'] : 0;
                    throw new Exception("Stok untuk raket '{$item['name']}' tidak mencukupi (tersisa {$available} unit). Silakan sesuaikan jumlah di keranjang.");
                }
            }

            // 2. Generate Nomor Faktur / Invoice Unik
            $invoiceCode = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // 3. Simpan ke Tabel `orders`
            $orderSql = "INSERT INTO orders (invoice_code, customer_name, customer_email, customer_phone, shipping_address, courier, payment_method, total_amount, status, notes) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $orderStmt = mysqli_prepare($conn, $orderSql);
            mysqli_stmt_bind_param($orderStmt, "sssssssds", $invoiceCode, $customerName, $customerEmail, $customerPhone, $shippingAddress, $courier, $paymentMethod, $totalAmount, $notes);
            mysqli_stmt_execute($orderStmt);
            $orderId = mysqli_insert_id($conn);
            mysqli_stmt_close($orderStmt);

            // 4. Simpan ke Tabel `order_items` dan Kurangi Stok di `products`
            $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            $itemStmt = mysqli_prepare($conn, $itemSql);

            $stockUpdateSql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stockStmt = mysqli_prepare($conn, $stockUpdateSql);

            foreach ($cartItems as $pId => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                
                // Insert detail item
                mysqli_stmt_bind_param($itemStmt, "iisdid", $orderId, $pId, $item['name'], $item['price'], $item['quantity'], $subtotal);
                mysqli_stmt_execute($itemStmt);

                // Update pengurangan stok produk
                mysqli_stmt_bind_param($stockStmt, "ii", $item['quantity'], $pId);
                mysqli_stmt_execute($stockStmt);
            }

            mysqli_stmt_close($itemStmt);
            mysqli_stmt_close($stockStmt);

            // Komit transaksi
            mysqli_commit($conn);

            // Kosongkan keranjang belanja
            unset($_SESSION['cart']);

            setFlashMessage('success', "Pesanan Anda berhasil dibuat dengan No. Invoice {$invoiceCode}!");
            header('Location: ' . BASE_URL . '/invoice.php?inv=' . urlencode($invoiceCode));
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout Pesanan Raket';
$currUser = getCurrentUser();

$valName = $_POST['customer_name'] ?? ($currUser['name'] ?? '');
$valEmail = $_POST['customer_email'] ?? ($currUser['email'] ?? '');
$valPhone = $_POST['customer_phone'] ?? ($currUser['phone'] ?? '');
$valAddress = $_POST['shipping_address'] ?? ($currUser['address'] ?? '');

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="mb-4">
        <h2 class="fw-bold text-white mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Checkout & Pembayaran</h2>
        <small class="text-secondary">Lengkapi data pengiriman untuk menyelesaikan pesanan raket badminton Anda</small>
    </div>

    <?php if (!$currUser): ?>
        <div class="alert alert-dark border-secondary border-opacity-50 rounded-4 p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-25 text-info p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="bi bi-person-circle fs-4"></i>
                </div>
                <div>
                    <strong class="text-white d-block">Punya akun SmashZone?</strong>
                    <small class="text-secondary">Masuk / Login sekarang agar data kontak dan alamat pengiriman terisi otomatis!</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode(BASE_URL . '/checkout.php') ?>" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk / Login
                </a>
                <a href="<?= BASE_URL ?>/register.php?redirect=<?= urlencode(BASE_URL . '/checkout.php') ?>" class="btn btn-dark border border-secondary border-opacity-50 btn-sm rounded-pill px-3 text-secondary">
                    Daftar Akun
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success bg-opacity-10 border-success rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <div>
                <strong class="text-success">Checkout sebagai <?= htmlspecialchars($currUser['name']) ?> (<?= htmlspecialchars($currUser['role']) ?>)</strong>
                <div class="small text-secondary">Data kontak dan alamat pengiriman telah dimuat otomatis dari profil akun Anda.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan pada input Anda:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/checkout.php">
        <div class="row g-4">
            <!-- Kolom Form Data Pembeli -->
            <div class="col-lg-7">
                <div class="card bg-card border-0 rounded-4 p-4 p-md-5 shadow-sm mb-4">
                    <h5 class="fw-bold text-white mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="bi bi-person-lines-fill text-info me-2"></i>Data Pembeli & Kontak
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label text-white fw-semibold">Nama Lengkap Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-control-custom" placeholder="Contoh: Anthony Sinisuka Ginting" value="<?= htmlspecialchars($valName) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">Email Aktif <span class="text-danger">*</span></label>
                            <input type="email" name="customer_email" class="form-control form-control-custom" placeholder="nama@email.com" value="<?= htmlspecialchars($valEmail) ?>" required>
                            <small class="text-secondary">Invoice akan dikaitkan ke email ini</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">No. WhatsApp / Telepon <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" class="form-control form-control-custom" placeholder="08123456789" value="<?= htmlspecialchars($valPhone) ?>" required>
                        </div>
                    </div>

                    <h5 class="fw-bold text-white mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="bi bi-truck text-warning me-2"></i>Alamat Pengiriman & Ekspedisi
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label text-white fw-semibold">Alamat Lengkap Tujuan <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" rows="3" class="form-control form-control-custom" placeholder="Tuliskan nama jalan, nomor rumah/kantor, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos..." required><?= htmlspecialchars($valAddress) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">Pilih Ekspedisi Kurir</label>
                            <select name="courier" class="form-select form-select-custom">
                                <option value="JNE Regular">JNE Regular (Pipa PVC Guard)</option>
                                <option value="JNE YES (Yakin Esok Sampai)">JNE YES (1 Hari Sampai)</option>
                                <option value="J&T Express">J&T Express</option>
                                <option value="SiCepat Gokil">SiCepat Cargo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select form-select-custom">
                                <option value="Transfer Bank BCA">Transfer Bank BCA (Otomatis)</option>
                                <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                                <option value="Transfer Bank BRI">Transfer Bank BRI</option>
                                <option value="QRIS / E-Wallet (GoPay/OVO/Dana)">QRIS / All E-Wallet</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-white fw-semibold">Catatan Khusus Pesanan (Opsional)</label>
                            <input type="text" name="notes" class="form-control form-control-custom" placeholder="Contoh: Tolong pasangkan senar 27 lbs simpul 4, atau pengiriman jangan dibanting." value="<?= htmlspecialchars($_POST['notes'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Ringkasan Belanja & Konfirmasi -->
            <div class="col-lg-5">
                <div class="card bg-card border-0 rounded-4 p-4 shadow-sm position-sticky" style="top: 100px;">
                    <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="bi bi-receipt text-primary me-2"></i>Rincian Belanjaan
                    </h5>

                    <!-- Item list -->
                    <div class="d-flex flex-column gap-3 mb-4 max-h-300 overflow-auto">
                        <?php foreach ($cartItems as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-dark border border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= getProductImageUrl($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-2" style="width: 45px; height: 45px; object-fit: contain;">
                                    <div>
                                        <div class="fw-semibold text-white small" style="line-height: 1.2;"><?= htmlspecialchars($item['name']) ?></div>
                                        <small class="text-secondary"><?= $item['quantity'] ?>x &bull; <?= formatRupiah($item['price']) ?></small>
                                    </div>
                                </div>
                                <span class="fw-bold text-primary small"><?= formatRupiah($subtotal) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-top border-secondary border-opacity-25 pt-3">
                        <div class="d-flex justify-content-between text-secondary mb-2">
                            <span>Total Item:</span>
                            <span class="text-white fw-semibold"><?= $totalCount ?> unit raket</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary mb-2">
                            <span>Ongkos Kirim:</span>
                            <span class="badge bg-success bg-opacity-25 text-success">SUBSIDI GRATIS</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary mb-3">
                            <span>Packaging Tabung Pipa PVC:</span>
                            <span class="badge bg-info bg-opacity-25 text-info">TERMASUK</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline pt-2 border-top border-secondary border-opacity-25 mb-4">
                            <span class="h6 fw-bold text-white mb-0">Total Pembayaran:</span>
                            <span class="h4 fw-extrabold text-primary mb-0"><?= formatRupiah($totalAmount) ?></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="place_order" value="1" class="btn btn-primary-custom btn-lg py-3 fw-bold">
                            <i class="bi bi-bag-check-fill me-2"></i> Konfirmasi & Buat Pesanan
                        </button>
                        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline-secondary btn-sm text-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
