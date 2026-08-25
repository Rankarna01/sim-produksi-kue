<?php
// config/database.php

// Deteksi otomatis apakah berjalan di Localhost atau Server Hosting
$is_localhost = (
    isset($_SERVER['HTTP_HOST']) && 
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)
);

if ($is_localhost) {
    // Konfigurasi Localhost (XAMPP/Laragon)
    $host   = 'localhost';
    $dbname = 'sim-kue';
    $user   = 'root';
    $pass   = '';
} else {
    // Konfigurasi Server Produksi (Hostinger)
    $host   = 'localhost';
    $dbname = 'u672726995_lovecakes22';
    $user   = 'u672726995_lovecakes22';
    $pass   = 'Randy2005_';
}

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set error mode ke Exception agar mudah di-debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_warehouse_stocks (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            product_id INT NOT NULL, 
            warehouse_id INT NOT NULL, 
            stock INT NOT NULL DEFAULT 0, 
            UNIQUE KEY unique_prod_wh (product_id, warehouse_id)
        )");
        $pdo->exec("INSERT IGNORE INTO product_warehouse_stocks (product_id, warehouse_id, stock) SELECT id, stock, 1 FROM products");
    } catch (Exception $e) {}

} catch (PDOException $e) {
    // Tangani error koneksi secara aman agar TIDAK memicu HTTP 500 Internal Server Error
    $pesan_error = "Koneksi Database Gagal: " . $e->getMessage();

    // Jika dipanggil via AJAX/Fetch (seperti form login)
    header('Content-Type: application/json');
    echo json_encode([
        'status'  => 'error',
        'message' => $pesan_error
    ]);
    exit;
}
?>