/**
 * Main JavaScript - SmashZone Badminton Marketplace
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Tooltip Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto dismiss alert setelah 4 detik
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 4000);
    });
});

/**
 * Helper Tambah / Kurang Kuantitas pada Detail Produk atau Cart
 */
function changeQty(delta, maxStock = 999) {
    const qtyInput = document.getElementById('productQty');
    if (!qtyInput) return;

    let currentVal = parseInt(qtyInput.value) || 1;
    let newVal = currentVal + delta;

    if (newVal < 1) newVal = 1;
    if (newVal > maxStock) newVal = maxStock;

    qtyInput.value = newVal;
}
