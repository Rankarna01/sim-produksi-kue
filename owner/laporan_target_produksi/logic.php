<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('lap_target_produksi');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'init') {
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'kitchens' => $kitchens]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $kitchen_id = $_GET['kitchen_id'] ?? 'semua';

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND pp.plan_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        if ($kitchen_id !== 'semua') {
            $whereClause .= " AND e.kitchen_id = ?";
            $params[] = $kitchen_id;
        }

        // Query Pintar: Menghitung Actual Qty dari hasil input produksi riil
        $sql = "
            SELECT 
                pp.plan_date,
                e.name AS employee_name,
                k.name AS kitchen_name,
                pr.name AS product_name,
                pr.code AS product_code,
                ppd.target_qty,
                ppd.est_adonan_kg,
                COALESCE((
                    SELECT SUM(pd.quantity)
                    FROM production_details pd
                    JOIN productions p ON pd.production_id = p.id
                    WHERE p.employee_id = pp.karyawan_id 
                      AND DATE(p.created_at) = pp.plan_date 
                      AND pd.product_id = ppd.product_id
                      AND p.status != 'rejected'
                ), 0) AS actual_qty
            FROM production_plan_details ppd
            JOIN production_plans pp ON ppd.plan_id = pp.id
            JOIN employees e ON pp.karyawan_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            JOIN products pr ON ppd.product_id = pr.id
            $whereClause
            ORDER BY pp.plan_date DESC, k.name ASC, e.name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>