<?php
require_once '../../config/database.php';
checkPermission('pesanan_custom');
session_start();

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action === 'read') {
    try {
        // Ambil Transaksi yang MENGANDUNG ITEM CUSTOM dan status produksinya belum 'diambil'
        $stmt = $pdo->query("
            SELECT s.id, s.invoice_no, s.created_at, s.production_status, s.notes, s.order_type, s.channel, c.name as customer_name
            FROM sales_pos s
            LEFT JOIN customers_pos c ON s.customer_id = c.id
            WHERE s.id IN (SELECT sale_id FROM sale_details_pos WHERE is_custom = 1)
            AND s.production_status != 'selesai'
            ORDER BY s.created_at ASC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil detail custom items untuk masing-masing transaksi
        $data = [];
        foreach ($orders as $order) {
            $stmtDetail = $pdo->prepare("SELECT custom_name, qty FROM sale_details_pos WHERE sale_id = ? AND is_custom = 1");
            $stmtDetail->execute([$order['id']]);
            $order['custom_items'] = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
            $data[] = $order;
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update_status') {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? ''; // 'diproses' atau 'selesai'

    try {
        $stmt = $pdo->prepare("UPDATE sales_pos SET production_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Status pesanan berhasil diperbarui!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>