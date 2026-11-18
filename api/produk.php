<?php
/**
 * REST API Endpoint: Produk Marketplace
 * URL:
 *   - GET /api/produk.php              (Daftar seluruh produk / filter pencarian)
 *   - GET /api/produk.php?id=1         (Detail produk tertentu berdasarkan ID)
 */

require_once __DIR__ . '/helpers.php';

// Validasi Metode HTTP (Hanya GET)
validateMethod(['GET']);

// =========================================================================
// 1. DETAIL PRODUK (JIKA PARAMETER 'id' DIBERIKAN)
// =========================================================================
if (isset($_GET['id'])) {
    $idParam = $_GET['id'];

    // Validasi parameter ID: harus bilangan bulat positif >= 1
    if (!filter_var($idParam, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        sendError(400, "Parameter 'id' tidak valid. ID produk harus berupa bilangan bulat positif.");
    }

    $productId = (int)$idParam;

    // Prepared statement untuk mengambil detail produk lengkap beserta kategori & seller
    $sql = "SELECT 
                p.id,
                p.seller_id,
                COALESCE(u.name, 'SmashZone Store') AS seller_name,
                p.category_id,
                COALESCE(c.name, 'Uncategorized') AS category_name,
                c.slug AS category_slug,
                p.name,
                p.brand,
                CAST(p.price AS DOUBLE) AS price,
                p.stock,
                p.weight_grip,
                p.tension_lbs,
                p.balance_point,
                p.flexibility,
                p.description,
                p.image,
                p.is_featured,
                p.created_at
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.seller_id = u.id
            WHERE p.id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        sendError(500, 'Gagal menyiapkan query database: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $productId);
    if (!mysqli_stmt_execute($stmt)) {
        sendError(500, 'Gagal mengeksekusi query database: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Jika produk tidak ditemukan
    if (!$product) {
        sendError(404, "Produk dengan ID {$productId} tidak ditemukan.");
    }

    // Format tipe data produk
    $product['id'] = (int)$product['id'];
    $product['seller_id'] = (int)$product['seller_id'];
    $product['category_id'] = (int)$product['category_id'];
    $product['price'] = (float)$product['price'];
    $product['stock'] = (int)$product['stock'];
    $product['is_featured'] = (int)$product['is_featured'];
    $product['image_url'] = getProductImageUrlApi($product['image']);

    // Ambil ulasan (reviews) untuk produk ini
    $reviewSql = "SELECT id, user_name, rating, comment, created_at FROM reviews WHERE product_id = ? ORDER BY created_at DESC";
    $revStmt = mysqli_prepare($conn, $reviewSql);
    mysqli_stmt_bind_param($revStmt, 'i', $productId);
    mysqli_stmt_execute($revStmt);
    $revRes = mysqli_stmt_get_result($revStmt);

    $reviews = [];
    $totalRating = 0;
    while ($rev = mysqli_fetch_assoc($revRes)) {
        $rev['id'] = (int)$rev['id'];
        $rev['rating'] = (int)$rev['rating'];
        $totalRating += $rev['rating'];
        $reviews[] = $rev;
    }
    mysqli_stmt_close($revStmt);

    $reviewCount = count($reviews);
    $product['rating'] = [
        'average' => $reviewCount > 0 ? round($totalRating / $reviewCount, 1) : 0,
        'count' => $reviewCount,
        'reviews' => $reviews
    ];

    sendSuccess("Berhasil mendapatkan detail produk ID {$productId}.", $product, 200);
}

// =========================================================================
// 2. DAFTAR SELURUH PRODUK (DENGAN FILTER & SEARCH OPSIONAL)
// =========================================================================
$whereClauses = [];
$params = [];
$paramTypes = '';

// Filter Kategori
if (!empty($_GET['category_id'])) {
    if (!filter_var($_GET['category_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        sendError(400, "Parameter 'category_id' harus berupa bilangan bulat positif.");
    }
    $whereClauses[] = "p.category_id = ?";
    $params[] = (int)$_GET['category_id'];
    $paramTypes .= 'i';
}

// Filter Brand
if (!empty($_GET['brand'])) {
    $whereClauses[] = "LOWER(p.brand) = LOWER(?)";
    $params[] = trim($_GET['brand']);
    $paramTypes .= 's';
}

// Filter Pencarian (Nama, Brand, Deskripsi)
$searchKeyword = $_GET['search'] ?? ($_GET['q'] ?? '');
if (!empty($searchKeyword)) {
    $whereClauses[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $likePattern = '%' . trim($searchKeyword) . '%';
    $params[] = $likePattern;
    $params[] = $likePattern;
    $params[] = $likePattern;
    $paramTypes .= 'sss';
}

// Filter Unggulan (is_featured)
if (isset($_GET['featured'])) {
    $featuredVal = in_array(strtolower((string)$_GET['featured']), ['1', 'true', 'yes'], true) ? 1 : 0;
    $whereClauses[] = "p.is_featured = ?";
    $params[] = $featuredVal;
    $paramTypes .= 'i';
}

// Pengurutan (Sorting)
$orderBy = "p.id ASC";
$sort = $_GET['sort'] ?? '';
switch ($sort) {
    case 'price_asc':
        $orderBy = "p.price ASC";
        break;
    case 'price_desc':
        $orderBy = "p.price DESC";
        break;
    case 'latest':
        $orderBy = "p.created_at DESC";
        break;
    case 'name_asc':
        $orderBy = "p.name ASC";
        break;
    case 'stock_desc':
        $orderBy = "p.stock DESC";
        break;
}

$sql = "SELECT 
            p.id,
            p.seller_id,
            COALESCE(u.name, 'SmashZone Store') AS seller_name,
            p.category_id,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            c.slug AS category_slug,
            p.name,
            p.brand,
            CAST(p.price AS DOUBLE) AS price,
            p.stock,
            p.weight_grip,
            p.tension_lbs,
            p.balance_point,
            p.flexibility,
            p.image,
            p.is_featured,
            p.created_at
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.seller_id = u.id";

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

$sql .= " ORDER BY " . $orderBy;

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    sendError(500, 'Gagal menyiapkan query database: ' . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);
}

if (!mysqli_stmt_execute($stmt)) {
    sendError(500, 'Gagal mengeksekusi query database: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = (int)$row['id'];
    $row['seller_id'] = (int)$row['seller_id'];
    $row['category_id'] = (int)$row['category_id'];
    $row['price'] = (float)$row['price'];
    $row['stock'] = (int)$row['stock'];
    $row['is_featured'] = (int)$row['is_featured'];
    $row['image_url'] = getProductImageUrlApi($row['image']);
    $products[] = $row;
}
mysqli_stmt_close($stmt);

sendSuccess('Daftar produk berhasil diambil.', [
    'total' => count($products),
    'filters' => [
        'category_id' => $_GET['category_id'] ?? null,
        'brand' => $_GET['brand'] ?? null,
        'search' => $searchKeyword ?: null,
        'sort' => $sort ?: 'default (id asc)'
    ],
    'products' => $products
], 200);
