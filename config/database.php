<?php
// config/database.php

$host = 'localhost';
$dbname = 'sim-kue';
$user = 'root'; // Sesuaikan dengan user MySQL kamu (biasanya 'root' di XAMPP/Laragon)
$pass = '';     // Sesuaikan dengan password MySQL kamu (biasanya kosong)

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set error mode ke Exception agar mudah di-debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_warehouse_stocks (
            id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, warehouse_id INT NOT NULL, stock INT NOT NULL DEFAULT 0, UNIQUE KEY unique_prod_wh (product_id, warehouse_id)
        )");
        $pdo->exec("INSERT IGNORE INTO product_warehouse_stocks (product_id, warehouse_id, stock) SELECT id, stock, 1 FROM products");
    } catch (Exception $e) {}
} catch (PDOException $e) {
    // Hentikan eksekusi dan tampilkan error jika koneksi gagal
    die("Koneksi database gagal: " . $e->getMessage());
}
?>