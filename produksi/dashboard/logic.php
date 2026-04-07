<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    if ($action === 'dashboard_data') {
        // 1. STATISTIK ANGKA 
        $stmt_total = $pdo->prepare("SELECT IFNULL(SUM(d.quantity), 0) as total FROM productions p JOIN production_details d ON p.id = d.production_id WHERE p.user_id = ? AND DATE(p.created_at) = ?");
        $stmt_total->execute([$user_id, $today]);
        $res_total = $stmt_total->fetch();
        $total = $res_total ? $res_total['total'] : 0;

        $stmt_pending = $pdo->prepare("SELECT IFNULL(SUM(d.quantity), 0) as pending FROM productions p JOIN production_details d ON p.id = d.production_id WHERE p.user_id = ? AND p.status = 'pending' AND DATE(p.created_at) = ?");
        $stmt_pending->execute([$user_id, $today]);
        $res_pending = $stmt_pending->fetch();
        $pending = $res_pending ? $res_pending['pending'] : 0;

        // TAMBAHAN: Tarik Data Ditolak / Revisi
        $stmt_ditolak = $pdo->prepare("SELECT IFNULL(SUM(d.quantity), 0) as ditolak FROM productions p JOIN production_details d ON p.id = d.production_id WHERE p.user_id = ? AND p.status = 'ditolak' AND DATE(p.created_at) = ?");
        $stmt_ditolak->execute([$user_id, $today]);
        $res_ditolak = $stmt_ditolak->fetch();
        $ditolak = $res_ditolak ? $res_ditolak['ditolak'] : 0;

        $stmt_valid = $pdo->prepare("SELECT IFNULL(SUM(d.quantity), 0) as valid FROM productions p JOIN production_details d ON p.id = d.production_id WHERE p.user_id = ? AND p.status = 'masuk_gudang' AND DATE(p.created_at) = ?");
        $stmt_valid->execute([$user_id, $today]);
        $res_valid = $stmt_valid->fetch();
        $valid = $res_valid ? $res_valid['valid'] : 0;

        // 2. LOG TERBARU (5 Baris)
        $stmt_recent = $pdo->prepare("SELECT p.created_at, pr.name, d.quantity, p.status FROM productions p JOIN production_details d ON p.id = d.production_id JOIN products pr ON d.product_id = pr.id WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT 5");
        $stmt_recent->execute([$user_id]);
        $recent = $stmt_recent->fetchAll();

        echo json_encode([
            'status' => 'success',
            'stats' => [
                'total' => $total, 
                'pending' => $pending, 
                'ditolak' => $ditolak, // Masukkan ke response
                'valid' => $valid
            ],
            'recent' => $recent
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>