<?php
/**
 * REST API Index & Documentation - SmashZone Badminton Marketplace
 * URL: GET /api/index.php atau /api/
 */

require_once __DIR__ . '/helpers.php';

validateMethod(['GET']);

$baseApiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/web_marketpls/api';

$apiDocs = [
    'name' => 'SmashZone Badminton Marketplace REST API',
    'version' => '1.0.0',
    'environment' => 'PHP Native, MySQLi, MySQL, XAMPP',
    'base_url' => $baseApiUrl,
    'endpoints' => [
        [
            'endpoint' => '/api/produk.php',
            'method' => 'GET',
            'description' => 'Menampilkan daftar seluruh produk raket badminton dengan dukungan pencarian dan filter.',
            'query_parameters' => [
                'category_id' => 'Filter berdasarkan ID kategori (integer positif)',
                'brand' => 'Filter berdasarkan nama brand (e.g. Yonex, Victor, Li-Ning)',
                'search' => 'Pencarian kata kunci pada nama, brand, atau deskripsi',
                'featured' => 'Filter produk unggulan (1 atau 0)',
                'sort' => 'Pengurutan (price_asc, price_desc, latest, name_asc, stock_desc)'
            ],
            'example_request' => $baseApiUrl . '/produk.php?brand=Yonex'
        ],
        [
            'endpoint' => '/api/produk.php?id={id}',
            'method' => 'GET',
            'description' => 'Menampilkan detail lengkap satu produk raket badminton berdasarkan ID, beserta rating dan ulasan pembeli.',
            'query_parameters' => [
                'id' => 'ID unik produk (wajib, integer positif)'
            ],
            'example_request' => $baseApiUrl . '/produk.php?id=1'
        ],
        [
            'endpoint' => '/api/kategori.php',
            'method' => 'GET',
            'description' => 'Menampilkan daftar seluruh kategori produk raket badminton beserta jumlah produk di setiap kategori.',
            'query_parameters' => [
                'id' => 'Opsional: ID kategori untuk melihat detail dan produk di bawah kategori tersebut'
            ],
            'example_request' => $baseApiUrl . '/kategori.php'
        ],
        [
            'endpoint' => '/api/pesanan.php',
            'method' => 'POST',
            'description' => 'Menyimpan transaksi pesanan baru ke tabel orders dan order_items, serta mengurangi stok produk otomatis dengan MySQL Transaction.',
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'payload_sample' => [
                'customer_name' => 'Ahmad Fikri',
                'customer_email' => 'ahmad.fikri@example.com',
                'customer_phone' => '081234567890',
                'shipping_address' => 'Jl. Asia Afrika No. 120, Kota Bandung, Jawa Barat 40112',
                'courier' => 'JNE Regular',
                'payment_method' => 'Transfer Bank BCA',
                'notes' => 'Harap sertakan pelindung pipa PVC.',
                'items' => [
                    [
                        'product_id' => 1,
                        'quantity' => 1
                    ]
                ]
            ],
            'example_request' => $baseApiUrl . '/pesanan.php'
        ]
    ],
    'status_codes' => [
        '200 OK' => 'Permintaan GET berhasil diproses.',
        '201 Created' => 'Pesanan baru berhasil dibuat dan disimpan.',
        '400 Bad Request' => 'Format payload atau parameter request tidak valid.',
        '404 Not Found' => 'Data produk atau kategori tidak ditemukan.',
        '405 Method Not Allowed' => 'Metode HTTP tidak diizinkan untuk endpoint tersebut.',
        '500 Internal Server Error' => 'Terjadi kesalahan pada database MySQL atau server.'
    ]
];

sendSuccess('Dokumentasi REST API SmashZone Marketplace', $apiDocs, 200);
