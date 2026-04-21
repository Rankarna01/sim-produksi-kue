<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_pembayaran');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN DAFTAR TAGIHAN PO (HANYA YANG SUDAH DITERIMA)
    if ($action === 'read_bills') {
        $status_filter = $_GET['status'] ?? 'belum_lunas';
        $search = $_GET['search'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        // Wajib status received karena barang belum diterima = belum bisa ditagih
        $where = "WHERE p.status = 'received'"; 
        $params = [];

        // Filter Pembayaran
        if ($status_filter === 'belum_lunas') {
            $where .= " AND p.payment_status IN ('unpaid', 'partial')";
        } elseif (in_array($status_filter, ['unpaid', 'partial', 'paid'])) {
            $where .= " AND p.payment_status = ?";
            $params[] = $status_filter;
        } // Jika 'semua', abaikan filter payment_status

        // Filter Tanggal Dibuat PO
        if (!empty($start_date) && !empty($end_date)) {
            $where .= " AND DATE(p.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $where .= " AND (p.po_no LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = "
            SELECT p.*, s.name as supplier_name, u.name as admin_name 
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.id
            JOIN users u ON p.created_by = u.id
            $where
            ORDER BY p.updated_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch detail item untuk masing-masing PO agar bisa tampil di card
        foreach ($pos as &$po) {
            $stmtItem = $pdo->prepare("SELECT ms.material_name, pod.qty, pod.price, ms.unit FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?");
            $stmtItem->execute([$po['id']]);
            $po['items'] = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode(['status' => 'success', 'data' => $pos]);
        exit;
    }

    // 2. GET DATA PEMBAYARAN UNTUK MODAL (INFO PO, RIWAYAT, & DROPDOWN METODE)
    if ($action === 'get_payment_data') {
        $po_id = $_GET['po_id'] ?? '';

        // Ambil info PO
        $stmtPO = $pdo->prepare("SELECT po_no, total_amount, paid_amount FROM purchase_orders WHERE id = ?");
        $stmtPO->execute([$po_id]);
        $poInfo = $stmtPO->fetch(PDO::FETCH_ASSOC);

        // Ambil Riwayat Pembayaran
        $stmtHist = $pdo->prepare("SELECT pp.*, pm.name as method_name FROM purchase_payments pp JOIN payment_methods pm ON pp.payment_method_id = pm.id WHERE pp.po_id = ? ORDER BY pp.payment_date ASC");
        $stmtHist->execute([$po_id]);
        $history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

        // Ambil Dropdown Metode
        $methods = $pdo->query("SELECT id, name FROM payment_methods ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'po_info' => $poInfo,
            'history' => $history,
            'methods' => $methods
        ]);
        exit;
    }

    // 3. SIMPAN PEMBAYARAN BARU
    if ($action === 'save_payment') {
        $po_id = $_POST['po_id'] ?? '';
        $method_id = $_POST['method_id'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $pay_date = $_POST['pay_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($po_id) || empty($method_id) || $amount <= 0 || empty($pay_date)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau jumlah bayar tidak valid!']); exit;
        }

        $pdo->beginTransaction();

        // Lock baris PO untuk update agar tidak ada race condition
        $stmtCheck = $pdo->prepare("SELECT total_amount, paid_amount FROM purchase_orders WHERE id = ? FOR UPDATE");
        $stmtCheck->execute([$po_id]);
        $po = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $sisa = $po['total_amount'] - $po['paid_amount'];

        if ($amount > $sisa) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Jumlah bayar melebih sisa tagihan! Sisa tagihan saat ini: Rp ' . number_format($sisa,0,',','.')]); exit;
        }

        // 1. Catat ke history
        $stmtPay = $pdo->prepare("INSERT INTO purchase_payments (po_id, payment_method_id, amount, payment_date, notes, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtPay->execute([$po_id, $method_id, $amount, $pay_date, $notes, $user_id]);

        // 2. Kalkulasi status baru
        $new_paid = $po['paid_amount'] + $amount;
        $new_status = ($new_paid >= $po['total_amount']) ? 'paid' : 'partial';

        // 3. Update PO
        $stmtUpd = $pdo->prepare("UPDATE purchase_orders SET paid_amount = ?, payment_status = ? WHERE id = ?");
        $stmtUpd->execute([$new_paid, $new_status, $po_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil dicatat!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error Database: ' . $e->getMessage()]);
}
?>