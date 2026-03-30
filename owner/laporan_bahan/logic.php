<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

$action = $_GET['action'] ?? '';

try {
    // Ambil Parameter Filter
    $search = $_GET['search'] ?? '';
    $status_stok = $_GET['status_stok'] ?? '';

    // Siapkan Query Dinamis
    $sql = "SELECT id, name, stock, unit FROM materials WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND name LIKE ?";
        $params[] = "%$search%";
    }

    if ($status_stok === 'habis') {
        $sql .= " AND stock <= 0";
    } elseif ($status_stok === 'menipis') {
        $sql .= " AND stock > 0 AND stock <= 10";
    } elseif ($status_stok === 'aman') {
        $sql .= " AND stock > 10";
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // ============================================
    // ROUTE 1: EXCEL EXPORT (DOWNLOAD FILE)
    // ============================================
    if ($action === 'export_excel') {
        $filename = "Laporan_Stok_Bahan_Baku_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Nama Bahan Baku', 'Sisa Stok', 'Satuan', 'Kondisi']);
        
        $no = 1;
        foreach ($data as $row) {
            $kondisi = 'Aman';
            if ($row['stock'] <= 0) $kondisi = 'Habis';
            elseif ($row['stock'] <= 10) $kondisi = 'Menipis';

            fputcsv($output, [
                $no++,
                $row['name'],
                $row['stock'],
                $row['unit'],
                $kondisi
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