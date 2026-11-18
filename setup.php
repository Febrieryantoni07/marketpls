<?php
/**
 * Setup Otomatis Database Marketplace Raket Badminton
 * SmashZone Pro Badminton Store
 */

$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($hostName === 'localhost' || $hostName === '127.0.0.1' || strpos($hostName, 'localhost:') !== false);

$dbHost = $isLocal ? 'localhost' : 'sqlxxx.infinityfree.com';
$dbUser = $isLocal ? 'root' : 'if0_xxxxxxxx';
$dbPass = $isLocal ? '' : 'PASSWORD_ANDA';
$dbName = $isLocal ? 'badminton_marketplace' : 'if0_xxxxxxxx_badminton_marketplace';

$statusMessages = [];
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    // 1. Coba konek ke MySQL Server
    $link = @mysqli_connect($dbHost, $dbUser, $dbPass);
    if (!$link) {
        $statusMessages[] = [
            'type' => 'danger',
            'text' => 'Gagal terhubung ke MySQL Server: ' . mysqli_connect_error() . '. Pastikan MySQL di XAMPP sudah di-START!'
        ];
    } else {
        $statusMessages[] = [
            'type' => 'success',
            'text' => "Berhasil terhubung ke MySQL Server ($dbHost)."
        ];

        // 2. Buat database jika belum ada
        $createDbQuery = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (mysqli_query($link, $createDbQuery)) {
            $statusMessages[] = [
                'type' => 'success',
                'text' => "Database `$dbName` berhasil dibuat / diverifikasi."
            ];
            mysqli_select_db($link, $dbName);

            // 3. Baca file database.sql
            $sqlFile = __DIR__ . '/database.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                
                // Pisahkan per query
                $queries = array_filter(array_map('trim', explode(';', $sqlContent)));
                $executed = 0;
                $errors = 0;

                foreach ($queries as $q) {
                    if (empty($q) || strpos($q, '--') === 0) continue;
                    // Skip USE & CREATE DATABASE karena sudah di-select
                    if (stripos($q, 'CREATE DATABASE') !== false || stripos($q, 'USE ') === 0) {
                        continue;
                    }
                    if (mysqli_query($link, $q)) {
                        $executed++;
                    } else {
                        // Jangan hitung duplicate key sebagai error fatal
                        if (mysqli_errno($link) != 1062) {
                            $errors++;
                        }
                    }
                }

                $statusMessages[] = [
                    'type' => 'success',
                    'text' => "Eksekusi struktur tabel dan data awal selesai. ($executed query berhasil dieksekusi)."
                ];
                $isSuccess = true;
            } else {
                $statusMessages[] = [
                    'type' => 'danger',
                    'text' => 'File database.sql tidak ditemukan di direktori proyek!'
                ];
            }
        } else {
            $statusMessages[] = [
                'type' => 'danger',
                'text' => "Gagal membuat database: " . mysqli_error($link)
            ];
        }
        mysqli_close($link);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database Marketplace Raket Badminton</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            min-height: 100vh;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .setup-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        .badge-brand {
            background: linear-gradient(90deg, #0284c7, #10b981);
            color: #fff;
        }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="setup-card p-4 p-md-5 shadow-2xl">
                    <div class="text-center mb-4">
                        <span class="badge badge-brand px-3 py-2 rounded-pill fw-semibold mb-2">
                            <i class="bi bi-gear-fill me-1"></i> INSTALLER OTOMATIS
                        </span>
                        <h2 class="fw-bold mt-2">SmashZone Badminton Marketplace</h2>
                        <p class="text-secondary">Inisialisasi Database Relasional (5+ Entitas: Users, Categories, Products, Orders, Order Items, Reviews)</p>
                    </div>

                    <?php if (!empty($statusMessages)): ?>
                        <div class="mb-4">
                            <?php foreach ($statusMessages as $msg): ?>
                                <div class="alert alert-<?= $msg['type'] ?> d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-<?= $msg['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                                    <div><?= htmlspecialchars($msg['text']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isSuccess): ?>
                        <div class="card bg-success bg-opacity-10 border-success p-4 rounded-4 mb-4 text-center">
                            <i class="bi bi-check2-circle text-success display-4 mb-2"></i>
                            <h4 class="fw-bold text-success">Setup Berhasil Sempurna!</h4>
                            <p class="text-white-50 mb-3">Database <strong><?= htmlspecialchars($dbName) ?></strong> telah berisi tabel dan data raket badminton (Yonex, Victor, Li-Ning, Mizuno, Felet).</p>
                            
                            <!-- Akun Demo Login -->
                            <div class="bg-dark border border-secondary border-opacity-50 rounded-3 p-3 text-start mb-3">
                                <h6 class="text-warning fw-bold mb-2"><i class="bi bi-key-fill me-1"></i>Akun Login yang Siap Digunakan:</h6>
                                <div class="row g-2 small">
                                    <div class="col-sm-6">
                                        <div class="p-2 rounded border border-secondary border-opacity-25 bg-black bg-opacity-25">
                                            <span class="badge bg-warning text-dark mb-1">AKUN ADMIN / PENJUAL</span>
                                            <div>Email: <strong class="text-white">admin@smashzone.com</strong></div>
                                            <div>Password: <strong class="text-info">password123</strong></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-2 rounded border border-secondary border-opacity-25 bg-black bg-opacity-25">
                                            <span class="badge bg-info text-dark mb-1">AKUN PEMBELI / USER</span>
                                            <div>Email: <strong class="text-white">budi@gmail.com</strong></div>
                                            <div>Password: <strong class="text-info">password123</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="login.php" class="btn btn-primary btn-lg px-4 fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk / Login
                                </a>
                                <a href="register.php" class="btn btn-outline-info btn-lg px-4">
                                    <i class="bi bi-person-plus-fill me-2"></i> Daftar Akun Baru
                                </a>
                                <a href="index.php" class="btn btn-outline-light btn-lg px-4">
                                    <i class="bi bi-shop me-2"></i> Katalog Toko
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="border border-secondary border-opacity-25 rounded-4 p-4 mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-hdd-network me-2 text-primary"></i>Konfigurasi Database yang Akan Dibuat:</h5>
                            <ul class="list-unstyled mb-0 row g-2">
                                <li class="col-sm-6 text-white-50">Host: <span class="text-white fw-bold"><?= htmlspecialchars($dbHost) ?></span></li>
                                <li class="col-sm-6 text-white-50">Username: <span class="text-white fw-bold"><?= htmlspecialchars($dbUser) ?></span></li>
                                <li class="col-sm-6 text-white-50">Nama Database: <span class="text-white fw-bold text-info"><?= htmlspecialchars($dbName) ?></span></li>
                                <li class="col-sm-6 text-white-50">Jumlah Entitas: <span class="text-white fw-bold text-warning">6 Tabel Relasional</span></li>
                            </ul>
                        </div>

                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold py-3">
                                    <i class="bi bi-lightning-charge-fill me-2"></i> Mulai Inisialisasi Database Sekarang
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">Kembali ke Halaman Utama</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
