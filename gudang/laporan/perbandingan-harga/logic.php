<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_perbandingan_harga');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; // Tampilkan 10 item per halaman karena detailnya banyak
        $offset = ($page - 1) * $limit;

        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE po.status = 'received' AND pod.price > 0";
        $params = [];

        // Filter Tanggal
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(po.updated_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(po.updated_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Ambil semua data mentah berdasarkan filter
        $sql = "
            SELECT 
                ms.id as material_id, ms.material_name, c.name as category_name,
                s.id as supplier_id, s.name as supplier_name,
                pod.price, po.updated_at as received_date
            FROM purchase_order_details pod
            JOIN purchase_orders po ON pod.po_id = po.id
            JOIN materials_stocks ms ON pod.material_id = ms.id
            LEFT JOIN material_categories c ON ms.category_id = c.id
            JOIN suppliers s ON po.supplier_id = s.id
            $whereClause 
            ORDER BY po.updated_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Olah data di PHP (Grouping per barang, ambil harga terbaru per supplier)
        $comparison = [];
        foreach ($results as $row) {
            $mat_id = $row['material_id'];
            $sup_id = $row['supplier_id'];
            
            if (!isset($comparison[$mat_id])) {
                $comparison[$mat_id] = [
                    'material_name' => $row['material_name'],
                    'category_name' => $row['category_name'] ?: 'Umum',
                    'suppliers' => []
                ];
            }
            
            // Karena SQL diurutkan DESC, yg pertama masuk adalah yg paling update
            if (!isset($comparison[$mat_id]['suppliers'][$sup_id])) {
                $comparison[$mat_id]['suppliers'][$sup_id] = [
                    'supplier_name' => $row['supplier_name'],
                    'price' => (float)$row['price'],
                    'date' => $row['received_date']
                ];
            }
        }

        // Kalkulasi Statistik & Rekomendasi
        $final_data = [];
        foreach ($comparison as &$item) {
            $prices = array_column($item['suppliers'], 'price');
            $min = min($prices);
            $max = max($prices);
            $avg = count($prices) > 0 ? array_sum($prices) / count($prices) : 0;
            
            // Rumus Spread (Selisih % antara termahal dan termurah)
            $spread = $min > 0 ? (($max - $min) / $min) * 100 : 0;

            // Urutkan supplier dari yg termurah
            usort($item['suppliers'], function($a, $b) {
                return $a['price'] <=> $b['price'];
            });

            $item['min_price'] = $min;
            $item['max_price'] = $max;
            $item['avg_price'] = $avg;
            $item['spread'] = round($spread, 2);
            $item['best_supplier'] = $item['suppliers'][0]['supplier_name'];
            $item['best_date'] = $item['suppliers'][0]['date'];
            
            $final_data[] = $item;
        }

        // Pagination Manual di PHP Array
        $total_data = count($final_data);
        $total_pages = ceil($total_data / $limit);
        $paginated_data = array_slice($final_data, $offset, $limit);

        echo json_encode([
            'status' => 'success', 
            'data' => $paginated_data,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>