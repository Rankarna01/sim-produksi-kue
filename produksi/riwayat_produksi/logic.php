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

        $countStmt = $pdo->prepare("SELECT COUNT(d.id) FROM productions p JOIN production_details d ON p.id = d.production_id $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT p.id as prod_id, d.id as detail_id, p.invoice_no, p.created_at, p.status, 
                   pr.name as product_name, d.quantity 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            $whereClause
            ORDER BY p.created_at DESC, d.id ASC
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
    // LOGIKA REVISI (TIDAK DIUBAH)
    // ====================================================================
    if ($action === 'update_revisi') {
        $prod_id = $_POST['prod_id'];     
        $detail_id = $_POST['detail_id']; 
        $new_qty = (int)$_POST['quantity'];

        if ($new_qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Jumlah harus lebih dari 0!']); exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE id = ?");
        $stmt->execute([$detail_id]);
        $oldData = $stmt->fetch();
        $product_id = $oldData['product_id'];
        $old_qty = $oldData['quantity'];

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
                echo json_encode(['status' => 'error', 'message' => "Stok {$material['name']} tidak cukup untuk merevisi jumlah ini!"]);
                exit;
            }

            $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
            $upd_stok->execute([$difference, $bom['material_id']]);
        }

        $upd_det = $pdo->prepare("UPDATE production_details SET quantity = ? WHERE id = ?");
        $upd_det->execute([$new_qty, $detail_id]);

        $upd_prod = $pdo->prepare("UPDATE productions SET status = 'pending' WHERE id = ?");
        $upd_prod->execute([$prod_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Revisi berhasil disimpan! Silakan minta Admin Gudang untuk scan ulang struk lama Anda.']);
        exit;
    }

    // ====================================================================
    // FITUR BARU: BATALKAN PRODUKSI (HAPUS & KEMBALIKAN STOK 100%)
    // ====================================================================
    if ($action === 'cancel_produksi') {
        $detail_id = $_POST['detail_id'];
        $prod_id = $_POST['prod_id'];

        $pdo->beginTransaction();

        // 1. Ambil Data Item yang mau dibatalkan
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE id = ?");
        $stmt->execute([$detail_id]);
        $detail = $stmt->fetch();

        if (!$detail) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan!']); exit;
        }

        // 2. Kembalikan semua stok bahan baku (REFUND)
        $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
        $bom_stmt->execute([$detail['product_id']]);
        $bom_list = $bom_stmt->fetchAll();

        foreach ($bom_list as $bom) {
            $stok_stmt = $pdo->prepare("SELECT stock, unit FROM materials WHERE id = ? FOR UPDATE");
            $stok_stmt->execute([$bom['material_id']]);
            $material = $stok_stmt->fetch();

            $qty_needed_per_item = floatval($bom['quantity_needed']);
            // Total Refund 100%
            $refund_amount = convertUnit($qty_needed_per_item * $detail['quantity'], $bom['unit_used'], $material['unit']);

            // Refund (+) Kembalikan ke Stok
            $upd_stok = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
            $upd_stok->execute([$refund_amount, $bom['material_id']]);
        }

        // 3. Hapus baris produk tersebut dari database
        $del_det = $pdo->prepare("DELETE FROM production_details WHERE id = ?");
        $del_det->execute([$detail_id]);

        // 4. Jika di Invoice tsb sudah tidak ada produk lain, hapus sekalian payung Invoice-nya
        $cek_sisa = $pdo->prepare("SELECT COUNT(id) FROM production_details WHERE production_id = ?");
        $cek_sisa->execute([$prod_id]);
        if ($cek_sisa->fetchColumn() == 0) {
            $del_prod = $pdo->prepare("DELETE FROM productions WHERE id = ?");
            $del_prod->execute([$prod_id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Produksi dibatalkan. Seluruh stok bahan baku telah dikembalikan secara otomatis.']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>