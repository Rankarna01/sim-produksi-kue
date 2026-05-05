<?php
// Izinkan akses dari domain POS (CORS Policy)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Sesuaikan koneksi database ini dengan sistem produksi kamu
$host = 'localhost';
$db   = 'sim-kue';
$user = 'root'; // Ganti dengan user database hosting kokowms nanti
$pass = '';     // Ganti dengan password database hosting kokowms nanti

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cek apakah ada request untuk get_products
    $action = $_GET['action'] ?? '';

    if ($action === 'get_products') {
        // Ambil data produk (Pastikan kolom online_price sudah kamu tambahkan di database)
        // Jika belum ditambahkan, hapus kata online_price dari query di bawah
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

        // Kirim response JSON
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