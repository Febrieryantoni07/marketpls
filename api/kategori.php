<?php
/**
 * REST API Endpoint: Kategori Produk
 * URL:
 *   - GET /api/kategori.php          (Daftar seluruh kategori)
 *   - GET /api/kategori.php?id=1     (Detail kategori tertentu berdasarkan ID)
 */

require_once __DIR__ . '/helpers.php';

// Validasi Metode HTTP (Hanya GET)
validateMethod(['GET']);

// Cek apakah ada parameter ID
if (isset($_GET['id'])) {
    $idParam = $_GET['id'];

    // Validasi parameter ID harus integer positif
    if (!filter_var($idParam, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        sendError(400, "Parameter 'id' tidak valid. ID kategori harus berupa bilangan bulat positif.");
    }

    $categoryId = (int)$idParam;

    // Prepared Statement untuk mengambil detail kategori
    $sql = "SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS total_products
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            WHERE c.id = ?
            GROUP BY c.id, c.name, c.slug, c.description";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        sendError(500, 'Gagal menyiapkan query database: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    if (!mysqli_stmt_execute($stmt)) {
        sendError(500, 'Gagal mengeksekusi query database: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$category) {
        sendError(404, "Kategori dengan ID {$categoryId} tidak ditemukan.");
    }

    // Ambil daftar produk di bawah kategori ini
    $prodSql = "SELECT id, name, brand, CAST(price AS DOUBLE) AS price, stock, image, is_featured
                FROM products
                WHERE category_id = ?
                ORDER BY id ASC";
    $prodStmt = mysqli_prepare($conn, $prodSql);
    mysqli_stmt_bind_param($prodStmt, 'i', $categoryId);
    mysqli_stmt_execute($prodStmt);
    $prodRes = mysqli_stmt_get_result($prodStmt);

    $products = [];
    while ($row = mysqli_fetch_assoc($prodRes)) {
        $row['id'] = (int)$row['id'];
        $row['price'] = (float)$row['price'];
        $row['stock'] = (int)$row['stock'];
        $row['is_featured'] = (int)$row['is_featured'];
        $row['image_url'] = getProductImageUrlApi($row['image']);
        $products[] = $row;
    }
    mysqli_stmt_close($prodStmt);

    $category['id'] = (int)$category['id'];
    $category['total_products'] = (int)$category['total_products'];
    $category['products'] = $products;

    sendSuccess("Berhasil mengambil data kategori ID {$categoryId}.", $category, 200);
}

// Default: Ambil Seluruh Daftar Kategori
$sql = "SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS total_products
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id, c.name, c.slug, c.description
        ORDER BY c.id ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    sendError(500, 'Gagal menyiapkan query database: ' . mysqli_error($conn));
}

if (!mysqli_stmt_execute($stmt)) {
    sendError(500, 'Gagal mengeksekusi query database: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$categories = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = (int)$row['id'];
    $row['total_products'] = (int)$row['total_products'];
    $categories[] = $row;
}
mysqli_stmt_close($stmt);

sendSuccess('Daftar kategori berhasil diambil.', [
    'total' => count($categories),
    'categories' => $categories
], 200);
