<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// Fungsi Konversi Satuan BOM (TIDAK ADA YANG DIUBAH - AMAN)
function convertUnit($amount, $from_unit, $to_unit) {
    $from = strtolower(trim($from_unit));
    $to = strtolower(trim($to_unit));
    if ($from === $to) return $amount;

    if ($from === 'gram' && $to === 'kg') return $amount / 1000;
    if ($from === 'kg' && $to === 'gram') return $amount * 1000;
    if ($from === 'ons' && $to === 'kg') return $amount / 10;
    if ($from === 'ons' && $to === 'gram') return $amount * 100;
    if ($from === 'gram' && $to === 'ons') return $amount / 100;
    if ($from === 'kg' && $to === 'ons') return $amount * 10;
    
    if ($from === 'ml' && $to === 'liter') return $amount / 1000;
    if ($from === 'liter' && $to === 'ml') return $amount * 1000;

    return $amount;
}

try {
    // PERBAIKAN: Tambah data Kitchens untuk filter UI
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success', 
            'warehouses' => $warehouses,
            'kitchens' => $kitchens
        ]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $status = $_GET['status'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? ''; 
        $kitchen_id = $_GET['kitchen_id'] ?? ''; 
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE p.user_id = ?";
        $params = [$_SESSION['user_id']];

        if (!empty($start_date)) { $whereClause .= " AND DATE(p.created_at) >= ?"; $params[] = $start_date; }
        if (!empty($end_date)) { $whereClause .= " AND DATE(p.created_at) <= ?"; $params[] = $end_date; }
        if (!empty($status)) { $whereClause .= " AND p.status = ?"; $params[] = $status; }
        if (!empty($warehouse_id)) { $whereClause .= " AND p.warehouse_id = ?"; $params[] = $warehouse_id; }
        
        // PERBAIKAN: Filter berdasarkan Dapur
        if (!empty($kitchen_id)) { 
            $whereClause .= " AND e.kitchen_id = ?"; 
            $params[] = $kitchen_id; 
        }

        // PERBAIKAN: Join tabel Employees dan Kitchens agar filter jalan
        $countSql = "
            SELECT COUNT(p.id) 
            FROM productions p
            LEFT JOIN employees e ON p.employee_id = e.id
            $whereClause
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // PERBAIKAN: Menarik nama karyawan dan nama dapurnya
        $sql = "
            SELECT p.id as prod_id, p.invoice_no, p.created_at, p.status, w.name as gudang,
                   k.name as asal_dapur, e.name as pembuat,
                   GROUP_CONCAT(CONCAT(pr.name, ' (', d.quantity, ')') SEPARATOR ', ') as product_list,
                   SUM(d.quantity) as total_qty
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            LEFT JOIN warehouses w ON p.warehouse_id = w.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            $whereClause
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }

    if ($action === 'get_details') {
        $prod_id = $_GET['prod_id'];
        $stmt = $pdo->prepare("
            SELECT d.id as detail_id, pr.name as product_name, d.quantity 
            FROM production_details d
            JOIN products pr ON d.product_id = pr.id
            WHERE d.production_id = ?
        ");
        $stmt->execute([$prod_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // ====================================================================
    // REVISI: SESUAIKAN DENGAN LOGIKA MULTI-DAPUR
    // ====================================================================
    if ($action === 'update_revisi') {
        $prod_id = $_POST['prod_id'];     
        $detail_ids = $_POST['detail_id']; 
        $new_qtys = $_POST['quantity'];   

        $pdo->beginTransaction();

        // AMBIL INFO KITCHEN_ID DARI INVOICE INI
        $prodInfo = $pdo->prepare("SELECT employee_id FROM productions WHERE id = ?");
        $prodInfo->execute([$prod_id]);
        $empId = $prodInfo->fetchColumn();

        $empInfo = $pdo->prepare("SELECT kitchen_id FROM employees WHERE id = ?");
        $empInfo->execute([$empId]);
        $kitchen_id = $empInfo->fetchColumn();

        for ($i = 0; $i < count($detail_ids); $i++) {
            $detail_id = $detail_ids[$i];
            $new_qty = (int)$new_qtys[$i];
            if ($new_qty <= 0) { $pdo->rollBack(); echo json_encode(['status' => 'error', 'message' => 'Jumlah produk tidak boleh 0!']); exit; }

            $stmt = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE id = ?");
            $stmt->execute([$detail_id]);
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
            $product_id = $oldData['product_id'];
            $old_qty = $oldData['quantity'];

            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bom_list as $bom) {
                // 1. Ambil Data Master Pilar
                $master_stmt = $pdo->prepare("SELECT sku_code FROM materials_stocks WHERE id = ?");
                $master_stmt->execute([$bom['material_id']]);
                $sku_code = $master_stmt->fetchColumn();

                // 2. Cari di Dapur Asal
                $stokDapur_stmt = $pdo->prepare("SELECT id, stock, unit, name FROM materials WHERE code = ? AND warehouse_id = ? FOR UPDATE");
                $stokDapur_stmt->execute([$sku_code, $kitchen_id]);
                $material = $stokDapur_stmt->fetch(PDO::FETCH_ASSOC);

                if($material) {
                    $qty_needed_per_item = floatval($bom['quantity_needed']);
                    $old_needed_in_gudang = convertUnit($qty_needed_per_item * $old_qty, $bom['unit_used'], $material['unit']);
                    $new_needed_in_gudang = convertUnit($qty_needed_per_item * $new_qty, $bom['unit_used'], $material['unit']);

                    // Jika revisinya nambah (New > Old), berarti harus motong stok lagi. Kalau stok gak cukup, biarkan minus.
                    $difference = $new_needed_in_gudang - $old_needed_in_gudang;
                    
                    $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                    $upd_stok->execute([$difference, $material['id']]);
                }
            }
            $upd_det = $pdo->prepare("UPDATE production_details SET quantity = ? WHERE id = ?");
            $upd_det->execute([$new_qty, $detail_id]);
        }

        $upd_prod = $pdo->prepare("UPDATE productions SET status = 'pending' WHERE id = ?");
        $upd_prod->execute([$prod_id]);
        
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Revisi berhasil disimpan, stok dapur telah disesuaikan!']);
        exit;
    }

    // ====================================================================
    // BATALKAN: KEMBALIKAN STOK KE DAPUR ASAL
    // ====================================================================
    if ($action === 'cancel_produksi') {
        $prod_id = $_POST['prod_id'];
        $pin = $_POST['pin'] ?? '';

        // 1. Verifikasi PIN Supervisor
        $stmtPin = $pdo->prepare("SELECT pin_code FROM supervisor_pins WHERE pin_type = 'delete_production' LIMIT 1");
        $stmtPin->execute();
        $valid_pin = $stmtPin->fetchColumn();

        if ($pin !== $valid_pin) {
            echo json_encode(['status' => 'error', 'message' => 'PIN Supervisor Salah! Akses Ditolak.']); exit;
        }

        $pdo->beginTransaction();

        $stmtCek = $pdo->prepare("SELECT status, employee_id FROM productions WHERE id = ? FOR UPDATE");
        $stmtCek->execute([$prod_id]);
        $prodInfo = $stmtCek->fetch(PDO::FETCH_ASSOC);

        if ($prodInfo['status'] === 'dibatalkan') { $pdo->rollBack(); echo json_encode(['status' => 'error', 'message' => 'Sudah dibatalkan!']); exit; }
        if ($prodInfo['status'] === 'masuk_gudang') { $pdo->rollBack(); echo json_encode(['status' => 'error', 'message' => 'Tidak bisa dibatalkan karena sudah masuk gudang!']); exit; }

        // Cari Kitchen ID
        $empInfo = $pdo->prepare("SELECT kitchen_id FROM employees WHERE id = ?");
        $empInfo->execute([$prodInfo['employee_id']]);
        $kitchen_id = $empInfo->fetchColumn();

        $stmtDet = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE production_id = ?");
        $stmtDet->execute([$prod_id]);
        $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$detail['product_id']]);
            $bom_list = $bom_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bom_list as $bom) {
                // Ambil kode dari Master
                $master_stmt = $pdo->prepare("SELECT sku_code FROM materials_stocks WHERE id = ?");
                $master_stmt->execute([$bom['material_id']]);
                $sku_code = $master_stmt->fetchColumn();

                // Kembalikan stok HANYA ke Dapur pembuatnya
                $stok_stmt = $pdo->prepare("SELECT id, unit FROM materials WHERE code = ? AND warehouse_id = ? FOR UPDATE");
                $stok_stmt->execute([$sku_code, $kitchen_id]);
                $material = $stok_stmt->fetch(PDO::FETCH_ASSOC);
                
                if($material) {
                    $qty_needed_per_item = floatval($bom['quantity_needed']);
                    $refund_amount = convertUnit($qty_needed_per_item * $detail['quantity'], $bom['unit_used'], $material['unit']);
                    
                    $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
                    $upd_stok->execute([$refund_amount, $material['id']]);
                }
            }
        }

        $upd_prod = $pdo->prepare("UPDATE productions SET status = 'dibatalkan' WHERE id = ?");
        $upd_prod->execute([$prod_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Produksi dibatalkan. Stok bahan telah dikembalikan ke Dapur asal!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>