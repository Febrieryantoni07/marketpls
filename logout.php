<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user'])) {
    $userName = $_SESSION['user']['name'] ?? 'Pengguna';
    unset($_SESSION['user']);
    setFlashMessage('info', "Sampai jumpa, <strong>{$userName}</strong>. Anda telah berhasil keluar (logout).");
}

header('Location: ' . BASE_URL . '/login.php');
exit;
?>