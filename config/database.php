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
    // Tangani error koneksi agar tidak memicu HTTP 500 Internal Server Error di server produksi
    $msg = "Koneksi database gagal: " . $e->getMessage();
    
    // Deteksi jika dipanggil oleh request API/JSON (seperti login_logic.php)
    if (
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (headers_sent() === false && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    ) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $msg]);
    } else {
        echo "<div style='padding:20px; background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; font-family:sans-serif; border-radius:8px; margin:20px;'>
                <strong style='display:block; margin-bottom:8px;'>⚠️ Gangguan Sistem</strong>
                $msg
              </div>";
    }
    exit;
}
?>