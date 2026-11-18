<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Ambil nama produk untuk notifikasi
    $stmt = mysqli_prepare($conn, "SELECT name, image FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($prod) {
        $name = $prod['name'];

        // Hapus dari database
        $delStmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($delStmt, "i", $id);
        
        if (mysqli_stmt_execute($delStmt)) {
            setFlashMessage('success', "Raket '{$name}' berhasil dihapus dari katalog!");
        } else {
            setFlashMessage('danger', "Gagal menghapus raket: " . mysqli_error($conn));
        }
        mysqli_stmt_close($delStmt);
    } else {
        setFlashMessage('danger', 'Data raket tidak ditemukan.');
    }
} else {
    setFlashMessage('danger', 'ID Produk tidak valid.');
}

header('Location: ' . BASE_URL . '/admin/index.php');
exit;
?>