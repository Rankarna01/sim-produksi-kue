<?php
session_start();

// Deteksi Routing
$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
$base_url = $is_localhost ? '/sim-produksi-kue/' : '/';

// Hapus semua variabel session
session_unset();

// Hancurkan session secara keseluruhan dari server
session_destroy();

// Redirect ke halaman login utama
header("Location: " . $base_url . "index.php");
exit();
?>