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
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'cancel') {
        $id = $_POST['id'] ?? '';
        $pdo->beginTransaction();

        $cekStatus = $pdo->prepare("SELECT status FROM titipan_productions WHERE id = ? FOR UPDATE");
        $cekStatus->execute([$id]);
        $currentStatus = $cekStatus->fetchColumn();

        if ($currentStatus === 'cancelled' || $currentStatus === 'received') {
            echo json_encode(['status' => 'error', 'message' => 'Status barang tidak dapat dibatalkan.']); exit;
        }

        $stmtDetail = $pdo->prepare("SELECT titipan_id, quantity FROM titipan_production_details WHERE titipan_production_id = ?");
        $stmtDetail->execute([$id]);
        foreach ($stmtDetail->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $pdo->prepare("UPDATE barang_titipan SET stok = stok + ? WHERE id = ?")->execute([$d['quantity'], $d['titipan_id']]);
        }

        $pdo->prepare("UPDATE titipan_productions SET status = 'cancelled' WHERE id = ?")->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil dibatalkan dan stok dikembalikan!']);
        exit;
    }

    // ==========================================
    // BAGIAN BARU: AMBIL DATA UNTUK MODAL REVISI
    // ==========================================
    if ($action === 'get_revisi_data') {
        $id = $_GET['id'];
        
        $stmtHead = $pdo->prepare("SELECT p.id, p.invoice_no, p.employee_id, e.name as emp_name FROM titipan_productions p LEFT JOIN employees e ON p.employee_id = e.id WHERE p.id = ?");
        $stmtHead->execute([$id]);
        $header = $stmtHead->fetch(PDO::FETCH_ASSOC);

        $stmtDetail = $pdo->prepare("
            SELECT d.titipan_id as id, b.nama_barang as name, b.nama_umkm as code, d.quantity 
            FROM titipan_production_details d
            JOIN barang_titipan b ON d.titipan_id = b.id
            WHERE d.titipan_production_id = ?
        ");
        $stmtDetail->execute([$id]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        $master = $pdo->query("SELECT id, nama_barang as name, nama_umkm as code, stok FROM barang_titipan ORDER BY nama_barang ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'header' => $header, 'details' => $details, 'master' => $master]);
        exit;
    }

    // ==========================================
    // BAGIAN BARU: PROSES SIMPAN REVISI
    // ==========================================
    if ($action === 'revisi') {
        $production_id = $_POST['production_id'];
        $product_ids = $_POST['product_id'] ?? []; 
        $quantities = $_POST['quantity'] ?? [];
        $pin_input = trim($_POST['pin'] ?? '');

        if (empty($pin_input) || count($product_ids) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lengkapi data dan PIN!']); exit;
        }

        $pdo->beginTransaction();

        $stmtEmp = $pdo->prepare("SELECT e.id, e.pin FROM titipan_productions p JOIN employees e ON p.employee_id = e.id WHERE p.id = ?");
        $stmtEmp->execute([$production_id]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if ((string)$emp['pin'] !== (string)$pin_input) {
            echo json_encode(['status' => 'error', 'message' => 'PIN Salah! Otorisasi ditolak.']); exit;
        }

        // 1. REFUND LAMA
        $old_details = $pdo->prepare("SELECT titipan_id, quantity FROM titipan_production_details WHERE titipan_production_id = ?");
        $old_details->execute([$production_id]);
        foreach ($old_details->fetchAll() as $old) {
            $pdo->prepare("UPDATE barang_titipan SET stok = stok + ? WHERE id = ?")->execute([$old['quantity'], $old['titipan_id']]);
        }
        $pdo->prepare("DELETE FROM titipan_production_details WHERE titipan_production_id = ?")->execute([$production_id]);

        // 2. CEK STOK BARU & POTONG
        $invoice_no = $pdo->prepare("SELECT invoice_no FROM titipan_productions WHERE id = ?")->execute([$production_id]) ? $pdo->query("SELECT invoice_no FROM titipan_productions WHERE id = $production_id")->fetchColumn() : 'TTP-REV';

        for ($i = 0; $i < count($product_ids); $i++) {
            $titipan_id = $product_ids[$i];
            $qty = (int)$quantities[$i];

            if (empty($titipan_id) || $qty <= 0) continue; 

            $cekStok = $pdo->prepare("SELECT nama_barang, stok FROM barang_titipan WHERE id = ? FOR UPDATE");
            $cekStok->execute([$titipan_id]);
            $brg = $cekStok->fetch(PDO::FETCH_ASSOC);

            if (!$brg || $brg['stok'] < $qty) {
                $pdo->rollBack();
                $sisa = $brg ? $brg['stok'] : 0;
                echo json_encode(['status' => 'error', 'message' => "Stok {$brg['nama_barang']} tidak cukup! (Tersedia: $sisa)"]); exit;
            }

            $pdo->prepare("UPDATE barang_titipan SET stok = stok - ? WHERE id = ?")->execute([$qty, $titipan_id]);
            
            $barcode = $invoice_no . "-" . ($i + 1);
            $pdo->prepare("INSERT INTO titipan_production_details (titipan_production_id, titipan_id, quantity, barcode) VALUES (?, ?, ?, ?)")->execute([$production_id, $titipan_id, $qty, $barcode]);
        }

        $pdo->prepare("UPDATE titipan_productions SET status = 'pending' WHERE id = ?")->execute([$production_id]);
        
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Revisi berhasil disimpan, status kembali Pending.']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>