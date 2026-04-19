<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('laporan_produk_jadi');

$action = $_GET['action'] ?? '';

try {
  
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $sql = "SELECT id, code, name, category FROM products WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        // Cari berdasarkan nama atau kode
        $sql .= " AND (name LIKE ? OR code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // ============================================
    // ROUTE 1: EXCEL EXPORT (DOWNLOAD FILE)
    // ============================================
    if ($action === 'export_excel') {
        $filename = "Laporan_Data_Produk_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Kode Produk', 'Nama Produk', 'Kategori']);
        
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['code'],
                $row['name'],
                $row['category']
            ]);
        }
        
        fclose($output);
        exit;
    }

    // ============================================
    // ROUTE 2: BACA DATA JSON
    // ============================================
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

} catch (PDOException $e) {
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>