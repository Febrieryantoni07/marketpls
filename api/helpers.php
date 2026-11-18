<?php
/**
 * Helper REST API - SmashZone Marketplace
 * Mengatur header CORS, format respon JSON, error handler, dan validasi HTTP method.
 */

// Header CORS & Konten JSON
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tangani Preflight Request OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hubungkan ke Database
require_once __DIR__ . '/../config/database.php';

// Verifikasi koneksi MySQLi
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Gagal terhubung ke database MySQL. Pastikan service MySQL XAMPP aktif.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Format standar respon JSON
 *
 * @param int $code HTTP Status Code
 * @param string $status 'success' atau 'error'
 * @param string $message Pesan deskriptif
 * @param mixed $data Data payload (opsional)
 * @param mixed $errors Detail error validasi (opsional)
 */
function sendResponse(int $code, string $status, string $message, $data = null, $errors = null) {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');

    $response = [
        'status' => $status,
        'code' => $code,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Kirim respon sukses
 */
function sendSuccess(string $message, $data = null, int $code = 200) {
    sendResponse($code, 'success', $message, $data);
}

/**
 * Kirim respon error
 */
function sendError(int $code, string $message, $errors = null) {
    sendResponse($code, 'error', $message, null, $errors);
}

/**
 * Validasi HTTP Method request
 * Jika tidak cocok, kirimkan respon 405 Method Not Allowed
 */
function validateMethod(array $allowedMethods) {
    $currentMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($currentMethod, $allowedMethods, true)) {
        header('Allow: ' . implode(', ', $allowedMethods));
        sendError(405, "Metode HTTP '{$currentMethod}' tidak diizinkan untuk endpoint ini. Metode yang diizinkan: " . implode(', ', $allowedMethods));
    }
}

/**
 * Ambil payload request JSON atau Form Data
 */
function getJsonInput(): array {
    $rawInput = file_get_contents('php://input');

    if (!empty($rawInput)) {
        $data = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendError(400, 'Format payload JSON tidak valid: ' . json_last_error_msg());
        }
        if (!is_array($data)) {
            sendError(400, 'Payload JSON harus berupa object atau associative array.');
        }
        return $data;
    }

    // Fallback jika dikirim via application/x-www-form-urlencoded atau multipart/form-data
    if (!empty($_POST)) {
        return $_POST;
    }

    return [];
}

/**
 * Sanitasi string
 */
function cleanString($value): string {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

/**
 * Dapatkan URL gambar lengkap
 */
function getProductImageUrlApi(?string $imageName): string {
    if (empty($imageName)) {
        $imageName = 'default_racket.svg';
    }
    if (filter_var($imageName, FILTER_VALIDATE_URL)) {
        return $imageName;
    }
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$scheme}://{$host}/web_marketpls/assets/images/{$imageName}";
}
