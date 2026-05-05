<?php
// Izinkan akses dari domain POS (CORS Policy)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// KONEKSI DATABASE HOSTINGER
$host = 'localhost';
$db   = 'u672726995_sim_kue'; // Sesuai nama database di phpMyAdmin Hostinger kamu
$user = 'u672726995_sim_kue'; // Biasanya username DB di Hostinger diawali kode yang sama
$pass = 'Randy2005_';         // Password database kamu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';

    if ($action === 'get_products') {
        $stmt = $pdo->prepare("
            SELECT 
                id, 
                code, 
                name, 
                category, 
                image, 
                modal_price, 
                price AS offline_price, 
                online_price, 
                stock 
            FROM products 
            ORDER BY name ASC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data produk berhasil diambil',
            'total_data' => count($products),
            'data' => $products
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action tidak valid']);
    }

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi Gagal: ' . $e->getMessage()]);
}
?>