<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah Raket Badminton Baru';

// Ambil Kategori
$catQuery = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");
$categories = mysqli_fetch_all($catQuery, MYSQLI_ASSOC);

// Ambil ID Penjual yang sedang login
$sellerId = $_SESSION['user']['id'] ?? 1;

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

    // Validasi
    if (empty($name)) $errors[] = 'Nama raket wajib diisi.';
    if (empty($brand)) $errors[] = 'Brand raket wajib diisi.';
    if ($price <= 0) $errors[] = 'Harga raket harus lebih dari 0.';
    if ($stock < 0) $errors[] = 'Stok tidak boleh negatif.';

    // Penanganan Upload Gambar
    $imageName = 'default_racket.svg';
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
    } elseif (!empty($_POST['preset_image'])) {
        $imageName = sanitize($_POST['preset_image']);
    }

    if (empty($errors)) {
        $sql = "INSERT INTO products (seller_id, category_id, name, brand, price, stock, weight_grip, tension_lbs, balance_point, flexibility, description, image, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iissdissssssi", $sellerId, $categoryId, $name, $brand, $price, $stock, $weightGrip, $tensionLbs, $balancePoint, $flexibility, $description, $imageName, $isFeatured);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            setFlashMessage('success', "Raket '{$name}' berhasil ditambahkan ke katalog!");
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $errors[] = "Gagal menyimpan raket ke database: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
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
                    <h2 class="fw-bold text-white mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Raket Badminton</h2>
                    <small class="text-secondary">Input spesifikasi lengkap raket badminton untuk dijual di marketplace</small>
                </div>
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
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
                            <input type="text" name="name" class="form-control form-control-custom" placeholder="Contoh: Yonex Astrox 77 Pro" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Brand / Merk <span class="text-danger">*</span></label>
                            <select name="brand" class="form-select form-select-custom" required>
                                <option value="Yonex" <?= (($_POST['brand'] ?? '') === 'Yonex') ? 'selected' : '' ?>>Yonex</option>
                                <option value="Victor" <?= (($_POST['brand'] ?? '') === 'Victor') ? 'selected' : '' ?>>Victor</option>
                                <option value="Li-Ning" <?= (($_POST['brand'] ?? '') === 'Li-Ning') ? 'selected' : '' ?>>Li-Ning</option>
                                <option value="Mizuno" <?= (($_POST['brand'] ?? '') === 'Mizuno') ? 'selected' : '' ?>>Mizuno</option>
                                <option value="Felet" <?= (($_POST['brand'] ?? '') === 'Felet') ? 'selected' : '' ?>>Felet</option>
                                <option value="Flypower" <?= (($_POST['brand'] ?? '') === 'Flypower') ? 'selected' : '' ?>>Flypower</option>
                                <option value="Lainnya" <?= (($_POST['brand'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Kategori Karakter <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select form-select-custom" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control form-control-custom" placeholder="Contoh: 2150000" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white fw-semibold">Jumlah Stok (Unit) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control form-control-custom" placeholder="Contoh: 10" value="<?= htmlspecialchars($_POST['stock'] ?? '10') ?>" required>
                        </div>

                        <!-- Spesifikasi Teknis Raket -->
                        <div class="col-12">
                            <hr class="border-secondary border-opacity-25 my-2">
                            <h6 class="text-info fw-bold mb-3"><i class="bi bi-sliders me-2"></i>Spesifikasi Teknis Badminton:</h6>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Bobot & Grip</label>
                            <input type="text" name="weight_grip" class="form-control form-control-custom" placeholder="4U/G5 atau 3U/G5" value="<?= htmlspecialchars($_POST['weight_grip'] ?? '4U/G5') ?>">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Maksimal Tarikan</label>
                            <input type="text" name="tension_lbs" class="form-control form-control-custom" placeholder="20 - 28 LBS" value="<?= htmlspecialchars($_POST['tension_lbs'] ?? '20 - 28 LBS') ?>">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Titik Keseimbangan</label>
                            <select name="balance_point" class="form-select form-select-custom">
                                <option value="Head Heavy" <?= (($_POST['balance_point'] ?? '') === 'Head Heavy') ? 'selected' : '' ?>>Head Heavy (Smash)</option>
                                <option value="Even Balance" <?= (($_POST['balance_point'] ?? '') === 'Even Balance') ? 'selected' : '' ?>>Even Balance (All-Around)</option>
                                <option value="Head Light" <?= (($_POST['balance_point'] ?? '') === 'Head Light') ? 'selected' : '' ?>>Head Light (Speed)</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-white fw-semibold">Kelenturan Shaft</label>
                            <select name="flexibility" class="form-select form-select-custom">
                                <option value="Extra Stiff">Extra Stiff</option>
                                <option value="Stiff" selected>Stiff</option>
                                <option value="Medium">Medium</option>
                                <option value="Flexible">Flexible</option>
                            </select>
                        </div>

                        <!-- Gambar & Deskripsi -->
                        <div class="col-12">
                            <hr class="border-secondary border-opacity-25 my-2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">Upload Foto Raket</label>
                            <input type="file" name="product_image" class="form-control form-control-custom" accept="image/*">
                            <small class="text-secondary">Format didukung: JPG, PNG, WEBP, atau SVG</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold">Atau Pilih Desain Raket Default</label>
                            <select name="preset_image" class="form-select form-select-custom">
                                <option value="default_racket.svg">Preset Standar SmashZone</option>
                                <option value="racket_astrox_88d.svg">Preset Biru-Emas (Astrox Style)</option>
                                <option value="racket_nanoflare_1000z.svg">Preset Kuning Kilat (Nanoflare Style)</option>
                                <option value="racket_ryuga_2.svg">Preset Ungu-Merah (Ryuga Style)</option>
                                <option value="racket_auraspeed_100x.svg">Preset Cyan-Orange (Auraspeed Style)</option>
                                <option value="racket_halbertec_8000.svg">Preset Hijau Zamrud (Halbertec Style)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-white fw-semibold">Deskripsi Produk & Keunggulan</label>
                            <textarea name="description" rows="4" class="form-control form-control-custom" placeholder="Jelaskan teknologi raket, karakter pukulan, material frame carbon, dan bonus yang didapatkan..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                                <label class="form-check-label text-white" for="isFeatured">Tampilkan sebagai Raket Pilihan Unggulan (Featured)</label>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-12 d-flex gap-3 justify-content-end pt-3 border-top border-secondary border-opacity-25">
                            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary text-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary-custom px-5 fw-bold">
                                <i class="bi bi-save2-fill me-2"></i> Simpan Raket
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
