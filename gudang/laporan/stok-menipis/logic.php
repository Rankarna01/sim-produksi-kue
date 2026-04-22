<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_stok_menipis');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $threshold = isset($_GET['threshold']) ? (float)$_GET['threshold'] : 10;
        $search = $_GET['search'] ?? '';

        // Tampilkan barang aktif yang stoknya kurang dari atau sama dengan threshold
        $whereClause = "WHERE ms.status = 'active' AND ms.stock <= ?";
        $params = [$threshold];

        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR ms.sku_code LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countSql = "SELECT COUNT(ms.id) FROM materials_stocks ms LEFT JOIN material_categories c ON ms.category_id = c.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT ms.id, ms.sku_code, ms.material_name, ms.stock, ms.unit, 
                   c.name as category_name, r.name as rack_name 
            FROM materials_stocks ms
            LEFT JOIN material_categories c ON ms.category_id = c.id
            LEFT JOIN racks r ON ms.rack_id = r.id
            $whereClause 
            ORDER BY ms.stock ASC 
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
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>