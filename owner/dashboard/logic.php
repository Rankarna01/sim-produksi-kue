<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('view_dashboard');

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');

    // 1. STATISTIK ANGKA (KPI)
    // Produksi Hari Ini
    $stmt_prod = $pdo->prepare("SELECT IFNULL(SUM(d.quantity), 0) as total FROM productions p JOIN production_details d ON p.id = d.production_id WHERE DATE(p.created_at) = ?");
    $stmt_prod->execute([$today]);
    $produksi_hari_ini = $stmt_prod->fetch()['total'] ?? 0;

    // Bahan Baku Kritis (Stok <= 10)
    $stmt_bahan = $pdo->query("SELECT COUNT(id) as total FROM materials WHERE stock <= 10");
    $bahan_kritis = $stmt_bahan->fetch()['total'] ?? 0;

    // Total Produk Master
    $stmt_produk = $pdo->query("SELECT COUNT(id) as total FROM products");
    $total_produk = $stmt_produk->fetch()['total'] ?? 0;

    // Total User Terdaftar
    $stmt_user = $pdo->query("SELECT COUNT(id) as total FROM users");
    $total_user = $stmt_user->fetch()['total'] ?? 0;

    // 2. DATA GRAFIK (7 Hari Terakhir)
    $stmt_chart = $pdo->query("
        SELECT DATE(p.created_at) as tgl, SUM(d.quantity) as total 
        FROM productions p 
        JOIN production_details d ON p.id = d.production_id 
        WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
        GROUP BY DATE(p.created_at) 
        ORDER BY DATE(p.created_at) ASC
    ");
    $chart_data = $stmt_chart->fetchAll();

    // 3. AKTIVITAS TERBARU (5 Baris Terakhir)
    $stmt_recent = $pdo->query("
        SELECT p.created_at, pr.name, d.quantity, u.name as karyawan 
        FROM productions p 
        JOIN production_details d ON p.id = d.production_id 
        JOIN products pr ON d.product_id = pr.id 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC LIMIT 5
    ");
    $recent_activities = $stmt_recent->fetchAll();

    // Kirim Balasan JSON
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'produksi' => $produksi_hari_ini,
            'bahan_kritis' => $bahan_kritis,
            'produk' => $total_produk,
            'user' => $total_user
        ],
        'chart' => $chart_data,
        'recent' => $recent_activities
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>