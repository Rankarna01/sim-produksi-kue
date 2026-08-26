<?php
// config/database.php

if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

// Deteksi otomatis apakah sedang di Localhost XAMPP atau Server Hosting Produksi
$is_local_host = (
    isset($_SERVER['HTTP_HOST']) && 
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)
);

if ($is_local_host) {
    // 💻 Konfigurasi Localhost (XAMPP Komputer)
    $host   = env('DB_HOST', 'localhost');
    $user   = env('DB_USER', 'root');
    $pass   = env('DB_PASS', '');
    $dbname = env('DB_NAME', 'sim-kue');
} else {
    // 🌐 Konfigurasi Server Produksi (Hostinger / cPanel)
    $host   = env('DB_HOST', 'localhost');
    $env_u  = env('DB_USER');
    $env_p  = env('DB_PASS');
    $env_d  = env('DB_NAME');

    // Jika .env di server masih berisi user lokal 'root' atau kosong, paksa gunakan user database hosting
    if (empty($env_u) || $env_u === 'root') {
        $user   = 'u672726995_lovecakes22';
        $pass   = 'Randy2005_';
        $dbname = 'u672726995_lovecakes22';
    } else {
        $user   = $env_u;
        $pass   = $env_p;
        $dbname = $env_d;
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    // Tangani error format JSON jika dipanggil oleh endpoint AJAX
    if (
        (isset($_POST['action']) || isset($_GET['action'])) || 
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    ) {
        header('Content-Type: application/json');
        die(json_encode([
            'status'  => 'error', 
            'message' => 'Koneksi database gagal di server: ' . $e->getMessage()
        ]));
    } else {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
?>