<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('dashboard');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'get_dashboard_data') {
        // 1. PO Berjalan
        $po = $pdo->query("SELECT COUNT(*) FROM purchase_orders WHERE status IN ('waiting_approval', 'approved', 'processing')")->fetchColumn();
        
        // 2. Pending Approval (Semua jenis)
        $approval = $pdo->query("
            SELECT (
                (SELECT COUNT(*) FROM purchase_orders WHERE status = 'waiting_approval') +
                (SELECT COUNT(*) FROM purchase_requests WHERE status = 'pending') +
                (SELECT COUNT(*) FROM barang_masuk WHERE status = 'pending' AND source = 'Manual') +
                (SELECT COUNT(*) FROM barang_keluar WHERE approval_status = 'pending')
            )
        ")->fetchColumn();

        // 3. Stok Kritis
        $kritis = $pdo->query("SELECT COUNT(*) FROM materials_stocks WHERE stock <= min_stock AND status = 'active'")->fetchColumn();
        
        // 4. Hutang
        $hutang = $pdo->query("SELECT SUM(total_amount - paid_amount) FROM purchase_orders WHERE payment_status != 'paid' AND status != 'cancelled'")->fetchColumn();

        // 5. Pengumuman
        $msg = $pdo->query("SELECT pesan FROM pengumuman WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetchColumn();

        // 6. Permintaan Dapur
        $reqs = $pdo->query("SELECT pr.*, ms.material_name, ms.unit FROM purchase_requests pr JOIN materials_stocks ms ON pr.material_id = ms.id ORDER BY pr.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // 7. Supplier
        $supps = $pdo->query("SELECT name, phone FROM suppliers ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // 8. Grafik Tren (7 Hari)
        $labels = []; $masuk = []; $keluar = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date));
            
            $qIn = $pdo->prepare("SELECT COALESCE(SUM(qty), 0) FROM barang_masuk WHERE DATE(created_at) = ? AND status = 'approved'");
            $qIn->execute([$date]); $masuk[] = (float)$qIn->fetchColumn();

            $qOut = $pdo->prepare("SELECT COALESCE(SUM(qty), 0) FROM barang_keluar WHERE DATE(created_at) = ? AND approval_status = 'approved'");
            $qOut->execute([$date]); $keluar[] = (float)$qOut->fetchColumn();
        }

        // 9. Grafik Donat
        $stok_aman = $pdo->query("SELECT COUNT(*) FROM materials_stocks WHERE stock > min_stock AND status = 'active'")->fetchColumn();
        $stok_habis = $pdo->query("SELECT COUNT(*) FROM materials_stocks WHERE stock = 0 AND status = 'active'")->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'stats' => ['po' => $po, 'req' => $approval, 'kritis' => $kritis, 'hutang' => $hutang ?: 0],
            'pengumuman' => $msg,
            'tables' => ['reqs' => $reqs, 'supps' => $supps],
            'charts' => [
                'trend' => ['labels' => $labels, 'in' => $masuk, 'out' => $keluar],
                'stock' => ['aman' => $stok_aman, 'kritis' => $kritis, 'habis' => $stok_habis]
            ]
        ]);
        exit;
    }

    if ($action === 'update_pengumuman') {
        if (($_SESSION['role'] ?? $_SESSION['role_slug'] ?? '') !== 'owner_gudang') {
            echo json_encode(['status' => 'error', 'message' => 'Hanya Owner yang berhak!']); exit;
        }
        $pesan = trim($_POST['pesan'] ?? '');
        $stmt = $pdo->prepare("UPDATE pengumuman SET pesan = ? WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $stmt->execute([$pesan]);
        echo json_encode(['status' => 'success', 'message' => 'Diperbarui!']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}