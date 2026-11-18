<?php
/**
 * Konfigurasi Koneksi Database MySQLi
 * Mendukung Lingkungan Lokal (XAMPP) & Hosting (InfinityFree)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi Host Lokal vs InfinityFree / Live Server
$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($hostName === 'localhost' || $hostName === '127.0.0.1' || strpos($hostName, 'localhost:') !== false);

if ($isLocal) {
    // Pengaturan XAMPP Lokal
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'badminton_marketplace');
    define('BASE_URL', '/web_marketpls');
} else {
    // Pengaturan Hosting InfinityFree (Sesuaikan dengan data dari vPanel Anda)
    // Contoh host InfinityFree: sql123.infinityfree.com
    define('DB_HOST', 'sqlxxx.infinityfree.com'); 
    define('DB_USER', 'if0_xxxxxxxx');
    define('DB_PASS', 'PASSWORD_ANDA');
    define('DB_NAME', 'if0_xxxxxxxx_badminton_marketplace');
    define('BASE_URL', '');
}

// Inisialisasi Koneksi MySQLi
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek status koneksi
if (!$conn) {
    $dbError = mysqli_connect_error();
    // Jika request mengarah ke API atau mengharapkan JSON, kembalikan respons JSON 500
    if (strpos($_SERVER['PHP_SELF'] ?? '', '/api/') !== false || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'status' => 'error',
            'code' => 500,
            'message' => 'Koneksi ke database gagal: ' . $dbError
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Jika database belum dibuat di lokal, arahkan atau beri panduan
    if (strpos($_SERVER['PHP_SELF'], 'setup.php') === false) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Koneksi Database Belum Dikonfigurasi - SmashZone</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
            <style>
                body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
                .card-setup { background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; }
            </style>
        </head>
        <body class="d-flex align-items-center min-vh-100 py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="card card-setup p-4 p-md-5 shadow-lg text-center">
                            <div class="mb-3">
                                <i class="bi bi-database-exclamation text-warning display-3"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Database Belum Siap / Belum Terhubung</h2>
                            <p class="text-secondary mb-4">
                                Aplikasi marketplace tidak dapat terhubung ke database <code><?= htmlspecialchars(DB_NAME) ?></code> di host <code><?= htmlspecialchars(DB_HOST) ?></code>.
                                <br><small class="text-danger">(Pesan Error: <?= htmlspecialchars($dbError) ?>)</small>
                            </p>

                            <div class="alert alert-info text-start d-flex align-items-start gap-3">
                                <i class="bi bi-info-circle-fill fs-4 flex-shrink-0"></i>
                                <div>
                                    <strong>Langkah Mudah Mengaktifkan:</strong>
                                    <ol class="mb-0 mt-2 ps-3">
                                        <li>Pastikan modul <strong>MySQL di XAMPP Control Panel</strong> sudah berstatus <em>Start</em> (Warna Hijau).</li>
                                        <li>Klik tombol <strong>Setup Otomatis Database</strong> di bawah untuk membuat database dan tabel badminton secara instan.</li>
                                        <li>Atau Anda bisa import file <code>database.sql</code> secara manual via <strong>phpMyAdmin</strong>.</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                                <a href="<?= BASE_URL ?>/setup.php" class="btn btn-primary btn-lg px-4 fw-bold">
                                    <i class="bi bi-magic me-2"></i> Jalankan Setup Database Otomatis
                                </a>
                                <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-light btn-lg px-4">
                                    <i class="bi bi-box-arrow-up-right me-2"></i> Buka phpMyAdmin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} else {
    // Set charset UTF-8
    mysqli_set_charset($conn, 'utf8mb4');
}
?>