<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('lap_keluar_titipan');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'read') {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 15; 
    $offset = ($page - 1) * $limit;

    $periode = $_GET['periode'] ?? 'bulan_ini';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $reason = $_GET['reason'] ?? 'semua';

    $whereClause = "WHERE 1=1";
    $params = [];

    if ($periode === 'bulan_ini') {
        $whereClause .= " AND MONTH(k.created_at) = MONTH(CURDATE()) AND YEAR(k.created_at) = YEAR(CURDATE())";
    } elseif ($periode === 'hari_ini') {
        $whereClause .= " AND DATE(k.created_at) = CURDATE()";
    } elseif ($periode === 'custom' && !empty($start_date) && !empty($end_date)) {
        $whereClause .= " AND DATE(k.created_at) BETWEEN ? AND ?";
        $params[] = $start_date; $params[] = $end_date;
    }

    if ($reason !== 'semua') {
        $whereClause .= " AND k.reason = ?";
        $params[] = $reason;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(k.id) FROM barang_titipan_keluar k $whereClause");
    $countStmt->execute($params);
    $total_data = $countStmt->fetchColumn();
    $total_pages = ceil($total_data / $limit);

    $sql = "SELECT k.*, t.nama_barang, t.nama_umkm, u.name as admin_name 
            FROM barang_titipan_keluar k 
            JOIN barang_titipan t ON k.titipan_id = t.id 
            JOIN users u ON k.user_id = u.id 
            $whereClause
            ORDER BY k.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data, 'total_pages' => $total_pages, 'current_page' => $page]);
    exit;
}
?>