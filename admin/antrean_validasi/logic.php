<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;
        $is_print = $_GET['is_print'] ?? 'false';

        // Hanya tarik status pending (Belum di validasi/masuk gudang)
        $whereClause = "WHERE p.status = 'pending'";

        $countStmt = $pdo->query("SELECT COUNT(d.id) FROM productions p JOIN production_details d ON p.id = d.production_id $whereClause");
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $limitClause = ($is_print === 'true') ? "" : "LIMIT $limit OFFSET $offset";
        
        $sql = "
            SELECT p.created_at, p.invoice_no, COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, d.quantity 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            $whereClause
            ORDER BY p.created_at DESC 
            $limitClause
        ";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>