<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

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
    // ====================================================================
    // ROUTE BACA DATA: Dikelompokkan per Invoice (1 Baris = 1 Invoice)
    // ====================================================================
    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($start_date)) {
            $whereClause .= " AND DATE(p.created_at) >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $whereClause .= " AND DATE(p.created_at) <= ?";
            $params[] = $end_date;
        }
        if (!empty($status)) {
            $whereClause .= " AND p.status = ?";
            $params[] = $status;
        }

        // Hitung total INVOICE (Bukan total produk)
        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM productions p $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Gabungkan nama produk menjadi 1 string menggunakan GROUP_CONCAT
        $sql = "
            SELECT p.id as prod_id, p.invoice_no, p.created_at, p.status, 
                   GROUP_CONCAT(CONCAT(pr.name, ' (', d.quantity, ')') SEPARATOR ', ') as product_list,
                   SUM(d.quantity) as total_qty
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            $whereClause
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }

    // ====================================================================
    // FITUR BARU: AMBIL DETAIL INVOICE UNTUK MODAL EDIT MULTI-PRODUK
    // ====================================================================
    if ($action === 'get_details') {
        $prod_id = $_GET['prod_id'];
        $stmt = $pdo->prepare("
            SELECT d.id as detail_id, pr.name as product_name, d.quantity 
            FROM production_details d
            JOIN products pr ON d.product_id = pr.id
            WHERE d.production_id = ?
        ");
        $stmt->execute([$prod_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        exit;
    }

    // ====================================================================
    // LOGIKA REVISI: Sekarang memproses ARRAY detail_id dan quantity
    // ====================================================================
    if ($action === 'update_revisi') {
        $prod_id = $_POST['prod_id'];     
        $detail_ids = $_POST['detail_id']; // Array []
        $new_qtys = $_POST['quantity'];   // Array []

        $pdo->beginTransaction();

        for ($i = 0; $i < count($detail_ids); $i++) {
            $detail_id = $detail_ids[$i];
            $new_qty = (int)$new_qtys[$i];

            if ($new_qty <= 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Jumlah produk tidak boleh 0!']); exit;
            }

            // Ambil data lama
            $stmt = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE id = ?");
            $stmt->execute([$detail_id]);
            $oldData = $stmt->fetch();
            $product_id = $oldData['product_id'];
            $old_qty = $oldData['quantity'];

            // Cek BOM dan Sesuaikan Stok
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll();

            foreach ($bom_list as $bom) {
                $stok_stmt = $pdo->prepare("SELECT stock, unit, name FROM materials WHERE id = ? FOR UPDATE");
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $qty_needed_per_item = floatval($bom['quantity_needed']);
                $old_needed_in_gudang = convertUnit($qty_needed_per_item * $old_qty, $bom['unit_used'], $material['unit']);
                $new_needed_in_gudang = convertUnit($qty_needed_per_item * $new_qty, $bom['unit_used'], $material['unit']);

                $difference = $new_needed_in_gudang - $old_needed_in_gudang;

                if ($difference > 0 && floatval($material['stock']) < $difference) {
                    $pdo->rollBack();
                    echo json_encode(['status' => 'error', 'message' => "Stok {$material['name']} tidak cukup untuk merevisi jumlah produk!"]);
                    exit;
                }

                $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                $upd_stok->execute([$difference, $bom['material_id']]);
            }

            // Update baris detail ini
            $upd_det = $pdo->prepare("UPDATE production_details SET quantity = ? WHERE id = ?");
            $upd_det->execute([$new_qty, $detail_id]);
        }

        // Set status kembali ke pending
        $upd_prod = $pdo->prepare("UPDATE productions SET status = 'pending' WHERE id = ?");
        $upd_prod->execute([$prod_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Revisi berhasil disimpan! Silakan minta Admin Gudang untuk scan ulang struk lama Anda.']);
        exit;
    }

    // ====================================================================
    // FITUR BATALKAN: Cukup butuh prod_id, batalkan 1 invoice penuh
    // ====================================================================
    if ($action === 'cancel_produksi') {
        $prod_id = $_POST['prod_id'];

        $pdo->beginTransaction();

        $stmtCek = $pdo->prepare("SELECT status FROM productions WHERE id = ? FOR UPDATE");
        $stmtCek->execute([$prod_id]);
        $currentStatus = $stmtCek->fetchColumn();

        if ($currentStatus === 'dibatalkan') {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Data ini sudah dibatalkan sebelumnya!']); exit;
        }
        if ($currentStatus === 'masuk_gudang') {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Tidak bisa dibatalkan karena produk sudah masuk gudang!']); exit;
        }

        $stmtDet = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE production_id = ?");
        $stmtDet->execute([$prod_id]);
        $details = $stmtDet->fetchAll();

        foreach ($details as $detail) {
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$detail['product_id']]);
            $bom_list = $bom_stmt->fetchAll();

            foreach ($bom_list as $bom) {
                $stok_stmt = $pdo->prepare("SELECT stock, unit FROM materials WHERE id = ? FOR UPDATE");
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $qty_needed_per_item = floatval($bom['quantity_needed']);
                $refund_amount = convertUnit($qty_needed_per_item * $detail['quantity'], $bom['unit_used'], $material['unit']);

                $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
                $upd_stok->execute([$refund_amount, $bom['material_id']]);
            }
        }

        $upd_prod = $pdo->prepare("UPDATE productions SET status = 'dibatalkan' WHERE id = ?");
        $upd_prod->execute([$prod_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Produksi berhasil dibatalkan. Seluruh stok bahan baku untuk invoice ini telah dikembalikan.']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>