<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_permintaan_barang');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. INIT FORM (Tarik Barang)
    if ($action === 'init_form') {
        $materials = $pdo->query("SELECT id, material_name, unit, stock, sku_code FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'materials' => $materials]);
        exit;
    }

    // 2. READ DATA LIST
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $tab = $_GET['tab'] ?? 'semua'; // semua, pending, processing, rejected
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($tab !== 'semua') {
            $whereClause .= " AND pr.status = ?";
            $params[] = $tab;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(pr.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR u.name LIKE ? OR pr.request_no LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countSql = "SELECT COUNT(pr.id) FROM purchase_requests pr LEFT JOIN materials_stocks ms ON pr.material_id = ms.id LEFT JOIN users u ON pr.user_id = u.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT pr.*, ms.material_name, ms.unit, u.name as requester_name 
            FROM purchase_requests pr
            LEFT JOIN materials_stocks ms ON pr.material_id = ms.id
            LEFT JOIN users u ON pr.user_id = u.id
            $whereClause 
            ORDER BY pr.created_at DESC 
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

    // 3. SAVE CART DATA
    if ($action === 'save') {
        $cart = json_decode($_POST['cart'], true);
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong!']); exit;
        }

        $pdo->beginTransaction();
        
        // Generate nomor batch request untuk kemudahan tracking
        $batch_no = "PR-" . date('YmdHis') . rand(10,99);

        $stmt = $pdo->prepare("INSERT INTO purchase_requests (request_no, material_id, qty, notes, status, user_id) VALUES (?, ?, ?, ?, 'pending', ?)");

        foreach ($cart as $item) {
            $stmt->execute([
                $batch_no, 
                $item['material_id'], 
                $item['qty'], 
                $item['notes'], 
                $user_id
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Semua permintaan berhasil dikirim ke Owner/Purchasing!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>