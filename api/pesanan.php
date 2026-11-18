<?php
/**
 * REST API Endpoint: Pembuatan Pesanan Baru (Orders)
 * URL:
 *   - POST /api/pesanan.php
 *
 * Mendukung payload format JSON maupun Form-Data.
 * Menggunakan Database Transaction dan Prepared Statements.
 */

require_once __DIR__ . '/helpers.php';

// Validasi Metode HTTP (Hanya POST)
validateMethod(['POST']);

// Ambil input data request (JSON / Form Data)
$input = getJsonInput();

// =========================================================================
// 1. VALIDASI DATA REQUEST (HTTP 400 BAD REQUEST JIKA TIDAK VALID)
// =========================================================================
$errors = [];

$customerName = trim($input['customer_name'] ?? '');
$customerEmail = trim($input['customer_email'] ?? '');
$customerPhone = trim($input['customer_phone'] ?? '');
$shippingAddress = trim($input['shipping_address'] ?? '');
$courier = trim($input['courier'] ?? 'JNE Regular');
$paymentMethod = trim($input['payment_method'] ?? 'Transfer Bank BCA');
$notes = trim($input['notes'] ?? '');
$items = $input['items'] ?? null;

// Validasi Nama Lengkap
if (empty($customerName)) {
    $errors['customer_name'] = 'Nama lengkap pemesan wajib diisi.';
} elseif (mb_strlen($customerName) < 3) {
    $errors['customer_name'] = 'Nama pemesan minimal terdiri dari 3 karakter.';
}

// Validasi Email
if (empty($customerEmail)) {
    $errors['customer_email'] = 'Alamat email pemesan wajib diisi.';
} elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    $errors['customer_email'] = 'Format alamat email tidak valid.';
}

// Validasi Nomor Telepon / WA
if (empty($customerPhone)) {
    $errors['customer_phone'] = 'Nomor WhatsApp / telepon wajib diisi.';
} elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $customerPhone)) {
    $errors['customer_phone'] = 'Format nomor telepon tidak valid (hanya angka, tanda plus/minus, 8-20 digit).';
}

// Validasi Alamat Pengiriman
if (empty($shippingAddress)) {
    $errors['shipping_address'] = 'Alamat pengiriman lengkap wajib diisi.';
} elseif (mb_strlen($shippingAddress) < 8) {
    $errors['shipping_address'] = 'Alamat pengiriman terlalu pendek (minimal 8 karakter).';
}

// Validasi Daftar Item Pesanan
if (empty($items) || !is_array($items)) {
    $errors['items'] = 'Daftar item belanja (items) wajib disertakan dalam bentuk array dan tidak boleh kosong.';
} else {
    // Validasi format tiap item dalam array
    $processedItems = [];
    foreach ($items as $idx => $item) {
        $itemErr = [];
        $productId = $item['product_id'] ?? null;
        $quantity = $item['quantity'] ?? null;

        if (!filter_var($productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $itemErr[] = "product_id pada index {$idx} harus berupa integer positif.";
        }

        if (!filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $itemErr[] = "quantity pada index {$idx} harus berupa integer minimal 1.";
        }

        if (!empty($itemErr)) {
            $errors["items_{$idx}"] = implode(' ', $itemErr);
        } else {
            // Gabungkan kuantitas jika ada product_id yang sama berulang di payload
            $pId = (int)$productId;
            $qty = (int)$quantity;
            if (isset($processedItems[$pId])) {
                $processedItems[$pId] += $qty;
            } else {
                $processedItems[$pId] = $qty;
            }
        }
    }
}

// Jika terdapat error validasi input
if (!empty($errors)) {
    sendError(400, 'Validasi request pesanan gagal. Mohon periksa kembali input Anda.', $errors);
}

// =========================================================================
// 2. EKSEKUSI DATABASE TRANSACTION (VERIFIKASI STOK, INSERT ORDER, UPDATE STOK)
// =========================================================================

// Mulai transaksi MySQL
mysqli_begin_transaction($conn);

try {
    $orderItemsData = [];
    $totalAmount = 0.0;

    // 1. Verifikasi ketersediaan dan kunci baris produk (FOR UPDATE)
    $checkStmt = mysqli_prepare($conn, "SELECT id, name, CAST(price AS DOUBLE) AS price, stock FROM products WHERE id = ? FOR UPDATE");
    if (!$checkStmt) {
        throw new Exception('Gagal menyiapkan query pengecekan produk: ' . mysqli_error($conn), 500);
    }

    foreach ($processedItems as $pId => $qtyRequested) {
        mysqli_stmt_bind_param($checkStmt, 'i', $pId);
        mysqli_stmt_execute($checkStmt);
        $res = mysqli_stmt_get_result($checkStmt);
        $product = mysqli_fetch_assoc($res);

        if (!$product) {
            mysqli_stmt_close($checkStmt);
            throw new Exception("Produk dengan ID {$pId} tidak ditemukan di katalog.", 404);
        }

        $availableStock = (int)$product['stock'];
        if ($availableStock < $qtyRequested) {
            mysqli_stmt_close($checkStmt);
            throw new Exception("Stok untuk raket '{$product['name']}' (ID: {$pId}) tidak mencukupi. Tersisa: {$availableStock} unit, diminta: {$qtyRequested} unit.", 400);
        }

        $itemPrice = (float)$product['price'];
        $subtotal = $itemPrice * $qtyRequested;
        $totalAmount += $subtotal;

        $orderItemsData[] = [
            'product_id' => $pId,
            'product_name' => $product['name'],
            'price' => $itemPrice,
            'quantity' => $qtyRequested,
            'subtotal' => $subtotal
        ];
    }
    mysqli_stmt_close($checkStmt);

    // 2. Generate Nomor Faktur / Invoice Unik
    $invoiceCode = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

    // 3. Simpan Pesanan ke Tabel `orders`
    $orderSql = "INSERT INTO orders (invoice_code, customer_name, customer_email, customer_phone, shipping_address, courier, payment_method, total_amount, status, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
    $orderStmt = mysqli_prepare($conn, $orderSql);
    if (!$orderStmt) {
        throw new Exception('Gagal menyiapkan query penyimpanan pesanan: ' . mysqli_error($conn), 500);
    }

    mysqli_stmt_bind_param(
        $orderStmt,
        'sssssssds',
        $invoiceCode,
        $customerName,
        $customerEmail,
        $customerPhone,
        $shippingAddress,
        $courier,
        $paymentMethod,
        $totalAmount,
        $notes
    );

    if (!mysqli_stmt_execute($orderStmt)) {
        $stmtErr = mysqli_stmt_error($orderStmt);
        mysqli_stmt_close($orderStmt);
        throw new Exception('Gagal menyimpan data pesanan: ' . $stmtErr, 500);
    }

    $orderId = (int)mysqli_insert_id($conn);
    mysqli_stmt_close($orderStmt);

    // 4. Simpan Detail Item ke Tabel `order_items` dan Kurangi Stok `products`
    $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
    $itemStmt = mysqli_prepare($conn, $itemSql);
    if (!$itemStmt) {
        throw new Exception('Gagal menyiapkan query order_items: ' . mysqli_error($conn), 500);
    }

    $stockSql = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $stockStmt = mysqli_prepare($conn, $stockSql);
    if (!$stockStmt) {
        throw new Exception('Gagal menyiapkan query update stok produk: ' . mysqli_error($conn), 500);
    }

    foreach ($orderItemsData as &$itemData) {
        // Insert order_items
        mysqli_stmt_bind_param(
            $itemStmt,
            'iisdid',
            $orderId,
            $itemData['product_id'],
            $itemData['product_name'],
            $itemData['price'],
            $itemData['quantity'],
            $itemData['subtotal']
        );
        if (!mysqli_stmt_execute($itemStmt)) {
            $err = mysqli_stmt_error($itemStmt);
            mysqli_stmt_close($itemStmt);
            mysqli_stmt_close($stockStmt);
            throw new Exception('Gagal menyimpan rincian item pesanan: ' . $err, 500);
        }
        $itemData['id'] = (int)mysqli_insert_id($conn);

        // Update kurangi stok
        mysqli_stmt_bind_param($stockStmt, 'ii', $itemData['quantity'], $itemData['product_id']);
        if (!mysqli_stmt_execute($stockStmt)) {
            $err = mysqli_stmt_error($stockStmt);
            mysqli_stmt_close($itemStmt);
            mysqli_stmt_close($stockStmt);
            throw new Exception('Gagal memperbarui stok produk: ' . $err, 500);
        }
    }
    mysqli_stmt_close($itemStmt);
    mysqli_stmt_close($stockStmt);

    // 5. Komit seluruh transaksi
    mysqli_commit($conn);

    // Format respon sukses (HTTP 201 Created)
    $responsePayload = [
        'order_id' => $orderId,
        'invoice_code' => $invoiceCode,
        'status' => 'pending',
        'customer' => [
            'name' => $customerName,
            'email' => $customerEmail,
            'phone' => $customerPhone,
            'shipping_address' => $shippingAddress
        ],
        'shipping' => [
            'courier' => $courier,
            'payment_method' => $paymentMethod,
            'notes' => $notes ?: null
        ],
        'summary' => [
            'total_items' => count($orderItemsData),
            'total_amount' => $totalAmount,
            'total_amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.')
        ],
        'items' => $orderItemsData,
        'created_at' => date('Y-m-d H:i:s')
    ];

    sendSuccess("Pesanan berhasil dibuat dengan nomor invoice {$invoiceCode}.", $responsePayload, 201);

} catch (Exception $e) {
    mysqli_rollback($conn);
    $statusCode = in_array($e->getCode(), [400, 404, 500], true) ? $e->getCode() : 500;
    sendError($statusCode, $e->getMessage());
}
