<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? 'read';

if ($action === 'read') {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    try {
        $query = "
            SELECT 
                sd.custom_name, 
                sd.qty, 
                sd.price, 
                sd.subtotal, 
                s.invoice_no, 
                s.created_at, 
                s.production_status, 
                COALESCE(c.name, 'Pelanggan Umum') as customer_name
            FROM sale_details_pos sd
            JOIN sales_pos s ON sd.sale_id = s.id
            LEFT JOIN customers_pos c ON s.customer_id = c.id
            WHERE sd.is_custom = 1
        ";

        $params = [];

        if (!empty($search)) {
            $query .= " AND (sd.custom_name LIKE ? OR s.invoice_no LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status)) {
            $query .= " AND s.production_status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY s.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>