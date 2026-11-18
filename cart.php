<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Inisialisasi Keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$productId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

// 1. Aksi Tambah Produk ke Keranjang
if ($action === 'add' && $productId > 0) {
    $qty = isset($_REQUEST['qty']) ? max(1, (int)$_REQUEST['qty']) : 1;

    // Ambil data produk terbaru dari database untuk verifikasi harga & stok
    $stmt = mysqli_prepare($conn, "SELECT id, name, brand, price, stock, weight_grip, tension_lbs, image FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($product) {
        if ($product['stock'] <= 0) {
            setFlashMessage('danger', "Maaf, stok raket '{$product['name']}' sedang habis.");
        } else {
            // Cek apakah produk sudah ada di keranjang
            if (isset($_SESSION['cart'][$productId])) {
                $newQty = $_SESSION['cart'][$productId]['quantity'] + $qty;
                if ($newQty > $product['stock']) {
                    $newQty = $product['stock'];
                    setFlashMessage('warning', "Jumlah di keranjang disesuaikan dengan sisa stok maksimal ({$product['stock']} unit).");
                } else {
                    setFlashMessage('success', "Kuantitas raket '{$product['name']}' di keranjang berhasil ditambahkan.");
                }
                $_SESSION['cart'][$productId]['quantity'] = $newQty;
            } else {
                if ($qty > $product['stock']) {
                    $qty = $product['stock'];
                }
                $_SESSION['cart'][$productId] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'brand' => $product['brand'],
                    'price' => (float)$product['price'],
                    'stock' => (int)$product['stock'],
                    'weight_grip' => $product['weight_grip'],
                    'tension_lbs' => $product['tension_lbs'],
                    'image' => $product['image'],
                    'quantity' => $qty
                ];
                setFlashMessage('success', "Raket '{$product['name']}' berhasil ditambahkan ke keranjang belanja.");
            }

            // Jika tombol beli sekarang diklik
            if (isset($_REQUEST['direct_checkout']) && $_REQUEST['direct_checkout'] == 1) {
                header('Location: ' . BASE_URL . '/checkout.php');
                exit;
            }
        }
    } else {
        setFlashMessage('danger', 'Produk raket tidak ditemukan.');
    }
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// 2. Aksi Update Kuantitas
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $pId => $newQty) {
            $pId = (int)$pId;
            $newQty = (int)$newQty;
            if (isset($_SESSION['cart'][$pId])) {
                if ($newQty <= 0) {
                    unset($_SESSION['cart'][$pId]);
                } else {
                    // Cek batas stok
                    $maxStock = $_SESSION['cart'][$pId]['stock'];
                    $_SESSION['cart'][$pId]['quantity'] = min($newQty, $maxStock);
                }
            }
        }
        setFlashMessage('success', 'Keranjang belanja berhasil diperbarui.');
    }
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// 3. Aksi Hapus 1 Item dari Keranjang
if ($action === 'remove' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        $pName = $_SESSION['cart'][$productId]['name'];
        unset($_SESSION['cart'][$productId]);
        setFlashMessage('info', "Raket '{$pName}' telah dihapus dari keranjang belanja.");
    }
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// 4. Aksi Kosongkan Keranjang
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    setFlashMessage('info', 'Keranjang belanja telah dikosongkan.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$pageTitle = 'Keranjang Belanja';
$cartItems = $_SESSION['cart'];
$totalAmount = getCartTotal();
$totalCount = getCartCount();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-0"><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja</h2>
            <small class="text-secondary">Kelola raket badminton yang akan Anda pesan</small>
        </div>
        <?php if (!empty($cartItems)): ?>
            <a href="<?= BASE_URL ?>/cart.php?action=clear" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang?')">
                <i class="bi bi-trash3 me-1"></i> Kosongkan Keranjang
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card bg-card border-0 rounded-4 p-5 text-center my-4">
            <div class="mb-3">
                <i class="bi bi-bag-x text-secondary display-1"></i>
            </div>
            <h3 class="fw-bold text-white mb-2">Keranjang Belanja Anda Masih Kosong</h3>
            <p class="text-secondary mb-4">Belum ada raket yang ditambahkan. Jelajahi katalog raket badminton original kami sekarang!</p>
            <div>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary-custom btn-lg px-4">
                    <i class="bi bi-arrow-left me-2"></i> Belanja Raket Sekarang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Kolom Tabel Keranjang -->
            <div class="col-lg-8">
                <form action="<?= BASE_URL ?>/cart.php?action=update" method="POST">
                    <div class="card bg-card border-0 rounded-4 shadow-sm overflow-hidden mb-3">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk Raket</th>
                                        <th class="text-center">Harga Satuan</th>
                                        <th class="text-center" style="width: 150px;">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center" style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $pId => $item): 
                                        $subtotal = $item['price'] * $item['quantity'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?= getProductImageUrl($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-3" style="width: 60px; height: 60px; object-fit: contain; background: #0f172a; padding: 4px;">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-white">
                                                            <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="text-white text-decoration-none hover-primary">
                                                                <?= htmlspecialchars($item['name']) ?>
                                                            </a>
                                                        </h6>
                                                        <div class="small text-secondary">
                                                            <span class="badge bg-dark border border-secondary text-info me-1"><?= htmlspecialchars($item['brand']) ?></span>
                                                            <span class="me-2"><?= htmlspecialchars($item['weight_grip']) ?></span>
                                                            <span><?= htmlspecialchars($item['tension_lbs']) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-secondary fw-semibold">
                                                <?= formatRupiah($item['price']) ?>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="quantities[<?= $pId ?>]" class="form-control form-control-custom text-center fw-bold" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Maks: <?= $item['stock'] ?></small>
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                <?= formatRupiah($subtotal) ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>/cart.php?action=remove&id=<?= $pId ?>" class="btn btn-outline-danger btn-sm border-0" title="Hapus dari keranjang" onclick="return confirm('Hapus raket ini dari keranjang?')">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="p-3 bg-dark border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                            <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary btn-sm text-white">
                                <i class="bi bi-arrow-left me-1"></i> Tambah Produk Lain
                            </a>
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Perbarui Kuantitas
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Kolom Ringkasan Belanja -->
            <div class="col-lg-4">
                <div class="card bg-card border-0 rounded-4 p-4 shadow-sm">
                    <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                        Ringkasan Pesanan
                    </h5>

                    <div class="d-flex justify-content-between text-secondary mb-2">
                        <span>Total Kuantitas:</span>
                        <strong class="text-white"><?= $totalCount ?> Raket</strong>
                    </div>

                    <div class="d-flex justify-content-between text-secondary mb-2">
                        <span>Subtotal Produk:</span>
                        <strong class="text-white"><?= formatRupiah($totalAmount) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between text-secondary mb-3">
                        <span>Biaya Pengemasan Pipa PVC:</span>
                        <span class="badge bg-success bg-opacity-25 text-success">GRATIS</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-baseline pt-3 border-top border-secondary border-opacity-25 mb-4">
                        <span class="h6 fw-bold text-white mb-0">Total Tagihan:</span>
                        <span class="h4 fw-extrabold text-primary mb-0"><?= formatRupiah($totalAmount) ?></span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary-custom btn-lg py-3 fw-bold">
                            <i class="bi bi-credit-card-fill me-2"></i> Lanjut ke Checkout
                        </a>
                    </div>

                    <div class="mt-4 p-3 rounded-3 bg-dark border border-secondary border-opacity-25 small text-secondary">
                        <div class="d-flex align-items-center gap-2 mb-2 text-white fw-semibold">
                            <i class="bi bi-shield-check text-success fs-5"></i> Transaksi Aman & Terpercaya
                        </div>
                        Pesanan langsung diteruskan ke sistem penjual untuk proses penarikan senar dan pengemasan pipa paralon tebal anti pecah.
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
