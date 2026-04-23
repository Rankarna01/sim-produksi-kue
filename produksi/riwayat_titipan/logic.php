<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        // Logika Admin Produksi yang terikat Dapur tertentu
        $stmtUser = $pdo->prepare("SELECT kitchen_id FROM users WHERE id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $userKitchenId = $stmtUser->fetchColumn();

        if ($userKitchenId) {
            $where .= " AND e.kitchen_id = ?";
            $params[] = $userKitchenId;
        }

        if (!empty($start_date)) { $where .= " AND DATE(p.created_at) >= ?"; $params[] = $start_date; }
        if (!empty($end_date)) { $where .= " AND DATE(p.created_at) <= ?"; $params[] = $end_date; }
        if (!empty($warehouse_id)) { $where .= " AND p.warehouse_id = ?"; $params[] = $warehouse_id; }
        if (!empty($status)) { $where .= " AND p.status = ?"; $params[] = $status; }

        $sql = "
            SELECT p.id, p.invoice_no, p.created_at, p.status, 
                   COALESCE(e.name, u.name) as karyawan,
                   k.name as dapur, w.name as store_tujuan,
                   (SELECT SUM(quantity) FROM titipan_production_details WHERE titipan_production_id = p.id) as total_pcs,
                   (
                       SELECT GROUP_CONCAT(CONCAT(b.nama_barang, ' (', d.quantity, ')') SEPARATOR ', ')
                       FROM titipan_production_details d
                       JOIN barang_titipan b ON d.titipan_id = b.id
                       WHERE d.titipan_production_id = p.id
                   ) as product_list
            FROM titipan_productions p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            LEFT JOIN warehouses w ON p.warehouse_id = w.id
            $where
            ORDER BY p.created_at DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($action === 'cancel') {
        $id = $_POST['id'] ?? '';
        
        $pdo->beginTransaction();

        $cekStatus = $pdo->prepare("SELECT status FROM titipan_productions WHERE id = ? FOR UPDATE");
        $cekStatus->execute([$id]);
        $currentStatus = $cekStatus->fetchColumn();

        if ($currentStatus === 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => 'Data sudah dibatalkan sebelumnya.']); 
            exit;
        }
        if ($currentStatus === 'received') {
            echo json_encode(['status' => 'error', 'message' => 'Barang sudah diterima oleh Store, tidak dapat dibatalkan.']); 
            exit;
        }

        // KEMBALIKAN STOK TITIPAN
        $stmtDetail = $pdo->prepare("SELECT titipan_id, quantity FROM titipan_production_details WHERE titipan_production_id = ?");
        $stmtDetail->execute([$id]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $d) {
            $updStok = $pdo->prepare("UPDATE barang_titipan SET stok = stok + ? WHERE id = ?");
            $updStok->execute([$d['quantity'], $d['titipan_id']]);
        }

        // UPDATE STATUS
        $updStatus = $pdo->prepare("UPDATE titipan_productions SET status = 'cancelled' WHERE id = ?");
        $updStatus->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil dibatalkan dan stok dikembalikan!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>