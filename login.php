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

$pageTitle = 'Masuk / Login Akun';
$redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectPost = sanitize($_POST['redirect'] ?? '');

    if (empty($email)) {
        $errors[] = 'Alamat email wajib diisi.';
    }
    if (empty($password)) {
        $errors[] = 'Kata sandi / password wajib diisi.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role, phone, address FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($user) {
            $isPasswordValid = false;

            // Cek password hash bcrypt
            if (password_verify($password, $user['password'])) {
                $isPasswordValid = true;
            } elseif ($user['password'] === $password) {
                // Fallback jika plain text (misal data seeder awal), lalu auto-rehash
                $isPasswordValid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($upStmt, "si", $newHash, $user['id']);
                mysqli_stmt_execute($upStmt);
                mysqli_stmt_close($upStmt);
            }

            if ($isPasswordValid) {
                // Simpan data login ke session
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'address' => $user['address']
                ];

                $roleLabel = ($user['role'] === 'seller' || $user['role'] === 'admin') ? 'Penjual / Admin' : 'Pembeli';
                setFlashMessage('success', "Selamat datang, <strong>{$user['name']}</strong>! Anda berhasil masuk sebagai <strong>{$roleLabel}</strong>.");

                // Redirect sesuai prioritas
                if (!empty($redirectPost) && strpos($redirectPost, 'http') === false) {
                    header('Location: ' . $redirectPost);
                    exit;
                }

                if ($user['role'] === 'seller' || $user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/index.php');
                } else {
                    header('Location: ' . BASE_URL . '/index.php');
                }
                exit;
            } else {
                $errors[] = 'Password yang Anda masukkan salah. Silakan coba lagi.';
            }
        } else {
            $errors[] = 'Akun dengan email tersebut belum terdaftar. Silakan daftar akun baru terlebih dahulu.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <!-- Header Card -->
            <div class="card bg-card border-0 rounded-4 p-4 p-md-5 shadow-2xl position-relative overflow-hidden">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.2), rgba(16, 185, 129, 0.2)); border: 1px solid rgba(56, 189, 248, 0.3);">
                        <i class="bi bi-person-fill-lock fs-1 text-info"></i>
                    </div>
                    <h3 class="fw-extrabold text-white mb-1">Masuk ke SmashZone</h3>
                    <p class="text-secondary small">Akses akun Pembeli atau masuk ke Dashboard Penjual / Admin</p>
                </div>

                <!-- Info Banner Daftar Dulu -->
                <div class="alert alert-dark border-secondary border-opacity-50 rounded-3 p-3 mb-4 text-center">
                    <div class="small text-secondary mb-1">Belum memiliki akun marketplace?</div>
                    <a href="<?= BASE_URL ?>/register.php<?= !empty($redirectUrl) ? '?redirect=' . urlencode($redirectUrl) : '' ?>" class="btn btn-outline-info btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-person-plus-fill me-1"></i> Daftar Akun Baru Sekarang
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <strong class="small">Gagal Masuk:</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login.php">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">

                    <div class="mb-3">
                        <label class="form-label text-white fw-semibold small">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="emailInput" name="email" class="form-control form-control-custom" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label text-white fw-semibold small mb-1">Kata Sandi</label>
                            <a href="<?= BASE_URL ?>/register.php" class="text-info text-decoration-none small" style="font-size: 0.75rem;">Lupa kata sandi?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" id="passwordInput" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
                            <button type="button" class="btn btn-outline-secondary border-secondary border-opacity-50 text-secondary" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary-custom btn-lg py-3 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                        </button>
                    </div>
                </form>

                <!-- Tombol Cepat Akun Demo -->
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <small class="text-secondary fw-semibold d-block text-center mb-2">Akun Demo Pengujian:</small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-dark border border-secondary border-opacity-50 btn-sm text-info flex-grow-1" onclick="fillDemo('admin@smashzone.com', 'password123')">
                            <i class="bi bi-shield-lock me-1"></i> Admin / Penjual
                        </button>
                        <button type="button" class="btn btn-dark border border-secondary border-opacity-50 btn-sm text-success flex-grow-1" onclick="fillDemo('budi@gmail.com', 'password123')">
                            <i class="bi bi-person me-1"></i> Pembeli / User
                        </button>
                    </div>
                    <small class="text-muted d-block text-center mt-2" style="font-size: 0.72rem;">Klik tombol di atas untuk mengisi otomatis data login demo</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function fillDemo(email, pass) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = pass;
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
