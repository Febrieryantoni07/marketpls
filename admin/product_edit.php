<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlashMessage('danger', 'ID Produk tidak valid.');
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

// Ambil Data Produk
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
    setFlashMessage('danger', 'Data raket tidak ditemukan.');
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$pageTitle = 'Edit Raket: ' . $product['name'];

// Ambil Kategori
$catQuery = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");
$categories = mysqli_fetch_all($catQuery, MYSQLI_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $brand = sanitize($_POST['brand'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $weightGrip = sanitize($_POST['weight_grip'] ?? '4U/G5');
    $tensionLbs = sanitize($_POST['tension_lbs'] ?? '24 - 30 LBS');
    $balancePoint = sanitize($_POST['balance_point'] ?? 'Head Heavy');
    $flexibility = sanitize($_POST['flexibility'] ?? 'Medium Stiff');
    $description = sanitize($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $imageName = $product['image']; // Default pakai gambar lama

    // Validasi
    if (empty($name)) $errors[] = 'Nama raket wajib diisi.';
    if (empty($brand)) $errors[] = 'Brand raket wajib diisi.';
    if ($price <= 0) $errors[] = 'Harga raket harus lebih dari 0.';
    if ($stock < 0) $errors[] = 'Stok tidak boleh negatif.';

    // Penanganan Upload Gambar Baru jika ada
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (in_array($fileExt, $allowedExt)) {
            $newFileName = 'racket_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            $uploadDir = __DIR__ . '/../assets/images/products/';
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imageName = $newFileName;
            }
        } else {
            $errors[] = 'Format file gambar harus JPG, PNG, WEBP, atau SVG.';
        }
    }

    if (empty($errors)) {
        $updateSql = "UPDATE products 
                      SET category_id = ?, name = ?, brand = ?, price = ?, stock = ?, weight_grip = ?, tension_lbs = ?, balance_point = ?, flexibility = ?, description = ?, image = ?, is_featured = ?
                      WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "issdissssssii", $categoryId, $name, $brand, $price, $stock, $weightGrip, $tensionLbs, $balancePoint, $flexibility, $description, $imageName, $isFeatured, $id);

        if (mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            setFlashMessage('success', "Data raket '{$name}' berhasil diperbarui!");
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $errors[] = "Gagal memperbarui raket: " . mysqli_error($conn);
            mysqli_stmt_close($updateStmt);
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-white mb-0"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Raket Badminton</h2>
                    <small class="text-secondary">Ubah data harga, ketersediaan stok, dan parameter teknis raket</small>
                </div>
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Perhatian:</h6>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card bg-card border-0 rounded-4 p-4 p-md-5 shadow-lg">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- Informasi Dasar -->
                        <div class="col-md-8">
                            <label class="form-label text-white fw-semibold">Nama Lengkap Raket <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-custom" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Brand / Merk <span class="text-danger">*</span></label>
                            <select name="brand" class="form-select form-select-custom" required>
                                <?php 
                                $brandList = ['Yonex', 'Victor', 'Li-Ning', 'Mizuno', 'Felet', 'Flypower', 'Lainnya'];
                                foreach ($brandList as $b):
                                ?>
                                    <option value="<?= $b ?>" <?= ($product['brand'] === $b) ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Kategori Karakter <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select form-select-custom" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control form-control-custom" value="<?= (int)$product['price'] ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Jumlah Stok (Unit) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control form-control-custom" value="<?= (int)$product['stock'] ?>" required>
                        </div>

                        <!-- Spesifikasi Teknis Raket -->
                        <div class="col-12">
                            <hr class="border-secondary border-opacity-25 my-2">
                            <h6 class="text-info fw-bold mb-3"><i class="bi bi-sliders me-2"></i>Spesifikasi Teknis Badminton:</h6>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Bobot & Grip</label>
                            <input type="text" name="weight_grip" class="form-control form-control-custom" value="<?= htmlspecialchars($product['weight_grip']) ?>">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Maksimal Tarikan</label>
                            <input type="text" name="tension_lbs" class="form-control form-control-custom" value="<?= htmlspecialchars($product['tension_lbs']) ?>">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Titik Keseimbangan</label>
                            <select name="balance_point" class="form-select form-select-custom">
                                <option value="Head Heavy" <?= ($product['balance_point'] === 'Head Heavy' || strpos($product['balance_point'], 'Head Heavy') !== false) ? 'selected' : '' ?>>Head Heavy (Smash)</option>
                                <option value="Even Balance" <?= ($product['balance_point'] === 'Even Balance' || strpos($product['balance_point'], 'Even Balance') !== false) ? 'selected' : '' ?>>Even Balance (All-Around)</option>
                                <option value="Head Light" <?= ($product['balance_point'] === 'Head Light' || strpos($product['balance_point'], 'Head Light') !== false) ? 'selected' : '' ?>>Head Light (Speed)</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Kelenturan Shaft</label>
                            <select name="flexibility" class="form-select form-select-custom">
                                <option value="Extra Stiff" <?= ($product['flexibility'] === 'Extra Stiff') ? 'selected' : '' ?>>Extra Stiff</option>
                                <option value="Stiff" <?= ($product['flexibility'] === 'Stiff') ? 'selected' : '' ?>>Stiff</option>
                                <option value="Medium" <?= ($product['flexibility'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="Flexible" <?= ($product['flexibility'] === 'Flexible') ? 'selected' : '' ?>>Flexible</option>
                            </select>
                        </div>

                        <!-- Gambar & Deskripsi -->
                        <div class="col-12">
                            <hr class="border-secondary border-opacity-25 my-2">
                        </div>

                        <div class="col-md-3 text-center">
                            <label class="form-label text-white fw-semibold d-block">Gambar Saat Ini</label>
                            <img src="<?= getProductImageUrl($product['image']) ?>" alt="Preview" class="rounded-3 img-fluid" style="max-height: 120px; background: #0b1120; padding: 6px;">
                        </div>

                        <div class="col-md-9">
                            <label class="form-label text-white fw-semibold">Ganti Gambar (Opsional)</label>
                            <input type="file" name="product_image" class="form-control form-control-custom" accept="image/*">
                            <small class="text-secondary">Biarkan kosong jika tidak ingin mengganti gambar produk yang sudah ada.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-white fw-semibold">Deskripsi Produk</label>
                            <textarea name="description" rows="4" class="form-control form-control-custom"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" <?= ($product['is_featured'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label text-white" for="isFeatured">Tampilkan sebagai Raket Pilihan Unggulan (Featured)</label>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-12 d-flex gap-3 justify-content-end pt-3 border-top border-secondary border-opacity-25">
                            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-warning px-5 fw-bold text-dark">
                                <i class="bi bi-check2-circle me-2"></i> Perbarui Data Raket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
