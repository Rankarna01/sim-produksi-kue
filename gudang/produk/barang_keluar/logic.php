<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_barang_keluar');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. INIT FORM (Tarik Dropdown Barang Aktif + Info Stok)
    if ($action === 'init_form') {
        $materials = $pdo->query("SELECT id, material_name, unit, sku_code, stock FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'materials' => $materials]);
        exit;
    }

    // 2. READ DATA (Menampilkan Tabel History)
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
            $whereClause .= " AND bk.status = ?";
            $params[] = $tab;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(bk.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($search)) {
            $whereClause .= " AND (bk.transaction_no LIKE ? OR ms.material_name LIKE ? OR ms.sku_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countSql = "SELECT COUNT(bk.id) FROM barang_keluar bk LEFT JOIN materials_stocks ms ON bk.material_id = ms.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT bk.*, ms.material_name, ms.unit, ms.sku_code, u.name as admin_name 
            FROM barang_keluar bk
            LEFT JOIN materials_stocks ms ON bk.material_id = ms.id
            LEFT JOIN users u ON bk.user_id = u.id
            $whereClause 
            ORDER BY bk.created_at DESC 
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

    // 3. CREATE DATA MANUAL BATCH (DENGAN STATUS PENDING)
    if ($action === 'save') {
        $drafts = json_decode($_POST['drafts'] ?? '[]', true);
        $status_keluar = $_POST['status'] ?? 'Rusak';
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($drafts)) {
            echo json_encode(['status' => 'error', 'message' => 'Daftar barang keluar tidak boleh kosong!']); exit;
        }

        $pdo->beginTransaction();

        $stmtSetting = $pdo->query("SELECT req_approval_out FROM store_profile WHERE id = 1");
        $req_approval = $stmtSetting->fetchColumn() ?? 1;
        $status_app = ($req_approval == 1) ? 'pending' : 'approved';

        $trx_no_base = "OUT-" . date('YmdHis') . "-";

        $stmtCek = $pdo->prepare("SELECT stock, material_name FROM materials_stocks WHERE id = ? FOR UPDATE");
        $stmtIns = $pdo->prepare("INSERT INTO barang_keluar (transaction_no, material_id, qty, status, notes, user_id, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUpd = $pdo->prepare("UPDATE materials_stocks SET stock = stock - ? WHERE id = ?");

        foreach ($drafts as $item) {
            $material_id = $item['material_id'];
            $qty = (float)$item['qty'];

            // A. Cek Stok Saat Ini 
            $stmtCek->execute([$material_id]);
            $curr = $stmtCek->fetch(PDO::FETCH_ASSOC);

            if (!$curr) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => "Barang dengan ID {$material_id} tidak ditemukan!"]); exit;
            }

            if ($qty > (float)$curr['stock']) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => "Stok tidak cukup! Sisa stok {$curr['material_name']} saat ini hanya ". (float)$curr['stock']]); exit;
            }

            // B. Generate unique TRX No per item 
            $trx_no = $trx_no_base . rand(10,99);

            // C. Insert Riwayat Keluar
            $stmtIns->execute([$trx_no, $material_id, $qty, $status_keluar, $notes, $user_id, $status_app]);

            // Jika SOP Approve DIMATIKAN, langsung potong stok
            if ($req_approval == 0) {
                $stmtUpd->execute([$qty, $material_id]);
            }
        }

        $msg = ($req_approval == 0) ? 'Barang Keluar disimpan dan stok langsung dikurangi (Auto-Approve)!' : 'Pengajuan Keluar berhasil dicatat! Menunggu persetujuan Owner.';

        $pdo->commit(); 
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack(); 
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>