<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, langsung arahkan
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}

$pageTitle = 'Daftar Akun Baru';
$redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'customer');
    $address = sanitize($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $redirectPost = sanitize($_POST['redirect'] ?? '');

    // Validasi
    if (empty($name)) {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Alamat email tidak valid.';
    }
    if (empty($phone)) {
        $errors[] = 'Nomor WhatsApp / telepon wajib diisi.';
    }
    if (!in_array($role, ['customer', 'seller'])) {
        $role = 'customer';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Kata sandi minimal 6 karakter.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok dengan kata sandi.';
    }

    // Cek apakah email sudah terdaftar
    if (empty($errors)) {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errors[] = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau langsung login.';
        }
        mysqli_stmt_close($checkStmt);
    }

    // Simpan Pengguna Baru
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $passwordHash, $role, $phone, $address);

        if (mysqli_stmt_execute($stmt)) {
            $newUserId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            // Auto-login setelah berhasil mendaftar
            $_SESSION['user'] = [
                'id' => (int)$newUserId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
                'address' => $address
            ];

            $roleLabel = ($role === 'seller') ? 'Penjual / Admin' : 'Pembeli';
            setFlashMessage('success', "Pendaftaran berhasil! Selamat bergabung di SmashZone, <strong>{$name}</strong>. Anda langsung masuk sebagai <strong>{$roleLabel}</strong>.");

            // Redirection
            if (!empty($redirectPost) && strpos($redirectPost, 'http') === false) {
                header('Location: ' . $redirectPost);
                exit;
            }

            if ($role === 'seller') {
                header('Location: ' . BASE_URL . '/admin/index.php');
            } else {
                header('Location: ' . BASE_URL . '/index.php');
            }
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data akun: ' . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-10">
            <div class="card bg-card border-0 rounded-4 p-4 p-md-5 shadow-2xl position-relative overflow-hidden">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(2, 132, 199, 0.2)); border: 1px solid rgba(16, 185, 129, 0.3);">
                        <i class="bi bi-person-badge-fill fs-1 text-success"></i>
                    </div>
                    <h3 class="fw-extrabold text-white mb-1">Daftar Akun Baru</h3>
                    <p class="text-secondary small">Buat akun untuk mulai membeli raket atau kelola toko sebagai Penjual/Admin</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <strong class="small">Terdapat kesalahan pendaftaran:</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register.php">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">

                    <!-- Pilihan Tipe Akun (Role) -->
                    <div class="mb-4">
                        <label class="form-label text-white fw-bold small d-block mb-2">Pilih Peran Akun yang Didaftarkan:</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="card bg-dark border border-secondary border-opacity-50 p-3 rounded-3 text-start w-100 cursor-pointer role-card h-100">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="role" value="customer" class="form-check-input mt-0" <?= (($_POST['role'] ?? 'customer') === 'customer') ? 'checked' : '' ?>>
                                        <strong class="text-info"><i class="bi bi-bag-check-fill me-1"></i> Akun Pembeli (User)</strong>
                                    </div>
                                    <small class="text-secondary ps-4">Untuk memesan raket badminton, menyimpan alamat, & melihat status transaksi.</small>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card bg-dark border border-secondary border-opacity-50 p-3 rounded-3 text-start w-100 cursor-pointer role-card h-100">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="role" value="seller" class="form-check-input mt-0" <?= (($_POST['role'] ?? '') === 'seller') ? 'checked' : '' ?>>
                                        <strong class="text-warning"><i class="bi bi-shop me-1"></i> Akun Penjual (Admin)</strong>
                                    </div>
                                    <small class="text-secondary ps-4">Untuk mengelola katalog produk (CRUD raket) dan menerima pesanan masuk.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-custom" placeholder="Contoh: Taufik Hidayat" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-custom" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control form-control-custom" placeholder="08123456789" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Alamat Lengkap (Opsional)</label>
                            <input type="text" name="address" class="form-control form-control-custom" placeholder="Kota / Alamat Pengiriman" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Kata Sandi (Min. 6 Karakter) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-semibold small">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirm" class="form-control form-control-custom" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary-custom btn-lg py-3 fw-bold">
                            <i class="bi bi-person-check-fill me-2"></i> Daftarkan Akun Saya Sekarang
                        </button>
                    </div>

                    <div class="text-center pt-3 border-top border-secondary border-opacity-25 text-secondary small">
                        Sudah punya akun marketplace? 
                        <a href="<?= BASE_URL ?>/login.php<?= !empty($redirectUrl) ? '?redirect=' . urlencode($redirectUrl) : '' ?>" class="text-info fw-bold text-decoration-none">
                            Masuk / Login di sini <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.role-card:hover {
    border-color: #0284c7 !important;
    background-color: #17223b !important;
}
.cursor-pointer {
    cursor: pointer;
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
