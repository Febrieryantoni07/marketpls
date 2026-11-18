    </main>

    <!-- Footer -->
    <footer class="footer-custom mt-auto">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-3 p-2 bg-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-trophy-fill text-white"></i>
                        </div>
                        <h5 class="fw-bold text-white mb-0">SmashZone</h5>
                    </div>
                    <p class="text-secondary small">
                        Aplikasi Web Marketplace Tradisional Spesialis Raket Badminton Original. Menghubungkan penjual peralatan bulutangkis terpercaya dengan para pemain dari tingkat pemula hingga atlet profesional.
                    </p>
                    <div class="d-flex gap-3 text-secondary fs-5">
                        <a href="#" class="text-secondary hover-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Kategori Raket</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
                        <li><a href="<?= BASE_URL ?>/index.php?category=1" class="text-decoration-none text-secondary hover-primary">Power & Smash</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?category=2" class="text-decoration-none text-secondary hover-primary">Speed & Drive</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?category=3" class="text-decoration-none text-secondary hover-primary">All-Around Control</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?category=4" class="text-decoration-none text-secondary hover-primary">Beginner Friendly</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Menu Sistem</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
                        <li><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none text-secondary">Katalog Produk</a></li>
                        <li><a href="<?= BASE_URL ?>/cart.php" class="text-decoration-none text-secondary">Keranjang Belanja</a></li>
                        <li><a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none text-secondary">Kelola Data Produk (CRUD)</a></li>
                        <li><a href="<?= BASE_URL ?>/admin/orders.php" class="text-decoration-none text-secondary">Data Transaksi Pesanan</a></li>
                        <li><a href="<?= BASE_URL ?>/setup.php" class="text-decoration-none text-secondary">Setup & Migrasi Database</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Spesifikasi Teknologi</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">PHP 8.2 Native</span>
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">MySQLi Prepared</span>
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">Bootstrap 5.3</span>
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">InfinityFree Ready</span>
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">Session Cart</span>
                        <span class="badge bg-secondary bg-opacity-25 border border-secondary text-light">ERD Relasional</span>
                    </div>
                    <div class="mt-3 text-secondary small">
                        <i class="bi bi-shield-check text-success me-1"></i> Garansi 100% Original & String Tension Guaranteed.
                    </div>
                </div>
            </div>

            <hr class="border-secondary border-opacity-25">

            <div class="row align-items-center small text-secondary py-2">
                <div class="col-md-6 text-center text-md-start">
                    &copy; <?= date('Y') ?> <strong>SmashZone Badminton Marketplace</strong>. Tugas Perancangan & Pembangunan Aplikasi Web.
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> untuk Komunitas Bulutangkis Indonesia.
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
