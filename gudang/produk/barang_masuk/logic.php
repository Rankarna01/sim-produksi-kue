<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_barang_masuk');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. INIT FORM
    if ($action === 'init_form') {
        $materials = $pdo->query("SELECT id, material_name, unit, sku_code FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'materials' => $materials, 'suppliers' => $suppliers]);
        exit;
    }

    // 2. READ DATA
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $tab = $_GET['tab'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($tab !== 'semua') {
            $whereClause .= " AND bm.source = ?";
            $params[] = $tab;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(bm.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($search)) {
            $whereClause .= " AND (bm.transaction_no LIKE ? OR ms.material_name LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countSql = "SELECT COUNT(bm.id) FROM barang_masuk bm LEFT JOIN materials_stocks ms ON bm.material_id = ms.id LEFT JOIN suppliers s ON bm.supplier_id = s.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT bm.*, ms.material_name, ms.unit, ms.stock as current_stock, s.name as supplier_name, u.name as admin_name 
            FROM barang_masuk bm
            LEFT JOIN materials_stocks ms ON bm.material_id = ms.id
            LEFT JOIN suppliers s ON bm.supplier_id = s.id
            LEFT JOIN users u ON bm.user_id = u.id
            $whereClause 
            ORDER BY bm.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }

    // 3. CREATE DATA MANUAL BATCH (MULTI-ITEM)
    if ($action === 'save') {
        $drafts = json_decode($_POST['drafts'] ?? '[]', true);
        $supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($drafts)) {
            echo json_encode(['status' => 'error', 'message' => 'Daftar barang masuk tidak boleh kosong!']); exit;
        }

        $pdo->beginTransaction();

        // Generate 1 Nomor TRX untuk semua barang di batch ini
        $trx_no = "IN-" . date('YmdHis') . "-" . rand(10,99);

        // Cek SOP Toko
        $stmtSetting = $pdo->query("SELECT req_approval_in FROM store_profile WHERE id = 1");
        $req_approval = $stmtSetting->fetchColumn() ?? 1;
        $status_awal = ($req_approval == 1) ? 'pending' : 'approved';

        $stmtIns = $pdo->prepare("INSERT INTO barang_masuk (transaction_no, material_id, supplier_id, qty, source, expiry_date, notes, user_id, status) VALUES (?, ?, ?, ?, 'Manual', ?, ?, ?, ?)");
        $stmtUpd = $pdo->prepare("UPDATE materials_stocks SET stock = stock + ?, expiry_date = ? WHERE id = ?");

        foreach ($drafts as $item) {
            $material_id = $item['material_id'];
            $qty = (float)$item['qty'];
            $expiry_date = $item['expiry_date'];

            // Insert Transaksi
            $stmtIns->execute([$trx_no, $material_id, $supplier_id, $qty, $expiry_date, $notes, $user_id, $status_awal]);

            // Jika SOP Approve DIMATIKAN, langsung tambah stok
            if ($req_approval == 0) {
                $stmtUpd->execute([$qty, $expiry_date, $material_id]);
            }
        }

        $msg = ($req_approval == 0) ? 'Barang Masuk disimpan dan stok langsung bertambah!' : 'Pengajuan Barang Masuk berhasil dibuat! Menunggu persetujuan Owner.';
        
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>